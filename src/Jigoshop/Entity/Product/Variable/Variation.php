<?php

namespace Jigoshop\Entity\Product\Variable;

use Jigoshop\Entity\Product;
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
	private $parent;
	/** @var Product|Product\Purchasable */
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
	 * TODO: Speed improvements.
	 * @return string Variation title.
	 */
	public function getTitle()
	{
		// TODO: Title changing description in docs
		return sprintf(_x('%s (%s)', 'product_variation', 'jigoshop'), $this->parent->getName(), join(', ', array_filter(array_map(function($item){
			/** @var $item Attribute */
			$value = $item->getValue();
			if (is_numeric($value) && $value > 0) {
				return sprintf(_x('%s: %s', 'product_variation', 'jigoshop'), $item->getAttribute()->getLabel(), $item->getAttribute()->getOption($value)->getLabel());
			}

			return '';
		}, $this->attributes))));
	}

	/**
	 * @return Variable
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @param Variable $parent
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
	}

	/**
	 * @return Product|Product\Purchasable
	 */
	public function getProduct()
	{
		return $this->product;
	}

	/**
	 * @param Product|Product\Purchasable $product
	 */
	public function setProduct($product)
	{
		$this->product = $product;
	}

	/**
	 * @param Attribute $attribute
	 */
	public function addAttribute($attribute)
	{
		$attribute->setVariation($this);
		$this->attributes[$attribute->getAttribute()->getId()] = $attribute;
	}

	/**
	 * @param $id int Attribute ID.
	 * @return bool Whether variation already has this attribute.
	 */
	public function hasAttribute($id)
	{
		return isset($this->attributes[$id]);
	}

	/**
	 * @param $id int Attribute ID.
	 * @return Attribute Variation attribute.
	 */
	public function getAttribute($id)
	{
		if (!isset($this->attributes[$id])) {
			return null;
		}

		return $this->attributes[$id];
	}

	/**
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}
}
