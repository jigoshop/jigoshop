<?php

namespace Jigoshop\Entity\Order;

use Jigoshop\Entity\Product;
use Jigoshop\Exception;

/**
 * Order item.
 *
 * TODO: Proper description in PhpDoc
 *
 * @package Jigoshop\Entity\Order
 * @author Amadeusz Starzykiewicz
 */
class Item implements Product\Purchasable, Product\Taxable, \Serializable
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
	/** @var Product|Product\Purchasable|Product\Shippable */
	private $product;
	/** @var string */
	private $type;
	/** @var array */
	private $meta = array();

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
	 * @throws Exception When quantity is invalid.
	 */
	public function setQuantity($quantity)
	{
		if ($quantity < 0) {
			// TODO: Log message.
			throw new Exception(__('Item quantity cannot be below 0', 'jigoshop'));
		}

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
		// TODO: Support for "Price includes tax"
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

	/**
	 * Adds meta value to the item.
	 *
	 * @param Item\Meta $meta Meta value to add.
	 */
	public function addMeta(Item\Meta $meta)
	{
		$meta->setItem($this);
		$this->meta[$meta->getKey()] = $meta;
	}

	/**
	 * Removes meta value from the item and returns it.
	 *
	 * @param string $key Meta key.
	 * @return Item\Meta Meta object.
	 */
	public function removeMeta($key)
	{
		$meta = $this->getMeta($key);

		if ($meta === null) {
			return null;
		}

		unset($this->meta[$key]);
		return $meta;
	}

	/**
	 * Returns single meta object.
	 *
	 * @param string $key Meta key.
	 * @return Item\Meta Meta object.
	 */
	public function getMeta($key)
	{
		if (!isset($this->meta[$key])) {
			return null;
		}

		return $this->meta[$key];
	}

	/**
	 * @return array All meta values assigned to the item.
	 */
	public function getAllMeta()
	{
		return $this->meta;
	}

	/**
	 * Checks whether the product requires shipping.
	 *
	 * @return bool Whether the product requires shipping.
	 */
	public function isShippable()
	{
		return $this->product->isShippable();
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
			'id' => $this->id,
			'name' => $this->name,
			'type' => $this->type,
			'quantity' => $this->quantity,
			'price' => $this->price,
			'tax' => serialize($this->tax),
			'product' => $this->product->getState(),
			'meta' => serialize($this->meta),
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
		$this->id = $data['id'];
		$this->name = $data['name'];
		$this->type = $data['type'];
		$this->quantity = $data['quantity'];
		$this->price = $data['price'];
		$this->tax = unserialize($data['tax']);
		$this->totalTax = array_reduce($this->tax, function($value, $tax){ return $value + $tax; }, 0.0);
		$this->meta = unserialize($data['meta']);
		// TODO: How to properly unserialize product?
		$this->product = $data['product'];

		foreach ($this->meta as $meta) {
			$meta->setItem($this);
		}
	}
}
