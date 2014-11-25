<?php

namespace Jigoshop\Entity\Order\Item;

use Jigoshop\Entity\Order\Item;

class Meta
{
	/** @var Item */
	private $item;
	/** @var string */
	private $key;
	/** @var mixed */
	private $value;

	/**
	 * @return Item
	 */
	public function getItem()
	{
		return $this->item;
	}

	/**
	 * @param Item $item
	 */
	public function setItem($item)
	{
		$this->item = $item;
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
}
