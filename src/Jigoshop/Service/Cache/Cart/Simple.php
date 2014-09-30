<?php

namespace Jigoshop\Service\Cache\Cart;

use Jigoshop\Frontend\Cart;
use Jigoshop\Service\CartServiceInterface;

class Simple implements CartServiceInterface
{
	private $objects = array();

	/** @var \Jigoshop\Service\CartServiceInterface */
	private $service;

	public function __construct(CartServiceInterface $service)
	{
		$this->service = $service;
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
		if (!isset($this->objects[$id])) {
			$this->objects[$id] = $this->service->get($id);
		}

		return $this->objects[$id];
	}

	/**
	 * Saves cart for current user.
	 *
	 * @param Cart $cart Cart to save.
	 */
	public function save(Cart $cart)
	{
		$this->objects[$cart->getId()] = $cart;
		$this->service->save($cart);
	}

	/**
	 * Removes cart.
	 *
	 * @param Cart $cart Cart to remove.
	 */
	public function remove(Cart $cart)
	{
		unset($this->objects[$cart->getId()]);
		$this->service->remove($cart);
	}
}
