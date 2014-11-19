<?php

namespace Jigoshop\Entity\Product\Variable;

use Jigoshop\Entity\Product\Variable;

/**
 * Entity for variation of the product.
 *
 * @package Jigoshop\Entity\Product\Variable
 */
class Variation
{
	/** @var int */
	private $id;
	/** @var Variable */
	private $product;
	/** @var array */
	private $attributes = array();

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
	 * @return Variable
	 */
	public function getProduct()
	{
		return $this->product;
	}

	/**
	 * @param Variable $product
	 */
	public function setProduct($product)
	{
		$this->product = $product;
	}

	/**
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * @param Attribute $attribute
	 */
	public function addAttribute($attribute)
	{
		$this->attributes[] = $attribute;
	}
}
