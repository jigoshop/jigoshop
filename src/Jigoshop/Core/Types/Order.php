<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Helper\Order as OrderHelper;
use Jigoshop\Helper\Product;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
use WPAL\Wordpress;

class Order implements Post
{
	const NAME = 'shop_order';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Service\OrderServiceInterface */
	private $orderService;
	/** @var TaxServiceInterface */
	private $taxService;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, TaxServiceInterface $taxService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->taxService = $taxService;

		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::ORDER), array($this, 'columns'));
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::ORDER), array($this, 'displayColumn'), 2);
		// TODO: Introduce proper category filter
//		$wp->addAction('restrict_manage_posts', array($this, 'categoryFilter'));
//		$wp->addAction('restrict_manage_posts', array($this, 'typeFilter'));
//		$that = $this;
//		$wp->addAction('add_meta_boxes_'.self::NAME, function() use ($wp, $that){
//			$wp->addMetaBox('jigoshop-product-data', __('Product Data', 'jigoshop'), array($that, 'box'), $that::NAME, 'normal', 'high');
//			$wp->removeMetaBox('commentstatusdiv', null, 'normal');
//		});
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
			'capability_type' => 'shop_order',
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true,
			'supports' => array('title', 'comments'),
			'has_archive' => false,
			'menu_position' => 58,
			'menu_icon' => 'dashicons-clipboard',
		);
	}

	public function columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'status' => _x('Status', 'order', 'jigoshop'),
			'order_title' => _x('Order', 'order', 'jigoshop'),
			'customer' => _x('Customer', 'order', 'jigoshop'),
			'billing_address' => _x('Billing address', 'order', 'jigoshop'),
			'shipping_address' => _x('Shipping address', 'order', 'jigoshop'),
			'total' => _x('Total', 'order', 'jigoshop'),
			'creation' => _x('Created at', 'product', 'jigoshop'),
		);

		return $columns;
	}

	public function displayColumn($column)
	{
		$post = $this->wp->getGlobalPost();
		if($post === null){
			return;
		}

		$order = $this->orderService->findForPost($post);
		switch ($column) {
			case 'status':
				echo OrderHelper::getStatus($order);
				break;
			case 'order_title':
				$fullFormat = _x('Y/m/d g:i:s A', 'time', 'jigoshop');
				$format = _x('Y/m/d', 'time', 'jigoshop');
				echo '<a href="'.admin_url('post.php?post='.$order->getId().'&action=edit').'">'.sprintf(__('Order %s', 'jigoshop'), $order->getNumber()).'</a>';
				echo '<time title="'.mysql2date($fullFormat, $post->post_date).'">'.apply_filters('post_date_column_time', mysql2date($format, $post->post_date), $post ).'</time>';
				break;
			case 'customer':
				// TODO: Add proper displaying
				echo 'test2';
				break;
			case 'billing_address':
				// TODO: Add proper displaying
				echo 'test addr';
				break;
			case 'shipping_address':
				// TODO: Add proper displaying
				echo 'test sh addr';
				break;
			case 'total':
				// TODO: Add proper displaying
				echo Product::formatPrice(0.0);
				break;
		}
	}
}
