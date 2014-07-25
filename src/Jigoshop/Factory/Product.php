<?php

namespace Jigoshop\Factory;

use Jigoshop\Entity\Product\Type\Simple;
use Jigoshop\Exception;

class Product
{
	private $types = array();

	/**
	 * Adds new type to managed types.
	 *
	 * @param $type string Unique type name.
	 * @param $class string Class name.
	 * @throws \Jigoshop\Exception When type already exists.
	 */
	public function addType($type, $class)
	{
		if (isset($this->types[$type])) {
			throw new Exception(sprintf('Product of type %s already exists.'), $type);
		}

		$this->types[$type] = $class;
	}

	/**
	 * Returns empty product of selected type.
	 *
	 * @param $type string Type name of product.
	 * @throws \Jigoshop\Exception When product type does not exists.
	 * @return \Jigoshop\Entity\Product
	 */
	public function get($type)
	{
		if (!isset($this->types[$type])) {
			throw new Exception(sprintf('Product type %s does not exists.', $type));
		}

		$class = $this->types[$type];
		return new $class($this->wp);
	}

	/**
	 * Creates new product properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return \Jigoshop\Entity\Product
	 */
	public function create($id)
	{
		$type = isset($_POST['product']['type']) ? $_POST['product']['type'] : Simple::TYPE;
		$product = $this->get($type);
		$product->setId($id);
		// TODO: Implement
		return $product;
	}
}
