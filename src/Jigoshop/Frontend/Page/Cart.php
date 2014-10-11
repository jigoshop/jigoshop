<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Exception;
use Jigoshop\Frontend\Page;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\Customer;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Shipping\Method;
use WPAL\Wordpress;

class Cart implements Page
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages  */
	private $messages;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var Customer */
	private $customerService;
	/** @var ShippingServiceInterface */
	private $shippingService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, CartServiceInterface $cartService, ProductServiceInterface $productService,
		Customer $customerService, ShippingServiceInterface $shippingService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->cartService = $cartService;
		$this->productService = $productService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;

		$styles->add('jigoshop.shop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.shop.cart', JIGOSHOP_URL.'/assets/css/shop/cart.css');
		$styles->add('jigoshop-vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
		$scripts->add('jigoshop.helpers', JIGOSHOP_URL.'/assets/js/helpers.js');
		$scripts->add('jigoshop.shop', JIGOSHOP_URL.'/assets/js/shop.js');
		$scripts->add('jigoshop.shop.cart', JIGOSHOP_URL.'/assets/js/shop/cart.js');
		$scripts->add('jigoshop-vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
		$scripts->localize('jigoshop.shop.cart', 'jigoshop', array(
			'ajax' => admin_url('admin-ajax.php', 'jigoshop'),
		));

		$wp->addAction('wp_ajax_jigoshop_cart_update_item', array($this, 'ajaxUpdateItem'));
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_update_item', array($this, 'ajaxUpdateItem'));
		$wp->addAction('wp_ajax_jigoshop_cart_select_shipping', array($this, 'ajaxSelectShipping'));
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_select_shipping', array($this, 'ajaxSelectShipping'));
		$wp->addAction('wp_ajax_jigoshop_cart_change_country', array($this, 'ajaxChangeCountry'));
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_change_country', array($this, 'ajaxChangeCountry'));
		$wp->addAction('wp_ajax_jigoshop_cart_change_state', array($this, 'ajaxChangeState'));
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_change_state', array($this, 'ajaxChangeState'));
		$wp->addAction('wp_ajax_jigoshop_cart_change_postcode', array($this, 'ajaxChangePostcode'));
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_change_postcode', array($this, 'ajaxChangePostcode'));
	}

	// TODO: Refactor functions
	public function ajaxChangeCountry()
	{
		$customer = $this->customerService->getCurrent();
		$customer->setCountry($_POST['country']);
		$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());

		$shipping = array();
		foreach ($this->shippingService->getAvailable() as $method) {
			/** @var $method Method */
			$shipping[$method->getId()] = $method->calculate($cart);
		}

		$response = $this->getAjaxCartResponse($cart);
		// Add some additional fields
		$response['has_states'] = Country::hasStates($customer->getCountry());
		$response['states'] = Country::getStates($customer->getCountry());
		$response['shipping'] = $shipping;
		$response['html']['estimation'] = $customer->getLocation();
		$response['html']['shipping'] = array_map(function($item){ return Product::formatPrice($item); }, $shipping);

		echo json_encode($response);
		exit;
	}

	public function ajaxChangeState()
	{
		$customer = $this->customerService->getCurrent();
		$customer->setState($_POST['state']);
		$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());

		$shipping = array();
		foreach ($this->shippingService->getAvailable() as $method) {
			/** @var $method Method */
			$shipping[$method->getId()] = $method->calculate($cart);
		}

		$response = $this->getAjaxCartResponse($cart);
		$response['shipping'] = $shipping;
		$response['html']['estimation'] = $customer->getLocation();
		$response['html']['shipping'] = array_map(function($item){ return Product::formatPrice($item); }, $shipping);

		// TODO: Fetch shipping values

		echo json_encode($response);
		exit;
	}

	public function ajaxChangePostcode()
	{
		$customer = $this->customerService->getCurrent();
		$customer->setPostcode($_POST['postcode']);
		$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());

		$response = $this->getAjaxCartResponse($cart);
		$response['html']['estimation'] = $customer->getLocation();

		// TODO: Fetch shipping values

		echo json_encode($response);
		exit;
	}

	/**
	 * Processes change of selected shipping method and returns updated cart details.
	 */
	public function ajaxSelectShipping()
	{
		// TODO: Bullet-proof a little bit this code (i.e. invalid shipping method or something)
		$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());
		$cart->setShippingMethod($this->shippingService->get($_POST['method']));
		$this->cartService->save($cart);

		$response = $this->getAjaxCartResponse($cart);

		echo json_encode($response);
		exit;
	}

	/**
	 * Processes change of item quantity and returns updated item value and cart details.
	 */
	public function ajaxUpdateItem()
	{
		try {
			$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());
			$cart->updateQuantity($_POST['item'], (int)$_POST['quantity']);
			$item = $cart->getItem($_POST['item']);
			$price = $this->options->get('general.price_tax') == 'with_tax' ? $item['price'] + $item['tax'] : $item['price'];

			$response = $this->getAjaxCartResponse($cart);
			// Add some additional fields
			$response['item_price'] = $price;
			$response['item_subtotal'] = $price * $item['quantity'];
			$response['html']['item_price'] = Product::formatPrice($price);
			$response['html']['item_subtotal'] = Product::formatPrice($price * $item['quantity']);
		} catch(Exception $e) {
			$response = array(
				'success' => false,
				'error' => $e->getMessage(),
				'html' => array(
					'subtotal' => Product::formatPrice($cart->getSubtotal()),
					'tax' => array_map(function($tax){ return Product::formatPrice($tax); }, $cart->getTax()),
					'total' => Product::formatPrice($cart->getTotal()),
				),
			);
		}

		$this->cartService->save($cart);
		echo json_encode($response);
		exit;
	}

	public function action()
	{
		$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());

		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'checkout':
					// TODO: Update values with non-JS mode
					$this->wp->wpRedirect($this->wp->getPermalink($this->options->getPageId(Pages::CHECKOUT)));
					exit;
				case 'update-cart':
					if (isset($_POST['cart']) && is_array($_POST['cart'])) {
						try {
							foreach ($_POST['cart'] as $item => $quantity) {
								$cart->updateQuantity($item, (int)$quantity);
							}
							$this->cartService->save($cart);
							$this->messages->addNotice(__('Successfully updated the cart.', 'jigoshop'));
						} catch(Exception $e) {
							$this->messages->addError(sprintf(__('Error occurred while updating cart: %s', 'jigoshop'), $e->getMessage()));
						}
					}
			}
		}

		if (isset($_GET['action']) && isset($_GET['item']) && $_GET['action'] === 'remove-item' && is_numeric($_GET['item'])) {
			$cart->removeItem((int)$_GET['item']);
			$this->cartService->save($cart);
			$this->messages->addNotice(__('Successfully removed item from cart.', 'jigoshop'), false);
		}
	}

	public function render()
	{
		$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::CART));

		return Render::get('shop/cart', array(
			'content' => $content,
			'cart' => $cart,
			'messages' => $this->messages,
			'productService' => $this->productService,
			'customer' => $this->customerService->getCurrent(),
			'shippingMethods' => $this->shippingService->getAvailable(),
			'shopUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::SHOP)),
			'showWithTax' => $this->options->get('general.price_tax') == 'with_tax',
			'showShippingCalculator' => $this->options->get('shipping.calculator'),
		));
	}

	private function getAjaxCartResponse(\Jigoshop\Frontend\Cart $cart)
	{
		$tax = array();
		foreach ($cart->getTax() as $class => $value) {
			$tax[$class] = array(
				'label' => $cart->getTaxLabel($class),
				'value' => Product::formatPrice($value),
			);
		}

		$response = array(
			'success' => true,
			'subtotal' => $cart->getSubtotal(),
			'product_subtotal' => $cart->getProductSubtotal(),
			'tax' => $cart->getTax(),
			'total' => $cart->getTotal(),
			'html' => array(
				'subtotal' => Product::formatPrice($cart->getSubtotal()),
				'product_subtotal' => Product::formatPrice($cart->getProductSubtotal()),
				'tax' => $tax,
				'total' => Product::formatPrice($cart->getTotal()),
			),
		);

		return $response;
	}
}
