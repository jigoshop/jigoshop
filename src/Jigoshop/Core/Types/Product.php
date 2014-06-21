<?php


namespace Jigoshop\Core\Types;


class Product implements Post
{
	const NAME = 'product';

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
			'description' => \__('This is where you can add new products to your store.', 'jigoshop'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'product',
			'map_meta_cap' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'hierarchical' => false, // Hierarchical causes a memory leak http://core.trac.wordpress.org/ticket/15459
			'rewrite' => array(
				'slug' => 'product',
				'with_front' => true,
				'feeds' => true,
				'pages' => true,
			),
			'query_var' => true,
			'supports' => array('title', 'editor', 'thumbnail', 'comments', 'excerpt'),
			'has_archive' => false,
			'show_in_nav_menus' => false,
			'menu_position' => 56
		);
	}
}