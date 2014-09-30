<?php

namespace Jigoshop\Service;

use Jigoshop\Frontend\Cart;

/**
 * Interface for cart handling service.

 *
*@package Jigoshop\Service
 */
interface CartServiceInterface
{
	/**
	 * Find and fetches saved cart.
	 *
	 * If cart is not found - returns new empty one.
	 *
	 * @param $id string Id of cart to fetch.
	 * @return Cart Prepared cart instance.
	 */
	public function get($id);

	/**
	 * Saves cart for current user.
	 *
	 * @param Cart $cart Cart to save.
	 */
	public function save(Cart $cart);

	/**
	 * Removes cart.
	 *
	 * @param Cart $cart Cart to remove.
	 */
	public function remove(Cart $cart);
}
