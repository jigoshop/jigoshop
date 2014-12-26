<?php

namespace Jigoshop\Service\Cache\Cart;

use Jigoshop\Entity\Cart;
use Jigoshop\Entity\Order;
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
	 * Find and fetches cart for current user.
	 * If cart is not found - returns new empty one.
	 *
	 * @return Cart Prepared cart instance.
	 */
	public function getCurrent()
	{
		$id = $this->getCartIdForCurrentUser();

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

	/**
	 * Returns cart ID for current user.
	 * If the user is logged in - returns his ID so his cart will be properly loaded.
	 * Otherwise generates random string based on available user data to preserve it's cart.
	 *
	 * @return string Cart ID for currently logged in user.
	 */
	public function getCartIdForCurrentUser()
	{
		return $this->service->getCartIdForCurrentUser();
	}

	/**
	 * Creates cart from order ID.
	 *
	 * @param $cartId string Cart ID to use.
	 * @param $order Order Order to base cart on.
	 * @return Cart The cart.
	 */
	public function createFromOrder($cartId, $order)
	{
		return $this->createFromOrder($cartId, $order);
	}
}
