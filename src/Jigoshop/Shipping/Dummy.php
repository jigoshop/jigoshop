<?php

namespace Jigoshop\Shipping;

use Jigoshop\Core\Types;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Exception;
use WPAL\Wordpress;

class Dummy implements Method
{
	private $id;
	private $label;

	public function __construct($id, $label = null)
	{
		$this->id = $id;
		$this->label = $label !== null ? $label : $id;
	}

	/**
	 * @return string ID of shipping method.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string Human readable name of method.
	 */
	public function getName()
	{
		return $this->label;
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		return false;
	}

	/**
	 * @return bool Whether current method is taxable.
	 */
	public function isTaxable()
	{
		return false;
	}

	/**
	 * @return array List of options to display on Shipping settings page.
	 */
	public function getOptions()
	{
		return array();
	}

	/**
	 * Validates and returns properly sanitized options.
	 *
	 * @param $settings array Input options.
	 * @return array Sanitized result.
	 */
	public function validateOptions($settings)
	{
		return $settings;
	}

	/**
	 * @param OrderInterface $order Order to calculate shipping for.
	 * @return float Calculated value of shipping for the order.
	 * @throws Exception On error.
	 */
	public function calculate(OrderInterface $order)
	{
		throw new Exception(sprintf(__('Shipping method "%s" does not exist in the system. This should never happen, please contact Jigoshop support.', 'jigoshop'), $this->id));
	}

	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses()
	{
		return array();
	}

	/**
	 * @return array Minimal state to fully identify shipping method.
	 */
	public function getState()
	{
		return array();
	}

	/**
	 * Restores shipping method state.
	 *
	 * @param array $state State to restore.
	 */
	public function restoreState(array $state)
	{
		// Empty
	}

	/**
	 * Checks whether current method is the one specified with selected rule.
	 *
	 * @param Method $method Method to check.
	 * @param int $rate Rate to check.
	 * @return boolean Is this the method?
	 */
	public function is(Method $method, $rate = null)
	{
		return false;
	}
}
