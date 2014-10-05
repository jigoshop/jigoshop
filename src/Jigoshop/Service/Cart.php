<?php

namespace Jigoshop\Service;

use Jigoshop\Frontend\Cart as CartContainer;
use WPAL\Wordpress;

class Cart implements CartServiceInterface
{
	const CART = 'jigoshop_cart';
	const CART_ID = 'jigoshop_cart_id';

	/** @var Wordpress */
	private $wp;
	/** @var string */
	private $currentUserCartId;
	/** @var CartContainer */
	private $cart;

	public function __construct(Wordpress $wp, CartContainer $cart)
	{
		$this->wp = $wp;
		$this->cart = $cart;

		if (!isset($_SESSION[self::CART])) {
			$_SESSION[self::CART] = array();
		}

		$this->currentUserCartId = $this->generateCartId();
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
		// TODO: Support for transients?
		$data = array();
		if (isset($_SESSION[self::CART][$id])) {
			$data = unserialize($_SESSION[self::CART][$id]);
		}

		$this->cart->initializeFor($this->getCartIdForCurrentUser(), $data);
		return $this->cart;
	}

	/**
	 * Saves cart for current user.
	 *
	 * @param CartContainer $cart Cart to save.
	 */
	public function save(CartContainer $cart)
	{
		// TODO: Support for transients?
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
}
