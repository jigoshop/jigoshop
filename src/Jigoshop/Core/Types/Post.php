<?php

namespace Jigoshop\Core\Types;

/**
 * Interface for custom post types
 *
 * @package Jigoshop\Core\Types
 * @author Amadeusz Starzykiewicz
 */
interface Post
{
	/**
	 * Returns name which type will be registered under.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns full definition of the type.
	 *
	 * @return array
	 */
	public function getDefinition();
}