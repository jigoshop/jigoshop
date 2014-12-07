<?php

namespace Jigoshop\Entity\Order\Item;

use Jigoshop\Entity\Order\Item;

class Meta implements \Serializable
{
	/** @var Item */
	private $item;
	/** @var string */
	private $key;
	/** @var mixed */
	private $value;

	public function __construct($key = null, $value = null)
	{
		$this->key = $key;
		$this->value = $value;
	}

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

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return serialize(array(
			'item' => $this->item->getId(),
			'key' => $this->key,
			'value' => $this->value,
		));
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 * @return void
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->key = $data['key'];
		$this->value = $data['value'];
		// TODO: How to properly unserialize item?
	}
}
