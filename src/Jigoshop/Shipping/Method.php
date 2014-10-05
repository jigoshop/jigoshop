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
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled();

	/**
	 * @return array List of options to display on Shipping settings page.
	 */
	public function getOptions();

	/**
	 * @param Cart $cart Cart to calculate shipping for.
	 * @return float Calculates value of shipping for the cart.
	 */
	public function calculate(Cart $cart);
}
