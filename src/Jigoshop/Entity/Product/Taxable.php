<?php

namespace Jigoshop\Entity\Product;

/**
 * Taxable items interface.
 *
 * This interface is requirement for calculating tax for the product.
 *
 * @package Jigoshop\Entity\Product
 */
interface Taxable
{
	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses();
}
