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

	/**
	 * Returns cart ID for current user.
	 *
	 * If the user is logged in - returns his ID so his cart will be properly loaded.
	 * Otherwise generates random string based on available user data to preserve it's cart.
	 *
	 * @return string Cart ID for currently logged in user.
	 */
	public function getCartIdForCurrentUser();
}
