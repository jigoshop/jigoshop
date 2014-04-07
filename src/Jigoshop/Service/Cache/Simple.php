<?php

namespace Jigoshop\Service\Cache;

use Jigoshop\Entity\EntityInterface;
use Jigoshop\Service\ServiceInterface;

/**
 * Simple cache class for Jigoshop services.
 *
 * @package Jigoshop\Service\Cache
 */
class Simple implements ServiceInterface
{
	private $objects = array();
	private $queries = array();

	/** @var \Jigoshop\Service\ServiceInterface */
	private $service;

	public function __construct(ServiceInterface $service)
	{
		$this->service = $service;
	}

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return \stdClass
	 */
	public function find($id)
	{
		if(!isset($this->objects[$id]))
		{
			$this->objects[$id] = $this->service->find($id);
		}
		return $this->objects[$id];
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery(\WP_Query $query)
	{
		// TODO: Check on various occasions if this is sufficient as hashing method.
		$hash = hash('md5', serialize($query->query_vars));

		if(!isset($this->queries[$hash]))
		{
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
}