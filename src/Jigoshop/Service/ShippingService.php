<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Cart;
use Jigoshop\Exception;
use Jigoshop\Shipping\Dummy;
use Jigoshop\Shipping\Method;

/**
 * Service for managing shipping methods.
 *
 * @package Jigoshop\Service
 */
class ShippingService implements ShippingServiceInterface
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
	 * Finds item specified by state.
	 *
	 * @param array $state State of the method to be found.
	 * @return Method Method found.
	 */
	public function findForState(array $state)
	{
		$method = $this->get($state['id']);
		$method->restoreState($state);
		return $method;
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
			Registry::getInstance(JIGOSHOP_LOGGER)->addWarning(sprintf(__('Shipping method "%s" does not exists', 'jigoshop'), $id));

			return new Dummy($id);
		}

		return $this->methods[$id];
	}

	/**
	 * Finds and returns ID of cheapest available shipping method.


*
*@param \Jigoshop\Entity\Cart $cart Cart to calculate method prices for.
	 * @return string ID of cheapest shipping method.
	 */
	public function getCheapest(Cart $cart)
	{
		$cheapest = null;
		$cheapestPrice = PHP_INT_MAX;

		foreach ($this->getEnabled() as $method) {
			/** @var Method $method */
			$price = $method->calculate($cart);

			if ($price < $cheapestPrice) {
				$cheapest = $method;
			}
		}

		return $cheapest;
	}

	/**
	 * Returns list of enabled shipping methods.
	 *
	 * @return array List of enabled shipping methods.
	 */
	public function getEnabled()
	{
		return array_filter($this->methods, function($method){
			/** @var $method Method */
			return $method->isEnabled();
		});
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
