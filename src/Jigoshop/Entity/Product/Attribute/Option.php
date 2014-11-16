<?php

namespace Jigoshop\Entity\Product\Attribute;

use Jigoshop\Entity\Product\Attribute;

class Option
{
	/** @var int */
	private $id;
	/** @var string */
	private $label;
	/** @var mixed */
	private $value;
	/** @var Attribute */
	private $attribute;

	/**
	 * @return int Option ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id New ID for option.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string Option label.
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @param string $label New label.
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return mixed Option value.
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value New value.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return Attribute Associated attribute.
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}

	/**
	 * @param Attribute $attribute Attribute to attach option to.
	 */
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
	}
}
