<?php

namespace Jigoshop\Core\Types;

/**
 * Interface for custom taxonomies
 *
 * @package Jigoshop\Core\Types
 * @author Amadeusz Starzykiewicz
 */
interface Taxonomy
{
	/**
	 * Returns name which taxonomy will be registered under.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns list of parent post types which taxonomy will be registered under.
	 *
	 * @return array
	 */
	public function getPostTypes();

	/**
	 * Returns full definition of the taxonomy.
	 *
	 * @return array
	 */
	public function getDefinition();
}