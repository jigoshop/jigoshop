<?php

namespace Jigoshop\Entity\Order;

use Jigoshop\Entity\Product;

/**
 * Order item.
 *
 * @package Jigoshop\Entity\Order
 * @author Jigoshop
 */
class Item
{
	private $id;
	private $quantity;
	private $price;
	private $product;

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param float $price
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param Product $product
	 */
	public function setProduct(Product $product)
	{
		$this->product = $product;
	}

	/**
	 * @return Product
	 */
	public function getProduct()
	{
		return $this->product;
	}

	/**
	 * @param int $quantity
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
	}

	/**
	 * @return int
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

}