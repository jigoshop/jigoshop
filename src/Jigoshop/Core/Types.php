<?php

namespace Jigoshop\Core;

use Jigoshop\Core\Types\Post;
use Jigoshop\Core\Types\Taxonomy;
use WPAL\Wordpress;

/**
 * Registers required post types.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class Types
{
	// Post Types
	const PRODUCT = Types\Product::NAME;
	const ORDER = Types\Order::NAME;
//	const COUPON = 'shop_coupon';
	const EMAIL = Types\Email::NAME;

	// Taxonomy types
	const PRODUCT_CATEGORY = Types\ProductCategory::NAME;
	const PRODUCT_TAG = Types\ProductTag::NAME;

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var array */
	private $types = array();
	/** @var array */
	private $taxonomies = array();

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
	}

	public function addPostType(Post $type)
	{
		$this->types[] = $type;
		$this->wp->registerPostType($type->getName(), $type->getDefinition());
	}

	public function getPostTypes()
	{
		return $this->types;
	}

	public function addTaxonomy(Taxonomy $taxonomy)
	{
		$this->taxonomies[] = $taxonomy;
		$this->wp->registerTaxonomy($taxonomy->getName(), $taxonomy->getPostTypes(), $taxonomy->getDefinition());
	}

	public function getTaxonomies()
	{
		return $this->taxonomies;
	}
}
