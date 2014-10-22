<?php

namespace Jigoshop\Entity\Order;

use Jigoshop\Entity\Product;

/**
 * Order item.
 *
 * @package Jigoshop\Entity\Order
 * @author Amadeusz Starzykiewicz
 */
class Item
{
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var int */
	private $quantity;
	/** @var float */
	private $price;
	/** @var Product */
	private $product;
	/** @var string */
	private $type;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return int
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * @param int $quantity
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return $this->price;
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
	public function getCost()
	{
		return $this->price * $this->quantity;
	}

	/**
	 * @return Product|null
	 */
	public function getProduct()
	{
		return $this->product;
	}

	/**
	 * @param Product $product
	 */
	public function setProduct(Product $product)
	{
		$this->product = $product;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}
}
