<?php

namespace Jigoshop\Service\Cache;

use Jigoshop\Service\ServiceInterface;

/**
 * Simple cache class for Jigoshop services.
 *
 * @package Jigoshop\Service\Cache
 */
class Simple implements ServiceInterface
{
	private $_cache = array();

	/** @var \Jigoshop\Service\ServiceInterface */
	private $_service;

	public function __construct(ServiceInterface $service)
	{
		$this->_service = $service;
	}

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return \stdClass
	 */
	public function find($id)
	{
		if(!isset($this->_cache[$id]))
		{
			$this->_cache[$id] = $this->_service->find($id);
		}
		return $this->_cache[$id];
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

		if(!isset($this->_cache[$hash]))
		{
			$this->_cache[$hash] = $this->_service->findByQuery($query);
		}

		return $this->_cache[$hash];
	}
}