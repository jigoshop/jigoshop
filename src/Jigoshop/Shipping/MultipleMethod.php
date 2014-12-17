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
}
