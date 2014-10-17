<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Order as OrderHelper;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
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
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var ShippingServiceInterface */
	private $shippingService;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, TaxServiceInterface $taxService, CustomerServiceInterface $customerService,
		ShippingServiceInterface $shippingService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->taxService = $taxService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;

		$wp->addFilter('post_row_actions', array($this, 'displayTitle'));
		$wp->addFilter('post_updated_messages', array($this, 'updateMessages'));
		$wp->addFilter('request', array($this, 'request'));
		$wp->addFilter(sprintf('bulk_actions-edit-%s', self::NAME), array($this, 'bulkActions'));
		$wp->addFilter(sprintf('views_edit-%s', self::NAME), array($this, 'statusFilters'));
		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::ORDER), array($this, 'columns'));
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::ORDER), array($this, 'displayColumn'), 2);
		$wp->addAction('init', array($this, 'registerOrderStatuses'));
		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles, $scripts){
			if ($wp->getPostType() == Order::NAME) {
				$styles->add('jigoshop.admin.order', JIGOSHOP_URL.'/assets/css/admin/order.css');
				$styles->add('jigoshop.admin.orders', JIGOSHOP_URL.'/assets/css/admin/orders.css');
				$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
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

	// TODO: Think on better place to keep order displaying functions
	public function dataBox()
	{
		$post = $this->wp->getGlobalPost();
		$order = $this->orderService->findForPost($post);
		$billingFields = $this->wp->applyFilters('jigoshop\admin\order\billing_fields', array(
			'company' => __('Company', 'jigoshop'),
			'euvatno' => __('EU VAT Number', 'jigoshop'),
			'first_name' => __('First Name', 'jigoshop'),
			'last_name' => __('Last Name', 'jigoshop'),
			'address' => __('Address', 'jigoshop'),
			'city' => __('City', 'jigoshop'),
			'postcode' => __('Postcode', 'jigoshop'),
			'country' => __('Country', 'jigoshop'),
			'state' => __('State/Province', 'jigoshop'),
			'phone' => __('Phone', 'jigoshop'),
			'email' => __('Email Address', 'jigoshop'),
		), $order);
		$shippingFields = $this->wp->applyFilters('jigoshop\admin\order\shipping_fields', array(
			'company' => __('Company', 'jigoshop'),
			'first_name' => __('First Name', 'jigoshop'),
			'last_name' => __('Last Name', 'jigoshop'),
			'address' => __('Address', 'jigoshop'),
			'city' => __('City', 'jigoshop'),
			'postcode' => __('Postcode', 'jigoshop'),
			'country' => __('Country', 'jigoshop'),
			'state' => __('State/Province', 'jigoshop'),
			'phone' => __('Phone', 'jigoshop'),
		), $order);
		$customers = $this->customerService->findAll();

		Render::output('admin/orders/dataBox', array(
			'order' => $order,
			'billingFields' => $billingFields,
			'shippingFields' => $shippingFields,
			'customers' => $customers
		));
	}

	public function itemsBox()
	{
		$post = $this->wp->getGlobalPost();
		$order = $this->orderService->findForPost($post);

		Render::output('admin/orders/itemsBox', array(
			'order' => $order,
		));
	}

	public function totalsBox()
	{
		$post = $this->wp->getGlobalPost();
		$order = $this->orderService->findForPost($post);

		Render::output('admin/orders/totalsBox', array(
			'order' => $order,
			'shippingMethods' => array(),//$this->shippingService->getAvailable(),
		));
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
				echo OrderHelper::getUserLink($order->getCustomer());
				break;
			case 'billing_address':
				Render::output('admin/orders/billing_address', array(
					'order' => $order,
				));
				break;
			case 'shipping_address':
				Render::output('admin/orders/shipping_address', array(
					'order' => $order,
				));
				break;
			case 'shipping_payment':
				Render::output('admin/orders/shipping_payment', array(
					'order' => $order,
				));
				break;
			case 'total':
				// TODO: Add proper displaying
				echo Product::formatPrice(0.0);
				break;
		}
	}

	public function displayTitle($actions){
		$post = $this->wp->getGlobalPost();

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
			$messages['post'][6] = __('Order updated.', 'jigoshop');

			$messages['post'][8] = __('Order submitted.', 'jigoshop');
			$messages['post'][10] = __('Order draft updated.', 'jigoshop');
		}

		return $messages;
	}
}
