<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product\Sales;

/**
 * Interface for items with ability to be on sale.
 *
 * This interface is requirement for putting the product on sale.
 *
 * @package Jigoshop\Entity\Product
 */
interface Saleable
{
	/**
	 * Definition for sale.
	 *
	 * @return Attributes\Sales Current product sales data.
	 */
	public function getSales();
}
