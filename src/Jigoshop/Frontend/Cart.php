<?php

namespace Jigoshop\Frontend;

class Cart
{
	/**
	 * @return string Cart ID.
	 */
	public function getId()
	{
		// TODO: Implement
		return '';
	}

	/**
	 * Adds item to the cart.
	 *
	 * If item is already present - increases it's quantity.
	 *
	 * @param Product $product Product to add to cart.
	 * @param $quantity int Quantity of products to add.
	 */
	public function addItem(Product $product, $quantity)
	{
		// TODO: Implement
	}

	/**
	 * Removes item from cart.
	 *
	 * @param Product $product Product to remove from cart.
	 */
	public function removeItem(Product $product)
	{
		// TODO: Implement
	}

	/**
	 * @return array List of items in the cart.
	 */
	public function getItems()
	{
		// TODO: Implement
	}

	/**
	 * @return float Current total value of the cart.
	 */
	public function getTotal()
	{
		// TODO: Implement
	}
}
