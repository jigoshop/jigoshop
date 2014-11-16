<?php

namespace Jigoshop\Entity\Product\Attribute;

use Jigoshop\Entity\Product\Attribute;

class Field
{
	/** @var int */
	private $id;
	/** @var Attribute */
	private $attribute;
	/** @var string */
	private $key;
	/** @var string */
	private $value;

	public function __construct($key = null, $value = null)
	{
		$this->key = $key;
		$this->value = $value;
	}

	/**
	 * @return Attribute
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}

	/**
	 * @param Attribute $attribute
	 */
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
	}

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
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
}
