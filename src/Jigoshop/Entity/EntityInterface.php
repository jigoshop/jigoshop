<?php

namespace Jigoshop\Entity;

/**
 * Entity interface.
 *
 * @package Jigoshop\Entity
 * @author Jigoshop
 */
interface EntityInterface
{
	/**
	 * @return array List of changed fields (to update).
	 */
	public function getDirtyFields();

	/**
	 * @return int Entity ID.
	 */
	public function getId();

	/**
	 * @param $name string Name of attribute to retrieve.
	 * @return mixed
	 */
	public function get($name);
}