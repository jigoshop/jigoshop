<?php

namespace Jigoshop\Frontend;

use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Cart implements \Serializable
{
	/** @var string */
	private $id;
	private $items = array();
	private $total = 0.0;

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
	 * @param Product|Product\Purchasable $product Product to add to cart.
	 * @param $quantity int Quantity of products to add.
	 * @throws Exception On error.
	 */
	public function addItem(Product $product, $quantity)
	{
		if ($product === null || $product->getId() === 0) {
			throw new Exception(__('Product not found', 'jigoshop'));
		}

		if (!($product instanceof Product\Purchasable)) {
			throw new Exception(sprintf(__('Product of type "%s" cannot be added to cart', 'jigoshop'), $product->getType()));
		}

		if ($quantity <= 0) {
			throw new Exception(__('Quantity has to be positive number', 'jigoshop'));
		}

		if (isset($this->items[$product->getId()])) {
			$this->items[$product->getId()]['quantity'] += $quantity;
		} else {
			$this->items[$product->getId()] = array(
				'item' => $product->getState(),
				'price' => $product->getPrice(),
				'quantity' => $quantity,
			);
		}

		$this->total += $quantity * $product->getPrice();
	}

	/**
	 * Removes item from cart.
	 *
	 * @param string $key Item id to remove from cart.
	 * @return bool Is item removed?
	 */
	public function removeItem($key)
	{
		if (isset($this->items[$key])) {
			$this->total -= $this->items[$key]['price'] * $this->items[$key]['quantity'];
			unset($this->items[$key]);
		}

		return true;
	}

	public function getRemoveUrl($key)
	{
		return add_query_arg(array('action' => 'remove-item', 'item' => $key));
	}

	/**
	 * Updates quantity of selected item by it's key.
	 *
	 * @param $key string Item key in the cart.
	 * @param $quantity int Quantity to set.
	 * @throws Exception When product does not exists or quantity is not numeric.
	 */
	public function updateQuantity($key, $quantity)
	{
		if (!isset($this->items[$key])) {
			throw new Exception(__('Item does not exists', 'jigoshop')); // TODO: Will be nice to get better error message
		}

		if (!is_numeric($quantity)) {
			throw new Exception(__('Quantity has to be numeric value', 'jigoshop'));
		}

		if ($quantity <= 0) {
			$this->removeItem($key);
			return;
		}

		$this->total -= $this->items[$key]['price'] * $this->items[$key]['quantity'];
		$this->total += $this->items[$key]['price'] * $quantity;
		$this->items[$key]['quantity'] = $quantity;
	}

	public function getItem($key)
	{
		if (!isset($this->items[$key])) {
			throw new Exception(__('Item does not exists', 'jigoshop')); // TODO: Will be nice to get better error message
		}

		return $this->items[$key];
	}

	/**
	 * @return array List of items in the cart.
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return bool Is the cart empty?
	 */
	public function isEmpty()
	{
		return empty($this->items);
	}

	/**
	 * @return float Current total value of the cart.
	 */
	public function getTotal()
	{
		return $this->total;
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
			'items' => $this->items,
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
		$this->items = $data['items'];
		foreach ($this->items as $key => $item){
			$this->total += $item['price'] * $item['quantity'];
		}
	}
}
