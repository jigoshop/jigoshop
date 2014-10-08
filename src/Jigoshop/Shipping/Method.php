<?php

namespace Jigoshop\Shipping;

use Jigoshop\Frontend\Cart;

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
	 * @param Cart $cart Cart to calculate shipping for.
	 * @return float Calculates value of shipping for the cart.
	 */
	public function calculate(Cart $cart);

	/**
	 * @return array Minimal state to fully identify shipping method.
	 */
	public function getState();
}
