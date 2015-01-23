<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Cart;
use Jigoshop\Entity\Customer\Address;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Exception;
use Jigoshop\Factory\Order as OrderFactory;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Validation;
use WPAL\Wordpress;

class CartService implements CartServiceInterface
{
	const CART = 'jigoshop_cart';
	const CART_ID = 'jigoshop_cart_id';

	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var ShippingServiceInterface */
	private $shippingService;
	/** @var PaymentServiceInterface */
	private $paymentService;
	/** @var OrderFactory */
	private $orderFactory;
	/** @var string */
	private $currentUserCartId;

	private $carts = array();

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService,
		ProductServiceInterface $productService, ShippingServiceInterface $shippingService,
		PaymentServiceInterface $paymentService, OrderFactory $orderFactory)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->productService = $productService;
		$this->shippingService = $shippingService;
		$this->paymentService = $paymentService;
		$this->orderFactory = $orderFactory;

		if (!isset($_SESSION[self::CART])) {
			$_SESSION[self::CART] = array();
		}

		$this->currentUserCartId = $this->generateCartId();
	}

	private function generateCartId()
	{
		if ($this->wp->getCurrentUserId() > 0) {
			$id = $this->wp->getCurrentUserId();
		} elseif(isset($_SESSION[self::CART_ID])){
			$id = $_SESSION[self::CART_ID];
		} elseif(isset($_COOKIE[self::CART_ID])){
			$id = $_COOKIE[self::CART_ID];
		} else {
			$id = md5($_SERVER['HTTP_USER_AGENT'].time().$_SERVER['REMOTE_ADDR'].rand(1, 10000000));
		}

		if (!isset($_SESSION[self::CART_ID])) {
			$_SESSION[self::CART_ID] = $id;
		}
		if (!isset($_COOKIE[self::CART_ID])) {
			setcookie(self::CART_ID, $id, null, '/', null, null, true);
		}

		return $id;
	}

	public function init()
	{
		$this->wp->doAction('jigoshop\service\cart');
	}

	/**
	 * Find and fetches cart for current user.
	 * If cart is not found - returns new empty one.
	 *
	 * @return Cart Prepared cart instance.
	 */
	public function getCurrent()
	{
		return $this->get($this->getCartIdForCurrentUser());
	}

	/**
	 * Find and fetches saved cart.
	 * If cart is not found - returns new empty one.


*
*@param $id string Id of cart to fetch.
	 * @return \Jigoshop\Entity\Cart Prepared cart instance.
	 */
	public function get($id)
	{
		if (!isset($this->carts[$id])) {
			$cart = new Cart($this->wp, $this->options->get('tax.classes'));
			$cart->setCustomer($this->customerService->getCurrent());
			$cart->getCustomer()
				->selectTaxAddress($this->options->get('taxes.shipping') ? 'shipping' : 'billing');

			// Fetch data from session if available
			$cart->setId($id);

			$state = $this->getStateFromSession($id);
			if (isset($_POST['jigoshop_order']) && Pages::isCheckout()) {
				$state = $this->getStateFromCheckout($state);
			}

			// TODO: Support for transients?

			$cart = $this->orderFactory->fill($cart, $state);
			$this->carts[$id] = $this->wp->applyFilters('jigoshop\service\cart\get', $cart, $state);
		}

		return $this->carts[$id];
	}

	private function getStateFromSession($id)
	{
		$state = array();

		if (isset($_SESSION[self::CART][$id])) {
			$state = $_SESSION[self::CART][$id];

			if (isset($state['customer'])) {
				// Customer must be unserialized twice "thanks" to WordPress second serialization.
				$state['customer'] = unserialize($state['customer']);
			}

			if (isset($state['items'])) {
				$productService = $this->productService;
				$this->wp->addFilter('jigoshop\internal\order\item', function ($value, $data) use ($productService){
					return $productService->findForState($data);
				}, 10, 2);
				$state['items'] = unserialize($state['items']);
			}

			if (isset($state['shipping'])) {
				$shipping = $state['shipping'];
				if (!empty($shipping['method'])) {
					$state['shipping'] = array(
						'method' => $this->shippingService->findForState($shipping['method']),
						'price' => $shipping['price'],
						'rate' => isset($shipping['rate']) ? $shipping['rate'] : null,
					);
				}
			}
		}

		return $state;
	}

	private function getStateFromCheckout($state)
	{
		$state['customer_note'] = $_POST['jigoshop_order']['customer_note'];
		$state['billing_address'] = $_POST['jigoshop_order']['billing_address'];

		if ($_POST['jigoshop_order']['different_shipping_address'] == 'on') {
			$state['shipping_address'] = $_POST['jigoshop_order']['shipping_address'];
		} else {
			$state['shipping_address'] = $state['billing_address'];
		}

		if (isset($_POST['jigoshop_order']['payment_method'])) {
			$payment = $this->paymentService->get($_POST['jigoshop_order']['payment_method']);
			$this->wp->doAction('jigoshop\service\cart\payment', $payment);
			$state['payment'] = $payment;
		}

		if (isset($_POST['jigoshop_order']['shipping_method'])) {
			$shipping = $this->shippingService->get($_POST['jigoshop_order']['shipping_method']);
			$this->wp->doAction('jigoshop\service\cart\shipping', $shipping);
			$state['shipping'] = array(
				'method' => $shipping,
				'rate' => isset($_POST['jigoshop_order']['shipping_method_rate']) ? $_POST['jigoshop_order']['shipping_method_rate'] : null,
				'price' => -1,
			);
		}

		return $state;
	}

	/**
	 * Returns cart ID for current user.
	 * If the user is logged in - returns his ID so his cart will be properly loaded.
	 * Otherwise generates random string based on available user data to preserve it's cart.
	 *
	 * @return string Cart ID for currently logged in user.
	 */
	public function getCartIdForCurrentUser()
	{
		return $this->currentUserCartId;
	}

	/**
	 * Validates whether
	 * @param OrderInterface $cart
	 */
	public function validate(OrderInterface $cart)
	{
		$customer = $cart->getCustomer();
		$billingErrors = $this->validateAddress($customer->getBillingAddress());

		if ($customer->getBillingAddress()->getEmail() == null) {
			$billingErrors[] = __('Email address is empty.', 'jigoshop');
		}
		if ($customer->getBillingAddress()->getPhone() == null) {
			$billingErrors[] = __('Phone is empty.', 'jigoshop');
		}

		if (!Validation::isEmail($customer->getBillingAddress()->getEmail())) {
			$billingErrors[] = __('Email address is invalid.', 'jigoshop');
		}

		$shippingErrors = $this->validateAddress($customer->getShippingAddress());

		$error = '';
		if (!empty($billingErrors)) {
			$error .= $this->prepareAddressError(__('Billing address is not valid.', 'jigoshop'), $billingErrors);
		}
		if (!empty($shippingErrors)) {
			$error .= $this->prepareAddressError(__('Shipping address is not valid.', 'jigoshop'), $shippingErrors);
		}
		if (!empty($error)) {
			throw new Exception($error);
		}
	}

	/**
	 * @param $address Address
	 * @return array
	 */
	private function validateAddress($address)
	{
		$errors = array();

		if (!$address->isValid()) {
			if ($address->getFirstName() == null) {
				$errors[] = __('First name is empty.', 'jigoshop');
			}
			if ($address->getLastName() == null) {
				$errors[] = __('Last name is empty.', 'jigoshop');
			}
			if ($address->getAddress() == null) {
				$errors[] = __('Address is empty.', 'jigoshop');
			}
			if ($address->getCountry() == null) {
				$errors[] = __('Country is not selected.', 'jigoshop');
			}
			if ($address->getState() == null) {
				$errors[] = __('State or province is not selected.', 'jigoshop');
			}
			if ($address->getCity() == null) {
				$errors[] = __('City is empty.', 'jigoshop');
			}
			if ($address->getPostcode() == null) {
				$errors[] = __('Postcode is empty.', 'jigoshop');
			}
			if ($this->options->get('shopping.validate_zip') && !Validation::isPostcode($address->getPostcode(), $address->getCountry())) {
				$errors[] = __('Invalid postcode.', 'jigoshop');
			}
		}

		if (!Country::exists($address->getCountry())) {
			$errors[] = sprintf(__('Country "%s" does not exist.', 'jigoshop'), $address->getCountry());
		}
		if (Country::hasStates($address->getCountry()) && !Country::hasState($address->getCountry(), $address->getState())) {
			$errors[] = sprintf(__('Country "%s" does not have state "%s".', 'jigoshop'), $address->getCountry(), $address->getState());
		}

		return $errors;
	}

	private function prepareAddressError($message, $errors)
	{
		return $message.'<ul><li>'.join('</li><li>', $errors).'</li></ul>';
	}

	/**
	 * Saves cart for current user.


*
*@param \Jigoshop\Entity\Cart $cart Cart to save.
	 */
	public function save(Cart $cart)
	{
		// TODO: Support for transients?
		$cart->recalculateCoupons();
		$_SESSION[self::CART][$cart->getId()] = $cart->getStateToSave();
	}

	/**
	 * Removes cart.


*
*@param \Jigoshop\Entity\Cart $cart Cart to remove.
	 */
	public function remove(Cart $cart)
	{
		// TODO: Support for transients?
		if (isset($_SESSION[self::CART][$cart->getId()])) {
			unset($_SESSION[self::CART][$cart->getId()]);
		}
	}

	/**
	 * Creates cart from order ID.


*
*@param $cartId string Cart ID to use.
	 * @param $order Order Order to base cart on.
	 * @return \Jigoshop\Entity\Cart The cart.
	 */
	public function createFromOrder($cartId, $order)
	{
		/** @var \Jigoshop\Entity\Cart $cart */
//		$cart = $this->di->get('jigoshop.cart');
//		$cart->initializeFor($cartId, array());
//		$cart->setCustomer($order->getCustomer());
//		$cart->setShippingMethod($order->getShippingMethod());
//		foreach ($order->getItems() as $item) {
//			$cart->addItem($item);
//		}
//
//		return $cart;
	}
}
