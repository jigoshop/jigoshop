<?php

namespace Jigoshop\Frontend;

class Cart implements \Serializable
{
	/** @var string */
	private $id;

	public function __construct($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string Cart ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Adds item to the cart.
	 *
	 * If item is already present - increases it's quantity.
	 *
	 * @param Product $product Product to add to cart.
	 * @param $quantity int Quantity of products to add.
	 */
	public function addItem(Product $product, $quantity)
	{
		// TODO: Implement
	}

	/**
	 * Removes item from cart.
	 *
	 * @param Product $product Product to remove from cart.
	 */
	public function removeItem(Product $product)
	{
		// TODO: Implement
	}

	/**
	 * @return array List of items in the cart.
	 */
	public function getItems()
	{
		// TODO: Implement
	}

	/**
	 * @return float Current total value of the cart.
	 */
	public function getTotal()
	{
		// TODO: Implement
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
		// TODO: Implement serialize() method.
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
		// TODO: Implement unserialize() method.
	}
}
