<?php

namespace Jigoshop\Core;

/**
 * Registers required post types.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class PostTypes
{
	public static function initialize()
	{
		self::_registerProduct();
		self::_registerProductCategory();
		self::_registerProductTag();
	}

	/** Creates "product" post type in WordPress. */
	private static function _registerProduct()
	{
		register_post_type(
			'product',
			array(
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
			)
		);
	}

	/** Creates "product_category" taxonomy in WordPress. */
	private static function _registerProductCategory()
	{
		register_taxonomy('product_category',
			array('product'),
			array(
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
					'new_item_name' => __('New Product Category Name', 'jigoshop')
				),
				'capabilities' => array(
					'manage_terms' => 'manage_product_terms',
					'edit_terms' => 'edit_product_terms',
					'delete_terms' => 'delete_product_terms',
					'assign_terms' => 'assign_product_terms',
				),
				'hierarchical' => true,
//				'update_count_callback' => '_update_post_term_count', // TODO: Analyze if `update_count_callback` is needed for product_category
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array(
					'slug' => 'category',
					'with_front' => true,
					'feeds' => true,
					'pages' => true,
				),
			)
		);
	}

	/** Creates "product_tag" taxonomy in WordPress. */
	private static function _registerProductTag()
	{
		register_taxonomy('product_tag',
			array('product'),
			array(
				'labels' => array(
					'menu_name' => __('Tags', 'jigoshop'),
					'name' => __('Product Tags', 'jigoshop'),
					'singular_name' => __('Product Tag', 'jigoshop'),
					'search_items' => __('Search Product Tags', 'jigoshop'),
					'all_items' => __('All Product Tags', 'jigoshop'),
					'parent_item' => __('Parent Product Tag', 'jigoshop'),
					'parent_item_colon' => __('Parent Product Tag:', 'jigoshop'),
					'edit_item' => __('Edit Product Tag', 'jigoshop'),
					'update_item' => __('Update Product Tag', 'jigoshop'),
					'add_new_item' => __('Add New Product Tag', 'jigoshop'),
					'new_item_name' => __('New Product Tag Name', 'jigoshop')
				),
				'capabilities' => array(
					'manage_terms' => 'manage_product_terms',
					'edit_terms' => 'edit_product_terms',
					'delete_terms' => 'delete_product_terms',
					'assign_terms' => 'assign_product_terms',
				),
				'hierarchical' => false,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array(
					'slug' => 'tag',
					'with_front' => false
				),
			)
		);
	}
}