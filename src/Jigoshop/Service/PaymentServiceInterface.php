<?php

namespace Jigoshop\Service;

use Jigoshop\Exception;
use Jigoshop\Payment\Method;

/**
 * Interface for payment service.
 *
*@package Jigoshop\Service
 */
interface PaymentServiceInterface
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
