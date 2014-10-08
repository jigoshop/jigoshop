<?php

namespace Jigoshop\Service;

use Jigoshop\Exception;
use Jigoshop\Frontend\Cart;
use Jigoshop\Shipping\Method;

/**
 * Service for managing shipping methods.
 *
 * @package Jigoshop\Service
 */
class Shipping implements ShippingServiceInterface
{
	private $methods = array();

	/**
	 * Adds new method to service.
	 *
	 * @param Method $method Method to add.
	 */
	public function addMethod(Method $method)
	{
		$this->methods[$method->getId()] = $method;
	}

	/**
	 * Returns method by its ID.
	 *
	 * @param $id string ID of method.
	 * @return Method Method found.
	 * @throws Exception When no method is found for specified ID.
	 */
	public function get($id)
	{
		if (!isset($this->methods[$id])) {
			throw new Exception(sprintf(__('Method "%s" does not exists', 'jigoshop'), $id));
		}

		return $this->methods[$id];
	}

	/**
	 * Finds item specified by state.
	 *
	 * @param array $state State of the method to be found.
	 * @return Method Method found.
	 */
	public function findForState(array $state)
	{
		$method = $this->get($state['id']);
		// TODO: Maybe some kind of state refreshing?
		return $method;
	}

	/**
	 * Finds and returns ID of cheapest available shipping method.
	 *
	 * @param Cart $cart Cart to calculate method prices for.
	 * @return string ID of cheapest shipping method.
	 */
	public function getCheapest(Cart $cart)
	{
		// TODO: Implement getCheapest() method.
	}

	/**
	 * Returns list of available shipping methods.
	 *
	 * @return array List of available shipping methods.
	 */
	public function getAvailable()
	{
		return $this->methods;
	}
}
