<?php

namespace Jigoshop\Service;

use Jigoshop\Frontend\Cart;
use WPAL\Wordpress;

class CartService implements CartServiceInterface
{
	const CART = 'jigoshop_cart';
	const CART_ID = 'jigoshop_cart_id';

	/** @var Wordpress */
	private $wp;
	/** @var string */
	private $currentUserCartId;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;

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
	 * @return Cart Prepared cart instance.
	 */
	public function get($id)
	{
		// TODO: Support for transients?
		if (isset($_SESSION[self::CART][$id])) {
			$cart = unserialize($_SESSION[self::CART][$id]);

			if ($cart === null) {
				$cart = new Cart($id);
			}

			return $cart;
		}

		return new Cart($this->getCartIdForCurrentUser());
	}

	/**
	 * Saves cart for current user.
	 *
	 * @param Cart $cart Cart to save.
	 */
	public function save(Cart $cart)
	{
		// TODO: Support for transients?
		$_SESSION[self::CART][$cart->getId()] = serialize($cart);
	}

	/**
	 * Removes cart.
	 *
	 * @param Cart $cart Cart to remove.
	 */
	public function remove(Cart $cart)
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
		} elseif(isset($_COOKIE[self::CART_ID])){
			$id = $_COOKIE[self::CART_ID];
		} else {
			$id = md5($_SERVER['HTTP_USER_AGENT'].time().$_SERVER['REMOTE_ADDR'].rand(1, 10000000));
		}

		if (!isset($_COOKIE[self::CART_ID])) {
			setcookie(self::CART_ID, $id, null, '/', null, null, true);
		}

		return $id;
	}
}
