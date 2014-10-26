<?php

namespace Jigoshop\Entity\Order;

use Jigoshop\Entity\Product;

/**
 * Order item.
 *
 * TODO: Proper description in PhpDoc
 *
 * @package Jigoshop\Entity\Order
 * @author Amadeusz Starzykiewicz
 */
class Item implements Product\Purchasable, Product\Taxable
{
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var int */
	private $quantity = 0;
	/** @var float */
	private $price = 0.0;
	/** @var array */
	private $tax = array();
	/** @var float */
	private $totalTax = 0.0;
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
	 * @return array
	 */
	public function getTax()
	{
		return $this->tax;
	}

	/**
	 * @param array $tax
	 */
	public function setTax($tax)
	{
		$tax = array_filter($tax);
		$this->tax = $tax;
		$this->totalTax = array_reduce($tax, function($value, $item) { return $value + $item; }, 0.0);
	}

	/**
	 * @return float
	 */
	public function getTotalTax()
	{
		return $this->totalTax * $this->quantity;
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

	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses()
	{
		return array_keys($this->tax);
	}
}
