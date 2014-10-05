<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Exception;
use Jigoshop\Frontend\Page;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
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
	/** @var \Jigoshop\Frontend\Cart */
	private $cart;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, CartServiceInterface $cartService, ProductServiceInterface $productService, Styles $styles,
		Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->cartService = $cartService;
		$this->productService = $productService;
		$this->cart = $cartService->get($cartService->getCartIdForCurrentUser());

		$styles->add('jigoshop.shop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.shop.cart', JIGOSHOP_URL.'/assets/css/shop/cart.css');
		$scripts->add('jigoshop.helpers', JIGOSHOP_URL.'/assets/js/helpers.js');
		$scripts->add('jigoshop.shop', JIGOSHOP_URL.'/assets/js/shop.js');
		$scripts->add('jigoshop.shop.cart', JIGOSHOP_URL.'/assets/js/shop/cart.js');
		$scripts->localize('jigoshop.shop.cart', 'jigoshop', array(
			'ajax' => admin_url('admin-ajax.php', 'jigoshop'),
		));

		$wp->addAction('wp_ajax_jigoshop_cart_update_item', array($this, 'ajaxUpdateItem'));
		$wp->addAction('wp_ajax_nopriv_jigoshop_cart_update_item', array($this, 'ajaxUpdateItem'));
	}

	public function ajaxUpdateItem()
	{
		try {
			$this->cart->updateQuantity($_POST['item'], (int)$_POST['quantity']);
			$item = $this->cart->getItem($_POST['item']);

			// TODO: Improve totals calculation
			$result = array(
				'success' => true,
				'item_price' => $item['price'],
				'item_subtotal' => $item['price'] * $item['quantity'],
				'total' => $this->cart->getTotal(),
				'html' => array(
					'item_price' => Product::formatPrice($item['price']),
					'item_subtotal' => Product::formatPrice($item['price'] * $item['quantity']),
					'total' => Product::formatPrice($this->cart->getTotal()),
				),
			);
		} catch(Exception $e) {
			$result = array(
				'success' => false,
				'error' => $e->getMessage(),
				'html' => array(
					'total' => Product::formatPrice($this->cart->getTotal()),
				),
			);
		}
		$this->cartService->save($this->cart);
		echo json_encode($result);
		exit;
	}

	public function action()
	{
		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'checkout':
					$this->wp->wpRedirect($this->wp->getPermalink($this->options->getPageId(Pages::CHECKOUT)));
					exit;
				case 'update-cart':
					if (isset($_POST['cart']) && is_array($_POST['cart'])) {
						try {
							foreach ($_POST['cart'] as $item => $quantity) {
								$this->cart->updateQuantity($item, (int)$quantity);
							}
							$this->cartService->save($this->cart);
							$this->messages->addNotice(__('Successfully updated the cart.', 'jigoshop'));
						} catch(Exception $e) {
							$this->messages->addError(sprintf(__('Error occurred while updating cart: %s', 'jigoshop'), $e->getMessage()));
						}
					}
			}
		}

		if (isset($_GET['action']) && isset($_GET['item']) && $_GET['action'] === 'remove-item' && is_numeric($_GET['item'])) {
			$this->cart->removeItem((int)$_GET['item']);
			$this->cartService->save($this->cart);
			$this->messages->addNotice(__('Successfully removed item from cart.', 'jigoshop'), false);
		}
	}

	public function render()
	{
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::CART));

		return Render::get('shop/cart', array(
			'content' => $content,
			'cart' => $this->cart,
			'messages' => $this->messages,
			'productService' => $this->productService,
			'shopUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::SHOP)),
		));
	}
}
