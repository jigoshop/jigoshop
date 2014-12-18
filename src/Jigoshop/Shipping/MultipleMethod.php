<?php

namespace Jigoshop\Shipping;

interface MultipleMethod extends Method
{
	/**
	 * Returns list of available shipping rates.
	 *
	 * @return array List of available shipping rates.
	 */
	public function getRates();

	/**
	 * @param $rate int Rate to use.
	 */
	public function setShippingRate($rate);

	/**
	 * @return int Currently used rate.
	 */
	public function getShippingRate();
}
