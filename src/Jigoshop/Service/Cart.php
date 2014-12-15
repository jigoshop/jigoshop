<?php

namespace Jigoshop\Service;

use Jigoshop\Core\ContainerAware;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Frontend\Cart as CartContainer;
use Symfony\Component\DependencyInjection\Container;
use WPAL\Wordpress;

class Cart implements CartServiceInterface, ContainerAware
{
	const CART = 'jigoshop_cart';
	const CART_ID = 'jigoshop_cart_id';

	/** @var Wordpress */
	private $wp;
	/** @var TaxServiceInterface */
	private $taxService;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var string */
	private $currentUserCartId;
	/** @var Container */
	private $di;

	private $carts = array();

	public function __construct(Wordpress $wp, Options $options, TaxServiceInterface $taxService, CustomerServiceInterface $customerService)
	{
		$this->wp = $wp;
		$this->taxService = $taxService;
		$this->customerService = $customerService;

		if (!isset($_SESSION[self::CART])) {
			$_SESSION[self::CART] = array();
		}

		$this->currentUserCartId = $this->generateCartId();
		$this->wp->doAction('jigoshop\service\cart');
	}

	/**
	 * Sets container for every container aware service.
	 *
	 * @param Container $container
	 */
	public function setContainer(Container $container)
	{
		$this->di = $container;
	}

	/**
	 * Find and fetches saved cart.
	 * If cart is not found - returns new empty one.
	 *
	 * @param $id string Id of cart to fetch.
	 * @return CartContainer Prepared cart instance.
	 */
	public function get($id)
	{
		if (!isset($this->carts[$id])) {
			// TODO: Support for transients?
			$data = array();
			if (isset($_SESSION[self::CART][$id])) {
				$data = unserialize($_SESSION[self::CART][$id]);
			}

			/** @var \Jigoshop\Frontend\Cart $cart */
			$cart = $this->di->get('jigoshop.cart');
			$cart->initializeFor($this->getCartIdForCurrentUser(), $data);

			if ($cart->getCustomer() === null) {
				$cart->setCustomer($this->customerService->getCurrent());
			}

			$this->carts[$id] = $cart;
		}

		return $this->carts[$id];
	}

	/**
	 * Find and fetches cart for current user.
	 * If cart is not found - returns new empty one.
	 *
	 * @return CartContainer Prepared cart instance.
	 */
	public function getCurrent()
	{
		return $this->get($this->getCartIdForCurrentUser());
	}

	/**
	 * Saves cart for current user.
	 *
	 * @param CartContainer $cart Cart to save.
	 */
	public function save(CartContainer $cart)
	{
		// TODO: Support for transients?
		$cart->recalculateCoupons();
		$_SESSION[self::CART][$cart->getId()] = serialize($cart->getState());
	}

	/**
	 * Removes cart.
	 *
	 * @param CartContainer $cart Cart to remove.
	 */
	public function remove(CartContainer $cart)
	{
		// TODO: Support for transients?
		if (isset($_SESSION[self::CART][$cart->getId()])) {
			unset($_SESSION[self::CART][$cart->getId()]);
		}
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

	/**
	 * Creates cart from order ID.
	 *
	 * @param $cartId string Cart ID to use.
	 * @param $order Order Order to base cart on.
	 * @return CartContainer The cart.
	 */
	public function createFromOrder($cartId, $order)
	{
		/** @var \Jigoshop\Frontend\Cart $cart */
		$cart = $this->di->get('jigoshop.cart');
		$cart->initializeFor($cartId, array());
		$cart->setCustomer($order->getCustomer());
		$cart->setShippingMethod($order->getShippingMethod(), $this->taxService);
		foreach ($order->getItems() as $item) {
			$cart->addItem($item);
		}

		return $cart;
	}
}
