<?php

namespace Jigoshop\Shipping;

use Jigoshop\Entity\OrderInterface;

/**
 * Shipping rate model.
 *
 * @package Jigoshop\Shipping
 */
class Rate
{
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var float */
	private $price;
	/** @var Method */
	private $method;

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
		return sprintf(_x('%s - %s', 'shipping', 'jigoshop'), $this->method->getName(), $this->name);
	}

	/**
	 * @param string $label
	 */
	public function setName($label)
	{
		$this->name = $label;
	}

	/**
	 * @return Method
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @param Method $method
	 */
	public function setMethod($method)
	{
		$this->method = $method;
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

	public function calculate(OrderInterface $order)
	{
		return $this->price;
	}
}
