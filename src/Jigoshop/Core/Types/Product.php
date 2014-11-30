<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product\Variable;
use Jigoshop\Exception;
use Jigoshop\Service\ProductServiceInterface;
use Monolog\Registry;
use WPAL\Wordpress;

class Product implements Post
{
	const NAME = 'product';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var array */
	private $enabledTypes = array();

	public function __construct(\JigoshopContainer $di, Wordpress $wp, Options $options, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;

		$types = $options->getEnabledProductTypes();
		foreach ($types as $typeClass) {
			/** @var Types\Product\Type $type */
			$type = $di->get($typeClass);

			if (!($type instanceof Types\Product\Type)) {
				if (WP_DEBUG) {
					throw new Exception(sprintf(__('Invalid type definition! Offending class: "%s".', 'jigoshop'), $typeClass));
				}

				Registry::getInstance('jigoshop')->addWarning(sprintf('Invalid type definition! Offending class: "%s".', $typeClass));
				continue;
			}

			$this->enabledTypes[$type->getId()] = $type;
			$productService->addType($type->getId(), $type->getClass());
			$wp->addAction('jigoshop\product\type\init', array($type, 'initialize'), 10, 2);
		}

		$wp->doAction('jigoshop\product\type\init', $wp, $this->enabledTypes);
	}

	public function getName()
	{
		return self::NAME;
	}

	public function getDefinition()
	{
		return array(
			'labels' => array(
				'name' => __('Products', 'jigoshop'),
				'singular_name' => __('Product', 'jigoshop'),
				'all_items' => __('All Products', 'jigoshop'),
				'add_new' => __('Add New', 'jigoshop'),
				'add_new_item' => __('Add New Product', 'jigoshop'),
				'edit' => __('Edit', 'jigoshop'),
				'edit_item' => __('Edit Product', 'jigoshop'),
				'new_item' => __('New Product', 'jigoshop'),
				'view' => __('View Product', 'jigoshop'),
				'view_item' => __('View Product', 'jigoshop'),
				'search_items' => __('Search Products', 'jigoshop'),
				'not_found' => __('No Products found', 'jigoshop'),
				'not_found_in_trash' => __('No Products found in trash', 'jigoshop'),
				'parent' => __('Parent Product', 'jigoshop'),
			),
			'description' => __('This is where you can add new products to your store.', 'jigoshop'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'product',
			'map_meta_cap' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'hierarchical' => false, // Hierarchical causes a memory leak http://core.trac.wordpress.org/ticket/15459
			'rewrite' => array(
				'slug' => $this->options->get('permalinks.product'),
				'with_front' => true,
				'feeds' => true,
				'pages' => true,
			),
			'query_var' => true,
			'supports' => array('title', 'editor', 'thumbnail', 'comments', 'excerpt'),
			'has_archive' => true,
			'show_in_nav_menus' => false,
			'menu_position' => 56,
			'menu_icon' => 'dashicons-book',
		);
	}

	/**
	 * @return array
	 */
	public function getEnabledTypes()
	{
		return $this->enabledTypes;
	}

	/**
	 * Finds and returns type instance of specified product type.
	 *
	 * @param $type string Name of the type.
	 * @return Types\Product\Type Type instance.
	 */
	public function getType($type)
	{
		if (!isset($this->enabledTypes[$type])) {
			throw new Exception(sprintf(__('Unknown type: "%s".', 'jigoshop'), $type));
		}

		return $this->enabledTypes[$type];
	}
}
