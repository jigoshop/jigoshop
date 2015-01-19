<?php

namespace Jigoshop\Service;

use Jigoshop\Core\ContainerAware;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Cart;
use Jigoshop\Entity\Order;
use Symfony\Component\DependencyInjection\Container;
use WPAL\Wordpress;

class CartService implements CartServiceInterface
{
	const CART = 'jigoshop_cart';
	const CART_ID = 'jigoshop_cart_id';

	/** @var Wordpress */
	private $wp;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var string */
	private $currentUserCartId;
	/** @var Container */
	private $di;

	private $carts = array();

	public function __construct(Wordpress $wp, \JigoshopContainer $di, Options $options, CustomerServiceInterface $customerService)
	{
		$this->wp = $wp;
		$this->di = $di;
		$this->customerService = $customerService;

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
			// TODO: Support for transients?
			$data = array();
			if (isset($_SESSION[self::CART][$id])) {
				$data = unserialize($_SESSION[self::CART][$id]);
			}

			/** @var \Jigoshop\Entity\Cart $cart */
			$cart = $this->di->get('jigoshop.cart');
			$cart = $this->wp->applyFilters('jigoshop\service\cart\before_get', $cart);
			$cart->setCustomer($this->customerService->getCurrent());
			$cart = $this->wp->applyFilters('jigoshop\service\cart\before_initialize', $cart);
			$cart->initializeFor($this->getCartIdForCurrentUser(), $data);
			$cart = $this->wp->applyFilters('jigoshop\service\cart\after_get', $cart);
			$this->carts[$id] = $cart;
		}

		return $this->carts[$id];
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
	 * Saves cart for current user.


*
*@param \Jigoshop\Entity\Cart $cart Cart to save.
	 */
	public function save(Cart $cart)
	{
		// TODO: Support for transients?
		$cart->recalculateCoupons();
		$_SESSION[self::CART][$cart->getId()] = serialize($cart->getState());
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
		$cart = $this->di->get('jigoshop.cart');
		$cart->initializeFor($cartId, array());
		$cart->setCustomer($order->getCustomer());
		$cart->setShippingMethod($order->getShippingMethod());
		foreach ($order->getItems() as $item) {
			$cart->addItem($item);
		}

		return $cart;
	}
}
