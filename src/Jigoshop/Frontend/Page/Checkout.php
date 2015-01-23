<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Cart as CartEntity;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Customer\Address;
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product\Simple;
use Jigoshop\Exception;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Tax;
use Jigoshop\Helper\Validation;
use Jigoshop\Integration;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Shipping\Method;
use Jigoshop\Shipping\MultipleMethod;
use Jigoshop\Shipping\Rate;
use WPAL\Wordpress;

class Checkout implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages  */
	private $messages;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var ShippingServiceInterface */
	private $shippingService;
	/** @var PaymentServiceInterface */
	private $paymentService;
	/** @var OrderServiceInterface */
	private $orderService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, CartServiceInterface $cartService,	CustomerServiceInterface $customerService,
		ShippingServiceInterface $shippingService, PaymentServiceInterface $paymentService, OrderServiceInterface $orderService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->cartService = $cartService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;
		$this->paymentService = $paymentService;
		$this->orderService = $orderService;

		Styles::add('jigoshop.checkout', JIGOSHOP_URL.'/assets/css/shop/checkout.css', array(
			'jigoshop.shop',
			'jigoshop.vendors'
		));
		Scripts::add('jigoshop.checkout', JIGOSHOP_URL.'/assets/js/shop/checkout.js', array(
			'jquery',
			'jquery-blockui',
			'jigoshop.helpers',
			'jigoshop.vendors',
		));
		Scripts::localize('jigoshop.checkout', 'jigoshop_checkout', array(
			'ajax' => $this->wp->getAjaxUrl(),
			'assets' => JIGOSHOP_URL.'/assets',
			'i18n' => array(
				'loading' => __('Loading...', 'jigoshop'),
			),
		));

		if (!$wp->isSsl() && $options->get('advanced.force_ssl')) {
			$wp->addAction('template_redirect', array($this, 'redirectToSsl'), 100, 0);
		}

		$wp->addAction('wp_ajax_jigoshop_checkout_change_country', array($this, 'ajaxChangeCountry'));
		$wp->addAction('wp_ajax_nopriv_jigoshop_checkout_change_country', array($this, 'ajaxChangeCountry'));
		$wp->addAction('wp_ajax_jigoshop_checkout_change_state', array($this, 'ajaxChangeState'));
		$wp->addAction('wp_ajax_nopriv_jigoshop_checkout_change_state', array($this, 'ajaxChangeState'));
		$wp->addAction('wp_ajax_jigoshop_checkout_change_postcode', array($this, 'ajaxChangePostcode'));
		$wp->addAction('wp_ajax_nopriv_jigoshop_checkout_change_postcode', array($this, 'ajaxChangePostcode'));
	}

	/**
	 * Redirects to SSL checkout page.
	 */
	public function redirectToSsl()
	{
		$page = $this->options->getPageId(Pages::CHECKOUT);
		$url = str_replace('http:', 'https:', $this->wp->getPermalink($page));
		$this->wp->wpSafeRedirect($url, 301);
		exit;
	}

	/**
	 * Ajax action for changing country.
	 */
	public function ajaxChangeCountry()
	{
		$customer = $this->customerService->getCurrent();

		if ($this->options->get('shopping.restrict_selling_locations') && !in_array($_POST['value'], $this->options->get('shopping.selling_locations'))) {
			$locations = array_map(function($location){ return Country::getName($location); }, $this->options->get('shopping.selling_locations'));
			echo json_encode(array(
				'success' => false,
				'error' => sprintf(__('This location is not supported, we sell only to %s.'), join(', ', $locations)),
			));
			exit;
		}

		switch ($_POST['field']) {
			case 'shipping':
				$customer->getShippingAddress()->setCountry($_POST['value']);
				if ($customer->getBillingAddress()->getCountry() == null) {
					$customer->getBillingAddress()->setCountry($_POST['value']);
				}
				break;
			case 'billing':
				$customer->getBillingAddress()->setCountry($_POST['value']);
				if ($_POST['differentShipping'] === 'false') {
					$customer->getShippingAddress()->setCountry($_POST['value']);
				}
				break;
		}

		$this->customerService->save($customer);
		$cart = $this->cartService->getCurrent();
		$cart->setCustomer($customer);

		$response = $this->getAjaxLocationResponse($customer, $cart);

		echo json_encode($response);
		exit;
	}

	/**
	 * Abstraction for location update response.
	 *
	 * Prepares and returns array of updated data for location change requests.
	 *
	 * @param Customer $customer The customer (for location).
	 * @param CartEntity $cart Current cart.
	 * @return array
	 */
	private function getAjaxLocationResponse(Customer $customer, CartEntity $cart)
	{
		$response = $this->getAjaxCartResponse($cart);
		$address = $customer->getShippingAddress();
		// Add some additional fields
		$response['has_states'] = Country::hasStates($address->getCountry());
		$response['states'] = Country::getStates($address->getCountry());
		$response['html']['estimation'] = $address->getLocation();

		return $response;
	}

	/**
	 * Abstraction for cart update response.
	 *
	 * Prepares and returns response array for cart update requests.
	 *
	 * @param CartEntity $cart Current cart.
	 * @return array
	 */
	private function getAjaxCartResponse(CartEntity $cart)
	{
		$tax = array();
		foreach ($cart->getCombinedTax() as $class => $value) {
			$tax[$class] = array(
				'label' => Tax::getLabel($class, $cart),
				'value' => ProductHelper::formatPrice($value),
			);
		}

		$shipping = array();
		$shippingHtml = array();
		foreach ($this->shippingService->getAvailable() as $method) {
			/** @var $method Method */
			if ($method instanceof MultipleMethod) {
				/** @var $method MultipleMethod */
				foreach ($method->getRates() as $rate) {
					/** @var $rate Rate */
					$shipping[$method->getId().'-'.$rate->getId()] = $method->isEnabled() ? $rate->calculate($cart) : -1;

					if ($method->isEnabled()) {
						$shippingHtml[$method->getId().'-'.$rate->getId()] = array(
							'price' => ProductHelper::formatPrice($rate->calculate($cart)),
							'html' => Render::get('shop/cart/shipping/rate', array('method' => $method, 'rate' => $rate, 'cart' => $cart)),
						);
					}
				}
			} else {
				$shipping[$method->getId()] = $method->isEnabled() ? $method->calculate($cart) : -1;

				if ($method->isEnabled()) {
					$shippingHtml[$method->getId()] = array(
						'price' => ProductHelper::formatPrice($method->calculate($cart)),
						'html' => Render::get('shop/cart/shipping/method', array('method' => $method, 'cart' => $cart)),
					);
				}
			}
		}

		$response = array(
			'success' => true,
			'shipping' => $shipping,
			'subtotal' => $cart->getSubtotal(),
			'product_subtotal' => $cart->getProductSubtotal(),
			'tax' => $cart->getCombinedTax(),
			'total' => $cart->getTotal(),
			'html' => array(
				'shipping' => $shippingHtml,
				'subtotal' => ProductHelper::formatPrice($cart->getSubtotal()),
				'product_subtotal' => ProductHelper::formatPrice($cart->getProductSubtotal()),
				'tax' => $tax,
				'total' => ProductHelper::formatPrice($cart->getTotal()),
			),
		);

		return $response;
	}

	/**
	 * Ajax action for changing state.
	 */
	public function ajaxChangeState()
	{
		$customer = $this->customerService->getCurrent();

		switch ($_POST['field']) {
			case 'shipping':
				$customer->getShippingAddress()->setState($_POST['value']);
				if ($customer->getBillingAddress()->getState() == null) {
					$customer->getBillingAddress()->setState($_POST['value']);
				}
				break;
			case 'billing':
				$customer->getBillingAddress()->setState($_POST['value']);
				if ($_POST['differentShipping'] === 'false') {
					$customer->getShippingAddress()->setState($_POST['value']);
				}
				break;
		}

		$this->customerService->save($customer);
		$cart = $this->cartService->getCurrent();
		$cart->setCustomer($customer);

		$response = $this->getAjaxLocationResponse($customer, $cart);

		echo json_encode($response);
		exit;
	}

	/**
	 * Ajax action for changing postcode.
	 */
	public function ajaxChangePostcode()
	{
		$customer = $this->customerService->getCurrent();

		switch ($_POST['field']) {
			case 'shipping':
				if ($this->options->get('shopping.validate_zip') && !Validation::isPostcode($_POST['value'], $customer->getShippingAddress()->getCountry())) {
					echo json_encode(array(
						'success' => false,
						'error' => __('Shipping postcode is not valid!', 'jigoshop'),
					));
					exit;
				}

				$customer->getShippingAddress()->setPostcode($_POST['value']);
				if ($customer->getBillingAddress()->getPostcode() == null) {
					$customer->getBillingAddress()->setPostcode($_POST['value']);
				}
				break;
			case 'billing':
				if ($this->options->get('shopping.validate_zip') && !Validation::isPostcode($_POST['value'], $customer->getBillingAddress()->getCountry())) {
					echo json_encode(array(
						'success' => false,
						'error' => __('Billing postcode is not valid!', 'jigoshop'),
					));
					exit;
				}

				$customer->getBillingAddress()->setPostcode($_POST['value']);
				if ($_POST['differentShipping'] === 'false') {
					$customer->getShippingAddress()->setPostcode($_POST['value']);
				}
				break;
		}

		$this->customerService->save($customer);
		$cart = $this->cartService->getCurrent();
		$cart->setCustomer($customer);

		$response = $this->getAjaxLocationResponse($customer, $cart);

		echo json_encode($response);
		exit;
	}

	/**
	 * Executes actions associated with selected page.
	 */
	public function action()
	{
		$cart = $this->cartService->getCurrent();

		if ($cart->isEmpty()) {
			$this->messages->addWarning(__('Your cart is empty, please add products before proceeding.', 'jigoshop'));
			$this->wp->redirectTo($this->options->getPageId(Pages::SHOP));
		}

		if (!$this->isAllowedToEnterCheckout()) {
			$this->messages->addError(__('You need to log in before processing to checkout.', 'jigoshop'));
			$this->wp->redirectTo($this->options->getPageId(Pages::CART));
		}

		if (isset($_POST['action']) && $_POST['action'] == 'purchase') {
			try {
				$allowRegistration = $this->options->get('shopping.allow_registration');
				if ($allowRegistration && !$this->wp->isUserLoggedIn()) {
					$this->createUserAccount();
				}

				if (!$this->isAllowedToCheckout($cart)) {
					if ($allowRegistration) {
						throw new Exception(__('You need either to log in or create account to purchase.', 'jigoshop'));
					}

					throw new Exception(__('You need to log in before purchasing.', 'jigoshop'));
				}

				if ($this->options->get('advanced.pages.terms') > 0 && (!isset($_POST['terms']) || $_POST['terms'] != 'on')) {
					throw new Exception(__('You need to accept terms &amp; conditions!', 'jigoshop'));
				}

				$this->cartService->validate($cart);
				$this->customerService->save($cart->getCustomer());

				if (!Country::isAllowed($cart->getCustomer()->getBillingAddress()->getCountry())) {
					$locations = array_map(function($location){ return Country::getName($location); }, $this->options->get('shopping.selling_locations'));
					throw new Exception(sprintf(__('This location is not supported, we sell only to %s.'), join(', ', $locations)));
				}

				$shipping = $cart->getShippingMethod();
				if ($this->isShippingRequired($cart) && (!$shipping || !$shipping->isEnabled())) {
					throw new Exception(__('Shipping is required for this order. Please select shipping method.', 'jigoshop'));
				}

				$payment = $cart->getPaymentMethod();
				$isPaymentRequired = $this->isPaymentRequired($cart);
				if ($isPaymentRequired && (!$payment || !$payment->isEnabled())) {
					throw new Exception(__('Payment is required for this order. Please select payment method.', 'jigoshop'));
				}

				$order = $this->orderService->createFromCart($cart);
				$this->orderService->save($order);
				/** @var Order $order */
				$order = $this->wp->applyFilters('jigoshop\checkout\order', $order);
				$this->cartService->remove($cart);

				$url = '';
				if ($isPaymentRequired) {
					$url = $payment->process($order);
				}

				// Redirect to thank you page
				if (empty($url)) {
					$url = $this->wp->getPermalink($this->wp->applyFilters('jigoshop\checkout\redirect_page_id', $this->options->getPageId(Pages::THANK_YOU)));
					$url = $this->wp->getHelpers()->addQueryArg(array('order' => $order->getId(), 'key' => $order->getKey()), $url);
				}

				$this->wp->wpRedirect($url);
				exit;
			} catch(Exception $e) {
				$this->messages->addError($e->getMessage());
			}
		}
	}

	/**
	 * Checks whether user is allowed to see checkout page.
	 *
	 * @return bool Is user allowed to enter checkout page?
	 */
	private function isAllowedToEnterCheckout()
	{
		return $this->options->get('shopping.guest_purchases') || $this->wp->isUserLoggedIn() || $this->options->get('shopping.show_login_form')
		|| $this->options->get('shopping.allow_registration');
	}

	private function createUserAccount()
	{
		// Check if user agreed to account creation
		if (isset($_POST['jigoshop_account']) && $_POST['jigoshop_account']['create'] != 'on') {
			return;
		}

		$email = $_POST['jigoshop_order']['billing']['email'];
		$errors = new \WP_Error();
		$this->wp->doAction('register_post', $email, $email, $errors);

		if ($errors->get_error_code()) {
			throw new Exception($errors->get_error_message());
		}

		$login = $_POST['jigoshop_account']['login'];
		$password = $_POST['jigoshop_account']['password'];

		if (empty($login) || empty($password)) {
			throw new Exception(__('You need to fill username and password fields.', 'jigoshop'));
		}

		if ($password != $_POST['jigoshop_account']['password2']) {
			throw new Exception(__('Passwords do not match.', 'jigoshop'));
		}

		$id = $this->wp->wpCreateUser($login, $password, $email);

		if (!$id) {
			throw new Exception(sprintf(
				__("<strong>Error</strong> Couldn't register an account for you. Please contact the <a href=\"mailto:%s\">administrator</a>.", 'jigoshop'),
				$this->options->get('general.email')
			));
		}

		$this->wp->wpUpdateUser(array(
			'ID' => $id,
			'role' => 'customer',
			'first_name' => $_POST['jigoshop_order']['billing']['first_name'],
			'last_name' => $_POST['jigoshop_order']['billing']['last_name'],
		));
		$this->wp->doAction('jigoshop\checkout\created_account', $id);

		// send the user a confirmation and their login details
		if ($this->wp->applyFilters('jigoshop\checkout\new_user_notification', true, $id)) {
			$this->wp->wpNewUserNotification($id, $password);
		}

		$this->wp->wpSetAuthCookie($id, true, $this->wp->isSsl());
		$customer = $this->cartService->getCurrent()->getCustomer();
		$customer->setId($id);
	}

	/**
	 * Checks whether user is allowed to see checkout page.
	 *
	 * @param CartEntity $cart The cart.
	 * @return bool Is user allowed to enter checkout page?
	 */
	private function isAllowedToCheckout(CartEntity $cart)
	{
		return $this->options->get('shopping.guest_purchases') || $this->wp->isUserLoggedIn()
		|| ($this->options->get('shopping.allow_registration') && $cart->getCustomer()->getId() > 0);
	}

	/**
	 * @param $order OrderInterface The order.
	 * @return bool
	 */
	private function isShippingRequired($order)
	{
		foreach ($order->getItems() as $item) {
			/** @var $item Item */
			switch ($item->getType()) {
				case Simple::TYPE:
					/** @var \Jigoshop\Entity\Product|\Jigoshop\Entity\Product\Shippable $product */
					$product = $item->getProduct();
					if ($product->isShippable()) {
						return true;
					}
					break;
				default:
					if ($this->wp->applyFilters('jigoshop\checkout\is_shipping_required', false, $item)) {
						return true;
					}
			}
		}

		return false;
	}

	/**
	 * @param $order OrderInterface The order.
	 * @return bool
	 */
	private function isPaymentRequired($order)
	{
		return $order->getTotal() > 0;
	}

	/**
	 * Renders page template.
	 *
	 * @return string Page template.
	 */
	public function render()
	{
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::CHECKOUT));
		$cart = $this->cartService->getCurrent();

		$billingFields = $this->getBillingFields($cart->getCustomer()->getBillingAddress());
		$shippingFields = $this->getShippingFields($cart->getCustomer()->getShippingAddress());

		$termsUrl = '';
		$termsPage = $this->options->get('advanced.pages.terms');
		if ($termsPage > 0) {
			$termsUrl = $this->wp->getPageLink($termsPage);
		}

		return Render::get('shop/checkout', array(
			'cartUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::CART)),
			'content' => $content,
			'cart' => $cart,
			'messages' => $this->messages,
			'shippingMethods' => $this->shippingService->getEnabled(),
			'paymentMethods' => $this->paymentService->getEnabled(),
			'billingFields' => $billingFields,
			'shippingFields' => $shippingFields,
			'showWithTax' => $this->options->get('tax.price_tax') == 'with_tax',
			'showLoginForm' => $this->options->get('shopping.show_login_form') && !$this->wp->isUserLoggedIn(),
			'allowRegistration' => $this->options->get('shopping.allow_registration') && !$this->wp->isUserLoggedIn(),
			'showRegistrationForm' => $this->options->get('shopping.allow_registration') && !$this->options->get('shopping.guest_purchases') && !$this->wp->isUserLoggedIn(),
			'alwaysShowShipping' => $this->options->get('shipping.always_show_shipping'),
			'differentShipping' => isset($_POST['jigoshop_order']) ? $_POST['jigoshop_order']['different_shipping_address'] == 'on' : false,
			// TODO: Fetch whether user want different shipping by default
			'termsUrl' => $termsUrl,
		));
	}

	private function getBillingFields(Address $address)
	{
		$fields = $this->wp->applyFilters('jigoshop\checkout\billing_fields', self::getDefaultBillingFields($address));

		if (!Country::isEU($this->options->get('general.country'))) {
			unset($fields['vat_number']);
		}

		return $fields;
	}

	/**
	 * Returns list of default fields for billing section.
	 *
	 * @param Address $address Address to fill values.
	 * @return array Default fields.
	 */
	public static function getDefaultBillingFields(Address $address)
	{
		return array(
			'first_name' => array(
				'type' => 'text',
				'label' => __('First name', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][first_name]',
				'value' => $address->getFirstName(),
				'size' => 9,
				'columnSize' => 6,
			),
			'last_name' => array(
				'type' => 'text',
				'label' => __('Last name', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][last_name]',
				'value' => $address->getLastName(),
				'size' => 9,
				'columnSize' => 6,
			),
			'company' => array(
				'type' => 'text',
				'label' => __('Company', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][company]',
				'value' => $address instanceof CompanyAddress ? $address->getCompany() : '',
				'size' => 9,
				'columnSize' => 6,
			),
			'vat_number' => array(
				'type' => 'text',
				'label' => __('EU VAT number', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][eu_vat]',
				'value' => $address instanceof CompanyAddress ? $address->getVatNumber() : '',
				'size' => 9,
				'columnSize' => 6,
			),
			'address' => array(
				'type' => 'text',
				'label' => __('Address', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][address]',
				'value' => $address->getAddress(),
				'size' => 10,
				'columnSize' => 12,
			),
			'country' => array(
				'type' => 'select',
				'label' => __('Country', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][country]',
				'options' => Country::getAllowed(),
				'value' => $address->getCountry(),
				'size' => 9,
				'columnSize' => 6,
			),
			'state' => array(
				'type' => Country::hasStates($address->getCountry()) ? 'select' : 'text',
				'label' => __('State/Province', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][state]',
				'options' => Country::getStates($address->getCountry()),
				'value' => $address->getState(),
				'size' => 9,
				'columnSize' => 6,
			),
			'city' => array(
				'type' => 'text',
				'label' => __('City', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][city]',
				'value' => $address->getCity(),
				'size' => 9,
				'columnSize' => 6,
			),
			'postcode' => array(
				'type' => 'text',
				'label' => __('Postcode', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][postcode]',
				'value' => $address->getPostcode(),
				'size' => 9,
				'columnSize' => 6,
			),
			'phone' => array(
				'type' => 'text',
				'label' => __('Phone', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][phone]',
				'value' => $address->getPhone(),
				'size' => 9,
				'columnSize' => 6,
			),
			'email' => array(
				'type' => 'text',
				'label' => __('Email', 'jigoshop'),
				'name' => 'jigoshop_order[billing_address][email]',
				'value' => $address->getEmail(),
				'size' => 9,
				'columnSize' => 6,
			),
		);
	}

	private function getShippingFields(Address $address)
	{
		return $this->wp->applyFilters('jigoshop\checkout\shipping_fields', self::getDefaultShippingFields($address));
	}

	/**
	 * Returns list of default fields for shipping section.
	 *
	 * @param Address $address Address to fill values for.
	 * @return array Default fields.
	 */
	public static function getDefaultShippingFields(Address $address)
	{
		return array(
			'first_name' => array(
				'type' => 'text',
				'label' => __('First name', 'jigoshop'),
				'name' => 'jigoshop_order[shipping_address][first_name]',
				'value' => $address->getFirstName(),
				'size' => 9,
				'columnSize' => 6,
			),
			'last_name' => array(
				'type' => 'text',
				'label' => __('Last name', 'jigoshop'),
				'name' => 'jigoshop_order[shipping_address][last_name]',
				'value' => $address->getLastName(),
				'size' => 9,
				'columnSize' => 6,
			),
			'company' => array(
				'type' => 'text',
				'label' => __('Company', 'jigoshop'),
				'name' => 'jigoshop_order[shipping_address][company]',
				'value' => $address instanceof CompanyAddress ? $address->getCompany() : '',
				'size' => 9,
				'columnSize' => 6,
			),
			'address' => array(
				'type' => 'text',
				'label' => __('Address', 'jigoshop'),
				'name' => 'jigoshop_order[shipping_address][address]',
				'value' => $address->getAddress(),
				'size' => 10,
				'columnSize' => 12,
			),
			'country' => array(
				'type' => 'select',
				'label' => __('Country', 'jigoshop'),
				'name' => 'jigoshop_order[shipping_address][country]',
				'options' => Country::getAllowed(),
				'value' => $address->getCountry(),
				'size' => 9,
				'columnSize' => 6,
			),
			'state' => array(
				'type' => Country::hasStates($address->getCountry()) ? 'select' : 'text',
				'label' => __('State/Province', 'jigoshop'),
				'name' => 'jigoshop_order[shipping_address][state]',
				'options' => Country::getStates($address->getCountry()),
				'value' => $address->getState(),
				'size' => 9,
				'columnSize' => 6,
			),
			'city' => array(
				'type' => 'text',
				'label' => __('City', 'jigoshop'),
				'name' => 'jigoshop_order[shipping_address][city]',
				'value' => $address->getCity(),
				'size' => 9,
				'columnSize' => 6,
			),
			'postcode' => array(
				'type' => 'text',
				'label' => __('Postcode', 'jigoshop'),
				'name' => 'jigoshop_order[shipping_address][postcode]',
				'value' => $address->getPostcode(),
				'size' => 9,
				'columnSize' => 6,
			),
		);
	}
}
