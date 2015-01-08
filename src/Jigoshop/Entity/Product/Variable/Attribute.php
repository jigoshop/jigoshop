<?php

namespace Jigoshop\Entity\Product\Variable;

use Jigoshop\Entity\Order\Item;

/**
 * Attribute of variable product.
 *
 * @package Jigoshop\Entity\Product\Variable
 */
class Attribute
{
	const VARIATION_ATTRIBUTE_EXISTS = true;

	/** @var Variation */
	private $variation;
	/** @var \Jigoshop\Entity\Product\Attribute */
	private $attribute;
	/** @var mixed */
	private $value = 0;
	/** @var bool */
	private $exists;

	public function __construct($exists = false)
	{
		$this->exists = $exists;
	}

	/**
	 * @return boolean Is this attribute in the database?
	 */
	public function exists()
	{
		return $this->exists;
	}

	/**
	 * @param $exists boolean Set attribute to be in the database or not.
	 */
	public function setExists($exists)
	{
		$this->exists = $exists;
	}

	/**
	 * @return \Jigoshop\Entity\Product\Attribute
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}

	/**
	 * @param \Jigoshop\Entity\Product\Attribute $attribute
	 */
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return Variation
	 */
	public function getVariation()
	{
		return $this->variation;
	}

	/**
	 * @param Variation $variation
	 */
	public function setVariation($variation)
	{
		$this->variation = $variation;
	}

	/**
	 * @param $item Item
	 * @return string
	 */
	public function getItemValue($item)
	{
		if ($this->value !== '') {
			$value = $this->value;
		} else {
			$value = $item->getMeta($this->attribute->getSlug())->getValue();
		}

		return $this->getAttribute()->getOption($value)->getLabel();
	}

	/**
	 * @param $item Item
	 */
	public function printValue($item)
	{
		echo $this->getItemValue($item);
	}
}
