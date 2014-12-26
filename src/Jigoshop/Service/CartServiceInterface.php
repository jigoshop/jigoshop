<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Cart;
use Jigoshop\Entity\Order;

/**
 * Interface for cart handling service.

 *
*@package Jigoshop\Service
 */
interface CartServiceInterface
{
	/**
	 * Find and fetches saved cart.
	 * If cart is not found - returns new empty one.
	 *
	 * @param $id string Id of cart to fetch.
	 * @return Cart Prepared cart instance.
	 */
	public function get($id);

	/**
	 * Find and fetches cart for current user.
	 * If cart is not found - returns new empty one.
	 *
	 * @return Cart Prepared cart instance.
	 */
	public function getCurrent();

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
	 * If the user is logged in - returns his ID so his cart will be properly loaded.
	 * Otherwise generates random string based on available user data to preserve it's cart.
	 *
	 * @return string Cart ID for currently logged in user.
	 */
	public function getCartIdForCurrentUser();

	/**
	 * Creates cart from order ID.
	 *
	 * @param $cartId string Cart ID to use.
	 * @param $order Order Order to base cart on.
	 * @return Cart The cart.
	 */
	public function createFromOrder($cartId, $order);
}
