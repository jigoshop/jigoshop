<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Options;

class ProductCategory implements Taxonomy
{
	const NAME = 'product_category';

	/** @var Options */
	private $options;

	public function __construct(Options $options)
	{
		$this->options = $options;
	}

	/**
	 * Returns name which taxonomy will be registered under.
	 *
	 * @return string
	 */
	public function getName()
	{
		return self::NAME;
	}

	/**
	 * Returns list of parent post types which taxonomy will be registered under.
	 *
	 * @return array
	 */
	public function getPostTypes()
	{
		return array(Product::NAME);
	}

	/**
	 * Returns full definition of the taxonomy.
	 *
	 * @return array
	 */
	public function getDefinition()
	{
		return array(
			'labels' => array(
				'menu_name' => __('Categories', 'jigoshop'),
				'name' => __('Product Categories', 'jigoshop'),
				'singular_name' => __('Product Category', 'jigoshop'),
				'search_items' => __('Search Product Categories', 'jigoshop'),
				'all_items' => __('All Product Categories', 'jigoshop'),
				'parent_item' => __('Parent Product Category', 'jigoshop'),
				'parent_item_colon' => __('Parent Product Category:', 'jigoshop'),
				'edit_item' => __('Edit Product Category', 'jigoshop'),
				'update_item' => __('Update Product Category', 'jigoshop'),
				'add_new_item' => __('Add New Product Category', 'jigoshop'),
				'new_item_name' => __('New Product Category Name', 'jigoshop'),
			),
			'capabilities' => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms' => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
			),
			'hierarchical' => true,
			'show_ui' => true,
			'query_var' => self::NAME,
			'rewrite' => array(
				'slug' => $this->options->get('permalinks.category'),
				'with_front' => true,
				'feeds' => false,
				'pages' => true,
				'ep_mask' => EP_ALL,
			),
		);
	}
}
