<?php

namespace Jigoshop\Service;

use Jigoshop\Frontend\Cart;
use WPAL\Wordpress;

class CartService implements CartServiceInterface
{
	/** @var Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
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
		// TODO: Implement get() method.
	}

	/**
	 * Saves cart for current user.
	 *
	 * @param Cart $cart Cart to save.
	 */
	public function save(Cart $cart)
	{
		// TODO: Implement save() method.
	}

	/**
	 * Removes cart.
	 *
	 * @param Cart $cart Cart to remove.
	 */
	public function remove(Cart $cart)
	{
		// TODO: Implement remove() method.
}}
