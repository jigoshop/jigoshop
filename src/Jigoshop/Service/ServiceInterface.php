<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\EntityInterface;

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
	 * @return EntityInterface
	 */
	public function find($id);

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery(\WP_Query $query);

	/**
	 * Saves entity to database.
	 *
	 * @param $object EntityInterface Entity to save.
	 */
	public function save(EntityInterface $object);
}