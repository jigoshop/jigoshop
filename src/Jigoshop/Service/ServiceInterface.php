<?php

namespace Jigoshop\Service;

/**
 * Interface for Jigoshop services.
 *
 * @package Jigoshop\Service
 * @author Jigoshop
 */
interface ServiceInterface
{
	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return \stdClass
	 */
	public function find($id);

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery(\WP_Query $query);
}