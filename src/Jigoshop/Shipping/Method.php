<?php

namespace Jigoshop\Shipping;

use Jigoshop\Entity\OrderInterface;

/**
 * Shipping method interface.
 *
 * @package Jigoshop\Shipping
 */
interface Method
{
	/**
	 * @return string ID of shipping method.
	 */
	public function getId();

	/**
	 * @return string Human readable name of method.
	 */
	public function getName();

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled();

	/**
	 * @return array List of options to display on Shipping settings page.
	 */
	public function getOptions();

	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses();

	/**
	 * Validates and returns properly sanitized options.
	 *
	 * @param $settings array Input options.
	 * @return array Sanitized result.
	 */
	public function validateOptions($settings);

	/**
	 * Checks whether current method is the one specified with selected rule.
	 *
	 * @param Method $method Method to check.
	 * @param Rate $rate Rate to check.
	 * @return boolean Is this the method?
	 */
	public function is(Method $method, $rate = null);

	/**
	 * @param OrderInterface $order Order to calculate shipping for.
	 * @return float Calculates value of shipping for the order.
	 */
	public function calculate(OrderInterface $order);

	/**
	 * @return array Minimal state to fully identify shipping method.
	 */
	public function getState();

	/**
	 * Restores shipping method state.
	 *
	 * @param array $state State to restore.
	 */
	public function restoreState(array $state);
}
