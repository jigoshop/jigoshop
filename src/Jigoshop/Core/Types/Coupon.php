<?php

namespace Jigoshop\Core\Types;

use WPAL\Wordpress;

class Coupon implements Post
{
	const NAME = 'shop_coupon';

	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$wp->doAction('jigoshop\coupon\type\init', $wp);
	}

	public function getName()
	{
		return self::NAME;
	}

	public function getDefinition()
	{
		return array(
			'labels' => array(
				'menu_name' => __('Coupons', 'jigoshop'),
				'name' => __('Coupons', 'jigoshop'),
				'singular_name' => __('Coupon', 'jigoshop'),
				'add_new' => __('Add Coupon', 'jigoshop'),
				'add_new_item' => __('Add New Coupon', 'jigoshop'),
				'edit' => __('Edit', 'jigoshop'),
				'edit_item' => __('Edit Coupon', 'jigoshop'),
				'new_item' => __('New Coupon', 'jigoshop'),
				'view' => __('View Coupons', 'jigoshop'),
				'view_item' => __('View Coupon', 'jigoshop'),
				'search_items' => __('Search Coupons', 'jigoshop'),
				'not_found' => __('No Coupons found', 'jigoshop'),
				'not_found_in_trash' => __('No Coupons found in trash', 'jigoshop'),
				'parent' => __('Parent Coupon', 'jigoshop')
			),
			'description' => __('This is where you can add new coupons that customers can use in your store.', 'jigoshop'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => self::NAME,
			'map_meta_cap' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true,
			'supports' => array('title', 'editor'),
			'show_in_nav_menus' => false,
			'show_in_menu' => 'jigoshop'
		);
	}
}
