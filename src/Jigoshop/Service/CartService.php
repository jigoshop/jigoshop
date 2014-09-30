<?php

namespace Jigoshop\Service;

use Jigoshop\Frontend\Cart;
use WPAL\Wordpress;

class CartService implements CartServiceInterface
{
	const CART = 'jigoshop_cart';

	/** @var Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;

		if (!isset($_SESSION[self::CART])) {
			$_SESSION[self::CART] = array();
		}
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
			return unserialize($_SESSION[self::CART][$id]);
		}

		// TODO: ID generation
		return new Cart('');
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
}}
