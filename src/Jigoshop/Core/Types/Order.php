<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Status;
use WPAL\Wordpress;

class Order implements Post
{
	const NAME = 'shop_order';

	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;

		$wp->addAction('init', array($this, 'registerOrderStatuses'));
		$wp->addFilter('post_updated_messages', array($this, 'updateMessages'));
		// Enable comments for all orders, disable pings
		$wp->addFilter('wp_insert_post_data', function ($data){
			if ($data['post_type'] == Order::NAME) {
				$data['comment_status'] = 'open';
				$data['ping_status'] = 'closed';
			}

			return $data;
		});
	}

	public function getName()
	{
		return self::NAME;
	}

	public function getDefinition()
	{
		return array(
			'labels' => array(
				'name' => __('Orders', 'jigoshop'),
				'singular_name' => __('Order', 'jigoshop'),
				'all_items' => __('All orders', 'jigoshop'),
				'add_new' => __('Add new', 'jigoshop'),
				'add_new_item' => __('New order', 'jigoshop'),
				'edit' => __('Edit', 'jigoshop'),
				'edit_item' => __('Edit order', 'jigoshop'),
				'new_item' => __('New order', 'jigoshop'),
				'view' => __('View order', 'jigoshop'),
				'view_item' => __('View order', 'jigoshop'),
				'search_items' => __('Search', 'jigoshop'),
				'not_found' => __('No orders found', 'jigoshop'),
				'not_found_in_trash' => __('No orders found in trash', 'jigoshop'),
				'parent' => __('Parent orders', 'jigoshop')
			),
			'description' => __('This is where store orders are stored.', 'jigoshop'),
			'public' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'capability_type' => self::NAME,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => false,
			'supports' => array('title', 'comments'),
			'has_archive' => false,
			'menu_position' => 58,
			'menu_icon' => 'dashicons-clipboard',
		);
	}

	public function registerOrderStatuses()
	{
		$statuses = Status::getStatuses();
		foreach ($statuses as $status => $label) {
			$this->wp->registerPostStatus($status, array(
				'label' => $label,
				'public' => false,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop($label.' <span class="count">(%s)</span>', $label.' <span class="count">(%s)</span>', 'jigoshop'),
			));
		}
	}

	public function updateMessages($messages)
	{
		if ($this->wp->getPostType() === self::NAME) {
			$messages['post'][1] = __('Order updated.', 'jigoshop');
			$messages['post'][4] = __('Order updated.', 'jigoshop');
			$messages['post'][6] = __('Order updated.', 'jigoshop');

			$messages['post'][8] = __('Order submitted.', 'jigoshop');
			$messages['post'][10] = __('Order draft updated.', 'jigoshop');
		}

		return $messages;
	}
}
