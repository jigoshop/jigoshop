<?php

namespace Jigoshop\Service\Cache\Product;

use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Product;
use Jigoshop\Service\ProductServiceInterface;

/**
 * Simple cache class for Jigoshop products service.
 *
 * @package Jigoshop\Service\Cache\Product
 */
class Simple implements ProductServiceInterface
{
	private $objects = array();
	private $queries = array();

	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $service;

	public function __construct(ProductServiceInterface $service)
	{
		$this->service = $service;
	}

	/**
	 * Adds new type to managed types.
	 *
	 * @param $type string Unique type name.
	 * @param $class string Class name.
	 * @throws \Jigoshop\Exception When type already exists.
	 */
	public function addType($type, $class)
	{
		$this->service->addType($type, $class);
	}

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return Product
	 */
	public function find($id)
	{
		if (!isset($this->objects[$id])) {
			$this->objects[$id] = $this->service->find($id);
		}

		return $this->objects[$id];
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Product Item found.
	 */
	public function findForPost($post)
	{
		if (!isset($this->objects[$post->ID])) {
			$this->objects[$post->ID] = $this->service->findForPost($post);
		}

		return $this->objects[$post->ID];
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery($query)
	{
		// TODO: Check on various occasions if this is sufficient as hashing method.
		$hash = hash('md5', serialize($query->query_vars));

		if (!isset($this->queries[$hash])) {
			$this->queries[$hash] = $this->service->findByQuery($query);
		}

		return $this->queries[$hash];
	}

	/**
	 * Saves entity to database.
	 *
	 * @param $object EntityInterface Entity to save.
	 */
	public function save(EntityInterface $object)
	{
		$this->queries = array();
		$this->objects[$object->getId()] = $object;
		$this->service->save($object);
	}

	/**
	 * @return array List of products that are out of stock.
	 */
	public function findOutOfStock()
	{
		if (!isset($this->queries['out_of_stock'])) {
			$this->queries['out_of_stock'] = $this->service->findOutOfStock();
		}

		return $this->queries['out_of_stock'];
	}

	/**
	 * @param $threshold int Threshold where to assume product is low in stock.
	 * @return array List of products that are low in stock.
	 */
	public function findLowStock($threshold)
	{
		if (!isset($this->queries['low_stock_'.$threshold])) {
			$this->queries['low_stock_'.$threshold] = $this->service->findLowStock($threshold);
		}

		return $this->queries['low_stock_'.$threshold];
	}
}