<?php

namespace Jigoshop\Entity\Product;

/**
 * Purchasable items interface.
 *
 * This interface is requirement for adding product to cart.
 *
 * @package Jigoshop\Entity\Product
 */
interface Purchasable
{
	/**
	 * Returns final price of the product.
	 *
	 * @return float Current product price
	 */
	public function getPrice();
}
