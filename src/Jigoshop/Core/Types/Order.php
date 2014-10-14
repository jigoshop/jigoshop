<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Order as OrderHelper;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Styles;
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

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, TaxServiceInterface $taxService, Styles $styles)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->taxService = $taxService;

		$wp->addFilter('post_row_actions', array($this, 'displayTitle'));
		$wp->addFilter('post_updated_messages', array($this, 'updateMessages'));
		$wp->addFilter('request', array($this, 'request'));
		$wp->addFilter(sprintf('bulk_actions-edit-%s', self::NAME), array($this, 'bulkActions'));
		$wp->addFilter(sprintf('views_edit-%s', self::NAME), array($this, 'statusFilters'));
		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::ORDER), array($this, 'columns'));
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::ORDER), array($this, 'displayColumn'), 2);
		$wp->addAction('init', array($this, 'registerOrderStatuses'));
		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles){
			if ($wp->getPostType() == Order::NAME) {
				$styles->add('jigoshop.admin.orders', JIGOSHOP_URL.'/assets/css/admin/orders.css');
			}
		});

		$that = $this;
		$wp->addAction('add_meta_boxes_'.self::NAME, function() use ($wp, $that){
			$wp->addMetaBox('jigoshop-order-data', __('Order Data', 'jigoshop'), array($that, 'dataBox'), Order::NAME, 'normal', 'high');
			$wp->addMetaBox('jigoshop-order-items', __('Order Items', 'jigoshop'), array($that, 'itemsBox'), Order::NAME, 'normal', 'high');
			$wp->addMetaBox('jigoshop-order-totals', __('Order Totals', 'jigoshop'), array($that, 'totalsBox'), Order::NAME, 'normal', 'default');
//			add_meta_box('jigoshop-order-attributes', __('Order Variation Attributes / Addons', 'jigoshop'), array($that, 'itemsBox'), Order::NAME, 'side', 'default');

			$wp->addMetaBox('jigoshop-order-actions', __('Order Actions', 'jigoshop'), array($that, 'actionsBox'), Order::NAME, 'side', 'default');
			$wp->removeMetaBox('commentstatusdiv', null, 'normal');
		});
	}

	public function dataBox()
	{
		//
	}

	public function itemsBox()
	{
		//
	}

	public function totalsBox()
	{
		//
	}

	public function actionsBox()
	{
		//
	}

	public function request($vars)
	{
		if ($this->wp->getPostType() === self::NAME) {
			if (!isset($vars['post_status'])) {
				$vars['post_status'] = array_keys(Status::getStatuses());
			}
		}

		return $vars;
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
			'title' => _x('Order', 'order', 'jigoshop'),
			'customer' => _x('Customer', 'order', 'jigoshop'),
			'billing_address' => _x('Billing address', 'order', 'jigoshop'),
			'shipping_address' => _x('Shipping address', 'order', 'jigoshop'),
			'shipping_payment' => _x('Shipping &amp; Payment', 'order', 'jigoshop'),
			'total' => _x('Total', 'order', 'jigoshop'),
		);

		return $columns;
	}

	public function registerOrderStatuses()
	{
		$statuses = Status::getStatuses();
		foreach ($statuses as $status => $label) {
			register_post_status($status, array(
				'label' => _x('On hold', 'order-status', 'jigoshop'),
				'public' => false,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop($label.' <span class="count">(%s)</span>', $label.' <span class="count">(%s)</span>', 'jigoshop'),
			));
		}
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
			case 'shipping_payment':
				// TODO: Add proper displaying
				echo 'test sh pay';
				break;
			case 'total':
				// TODO: Add proper displaying
				echo Product::formatPrice(0.0);
				break;
		}
	}

	public function displayTitle($actions){
		$post = $this->wp->getGlobalPost();
		// TODO: Remember to save order title as "Order %d"

		// Remove "Quick edit" as we won't use it.
		unset($actions['inline hide-if-no-js']);

		if ($post->post_type == self::NAME) {
			$fullFormat = _x('Y/m/d g:i:s A', 'time', 'jigoshop');
			$format = _x('Y/m/d', 'time', 'jigoshop');
			// TODO: Exclude mysql2date to WPAL
			echo '<time title="'.mysql2date($fullFormat, $post->post_date).'">'.apply_filters('post_date_column_time', mysql2date($format, $post->post_date), $post ).'</time>';
		}

		return $actions;
	}

	public function bulkActions($actions)
	{
		unset($actions['edit']);
		return $actions;
	}

	function statusFilters($views)
	{
		$current = (isset($_GET['post_status']) && Status::exists($_GET['post_status'])) ? $_GET['post_status'] : '';
		$statuses = Status::getStatuses();
		$counts = $this->wp->wpCountPosts(self::NAME, 'readable');

		$dates = isset($_GET['m']) ? '&amp;m='.$_GET['m'] : '';
		foreach ($statuses as $status => $label) {
			$count = isset($counts->$status) ? $counts->$status : 0;
			$views[$status] = '<a class="'.$status.($current == $status ? ' current' : '').'" href="?post_type='.self::NAME.'&amp;post_status='.$status.$dates.'">'.$label.' <span class="count">('.$count.')</a>';
		}

		if (!empty($current)) {
			$views['all'] = str_replace('current', '', $views['all']);
		}

		unset($views['publish']);

		if (isset($views['trash'])) {
			$trash = $views['trash'];
			unset($views['draft']);
			unset($views['trash']);
			$views['trash'] = $trash;
		}

		return $views;
	}

	public function updateMessages($messages)
	{
		if ($this->wp->getPostType() === 'shop_order') {
			$messages['post'][1] = __('Order updated.', 'jigoshop');
			$messages['post'][4] = __('Order updated.', 'jigoshop');
			$messages['post'][6] = __('Order published.', 'jigoshop');

			$messages['post'][8] = __('Order submitted.', 'jigoshop');
			$messages['post'][10] = __('Order draft updated.', 'jigoshop');
		}

		return $messages;
	}
}
