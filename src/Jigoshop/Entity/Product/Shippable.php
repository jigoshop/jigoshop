<?php

namespace Jigoshop\Entity\Product;

/**
 * Shippable items interface.
 *
 * This interface is requirement for shipping the product..
 *
 * @package Jigoshop\Entity\Product
 */
interface Shippable
{
	/**
	 * Checks whether the product requires shipping.
	 *
	 * @return bool Whether the product requires shipping.
	 */
	public function isShippable();
}
