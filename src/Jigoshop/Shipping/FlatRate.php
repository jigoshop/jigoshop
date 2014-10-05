<?php

namespace Jigoshop\Shipping;

use Jigoshop\Core\Options;
use Jigoshop\Frontend\Cart;

class FlatRate implements Method
{
	const NAME = 'flat_rate';

	/** @var array */
	private $options;

	public function __construct(Options $options)
	{
		$this->options = $options->get('shipping.'.self::NAME);
	}

	/**
	 * @return string ID of shipping method.
	 */
	public function getId()
	{
		return self::NAME;
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		// TODO: Implement isEnabled() method.
		return $this->options['enabled'];
	}

	/**
	 * @return array List of options to display on Shipping settings page.
	 */
	public function getOptions()
	{
		// TODO: Implement getOptions() method.
	}

	/**
	 * @param Cart $cart Cart to calculate shipping for.
	 * @return float Calculates value of shipping for the cart.
	 */
	public function calculate(Cart $cart)
	{
		// TODO: Implement calculate() method.
	}
}
