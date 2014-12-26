<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Cart;
use Jigoshop\Exception;
use Jigoshop\Shipping\Method;

/**
 * Interface for shipping service.

 *
*@package Jigoshop\Service
 */
interface ShippingServiceInterface
{
	/**
	 * Adds new method to service.
	 *
	 * @param Method $method Method to add.
	 */
	public function addMethod(Method $method);

	/**
	 * Returns method by its ID.
	 *
	 * @param $id string ID of method.
	 * @return Method Method found.
	 * @throws Exception When no method is found for specified ID.
	 */
	public function get($id);

	/**
	 * Finds item specified by state.
	 *
	 * @param array $state State of the method to be found.
	 * @return Method Method found.
	 */
	public function findForState(array $state);

	/**
	 * Finds and returns ID of cheapest available shipping method.
	 *
	 * @param Cart $cart Cart to calculate method prices for.
	 * @return string ID of cheapest shipping method.
	 */
	public function getCheapest(Cart $cart);

	/**
	 * Returns list of available shipping methods.
	 *
	 * @return array List of available shipping methods.
	 */
	public function getAvailable();

	/**
	 * Returns list of enabled shipping methods.
	 *
	 * @return array List of enabled shipping methods.
	 */
	public function getEnabled();
}
