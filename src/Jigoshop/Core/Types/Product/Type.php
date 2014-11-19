<?php

namespace Jigoshop\Core\Types\Product;

use WPAL\Wordpress;

interface Type
{
	/**
	 * Returns identifier for the type.
	 *
	 * @return string Type identifier.
	 */
	public function getId();

	/**
	 * Returns human-readable name for the type.
	 *
	 * @return string Type name.
	 */
	public function getName();

	/**
	 * Returns class name to use as type entity.
	 * This class MUST extend {@code \Jigoshop\Entity\Product}!
	 *
	 * @return string Fully qualified class name.
	 */
	public function getClass();

	/**
	 * Initializes product type.
	 *
	 * @param Wordpress $wp WordPress Abstraction Layer
	 */
	public function initialize(Wordpress $wp);
}
