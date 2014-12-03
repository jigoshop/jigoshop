<?php

namespace Jigoshop\Core\Types;

use WPAL\Wordpress;

class Email implements Post
{
	const NAME = 'shop_email';

	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$wp->doAction('jigoshop\email\type\init', $wp);
	}

	public function getName()
	{
		return self::NAME;
	}

	public function getDefinition()
	{
		return array(
			'labels' => array(
				'menu_name' => __('Emails', 'jigoshop'),
				'name' => __('Emails', 'jigoshop'),
				'singular_name' => __('Emails', 'jigoshop'),
				'add_new' => __('Add Email', 'jigoshop'),
				'add_new_item' => __('Add New Email', 'jigoshop'),
				'edit' => __('Edit', 'jigoshop'),
				'edit_item' => __('Edit Email', 'jigoshop'),
				'new_item' => __('New Email', 'jigoshop'),
				'view' => __('View Email', 'jigoshop'),
				'view_item' => __('View Email', 'jigoshop'),
				'search_items' => __('Search Email', 'jigoshop'),
				'not_found' => __('No Emils found', 'jigoshop'),
				'not_found_in_trash' => __('No Emails found in trash', 'jigoshop'),
				'parent' => __('Parent Email', 'jigoshop')
			),
			'description' => __('This is where you can add new emails that customers can receive in your store.', 'jigoshop'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => Email::NAME,
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
