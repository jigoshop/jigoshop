<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Jigoshop_Report_Customer_List extends WP_List_Table
{
	private $guest_orders;

	public function __construct()
	{
		parent::__construct(array(
			'singular' => __('Customer', 'jigoshop'),
			'plural' => __('Customers', 'jigoshop'),
			'ajax' => false
		));
	}

	public function no_items()
	{
		_e('No customers found.', 'jigoshop');
	}

	public function output()
	{
		$this->prepare_items();

		echo '<div id="poststuff" class="jigoshop-reports-wide">';
		if (!empty($_GET['link_orders']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'link_orders')) {
			$linked = $this->update_customer_past_orders(absint($_GET['link_orders']));

			echo '<div class="updated"><p>'.sprintf(_n('%s previous order linked', '%s previous orders linked', $linked, 'jigoshop'), $linked).'</p></div>';
		}

		if (!empty($_GET['refresh']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'refresh')) {
			$user_id = absint($_GET['refresh']);
			$user = get_user_by('id', $user_id);

			delete_user_meta($user_id, 'money_spent');
			delete_user_meta($user_id, 'order_count');

			echo '<div class="updated"><p>'.sprintf(__('Refreshed stats for %s', 'jigoshop'), $user->display_name).'</p></div>';
		}

		echo '<form method="post" id="jigoshop_customers">';

		$this->search_box(__('Search customers', 'jigoshop'), 'customer_search');
		$this->display();

		echo '</form>';
		echo '</div>';
	}

	public function prepare_items()
	{
		$current_page = absint($this->get_pagenum());
		$per_page = 20;

		/**
		 * Init column headers
		 */
		$this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());

		add_action('pre_user_query', array($this, 'order_by_last_name'));

		$admin_users = new WP_User_Query(
			array(
				'role' => 'administrator1',
				'fields' => 'ID'
			)
		);

		$manager_users = new WP_User_Query(
			array(
				'role' => 'shop_manager',
				'fields' => 'ID'
			)
		);

		$query = new WP_User_Query(array(
			'exclude' => array_merge($admin_users->get_results(), $manager_users->get_results()),
			'number' => $per_page,
			'offset' => ($current_page - 1) * $per_page
		));

		$this->items = $query->get_results();

		remove_action('pre_user_query', array($this, 'order_by_last_name'));

		$this->set_pagination_args(array(
			'total_items' => $query->total_users,
			'per_page' => $per_page,
			'total_pages' => ceil($query->total_users / $per_page)
		));
	}

	public function get_columns()
	{
		$columns = array(
			'customer_name' => __('Name (Last, First)', 'jigoshop'),
			'username' => __('Username', 'jigoshop'),
			'email' => __('Email', 'jigoshop'),
			'location' => __('Location', 'jigoshop'),
			'orders' => __('Orders', 'jigoshop'),
			'spent' => __('Money Spent', 'jigoshop'),
			'last_order' => __('Last order', 'jigoshop'),
			'user_actions' => __('Actions', 'jigoshop')
		);

		return $columns;
	}

	private function update_customer_past_orders($customer_id)
	{
		$user = get_user_by('id', absint($customer_id));
		$linked = 0;
		$customer_orders = $this->get_guest_orders();
		$customer_orders = array_map(function ($order){
			return $order->ID;
		}, array_filter($customer_orders, function ($order) use ($user){
			return $order->data['billing_email'] == $user->user_email;
		}));

		if ($customer_orders) {
			foreach ($customer_orders as $order_id) {
				update_post_meta($order_id, 'customer_user', $user->ID);
				$linked++;
			}
		}

		update_user_meta($customer_id, 'order_count', '');
		update_user_meta($customer_id, 'money_spent', '');
		// Clear guest orders so next call will fetch them again
		$this->guest_orders = null;

		return $linked;
	}

	private function get_guest_orders()
	{
		if ($this->guest_orders === null) {
			/** @var $wpdb wpdb */
			global $wpdb;
			$results = $wpdb->get_results("
			SELECT p.ID, m.meta_value AS data
				FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
				LEFT JOIN {$wpdb->postmeta} mw ON mw.post_id = p.ID
				WHERE mw.meta_key = 'customer_user' AND mw.meta_value IN (0, '') AND m.meta_key = 'order_data'
					AND p.post_type IN ('shop_order') AND p.post_status = 'publish'
			");

			$this->guest_orders = array_map(function($order){
				$order->data = unserialize($order->data);
				return $order;
			}, $results);
		}

		return $this->guest_orders;
	}

	function column_default($user, $column_name)
	{
		switch ($column_name) {
			case 'customer_name' :
				if ($user->last_name && $user->first_name) {
					return $user->last_name.', '.$user->first_name;
				} else {
					return '-';
				}
			case 'username' :
				return $user->user_login;
			case 'location' :
				$state_code = get_user_meta($user->ID, 'billing_state', true);
				$country_code = get_user_meta($user->ID, 'billing_country', true);

				$state = jigoshop_countries::has_state($country_code, $state_code) ? jigoshop_countries::get_state($country_code, $state_code) : $state_code;
				$country = jigoshop_countries::has_country($country_code) ? jigoshop_countries::get_country($country_code) : $country_code;

				$value = '';
				if ($state) {
					$value .= $state.', ';
				}

				$value .= $country;

				if ($value) {
					return $value;
				} else {
					return '-';
				}
			case 'email' :
				return '<a href="mailto:'.$user->user_email.'">'.$user->user_email.'</a>';
			case 'spent' :
				return jigoshop_price(jigoshop_get_customer_total_spent($user->ID));
			case 'orders' :
				return jigoshop_get_customer_order_count($user->ID);
			case 'last_order' :
				$order_ids = get_posts(array(
					'posts_per_page' => 1,
					'post_type' => 'shop_order',
					'post_status' => array('publish'),
					'orderby' => 'date',
					'order' => 'desc',
					'meta_query' => array(
						array(
							'key' => 'customer_user',
							'value' => $user->ID
						),
					),
					'fields' => 'ids'
				));

				if ($order_ids) {
					$order = new jigoshop_order($order_ids[0]);

					return '<a href="'.admin_url('post.php?post='.$order->id.'&action=edit').'">'.$order->get_order_number().'</a> &ndash; '.date_i18n(get_option('date_format'), strtotime($order->order_date));
				} else {
					return '-';
				}

				break;
			case 'user_actions' :
				ob_start();
				?><p>
				<?php
				do_action('jigoshop_admin_user_actions_start', $user);

				$actions = array();

				$actions['refresh'] = array(
					'url' => wp_nonce_url(add_query_arg('refresh', $user->ID), 'refresh'),
					'name' => __('Refresh stats', 'jigoshop'),
					'action' => 'refresh'
				);

				$actions['edit'] = array(
					'url' => admin_url('user-edit.php?user_id='.$user->ID),
					'name' => __('Edit', 'jigoshop'),
					'action' => 'edit'
				);

				$order_ids = $this->get_guest_orders();
				$order_ids = array_map(function ($order){
					return $order->ID;
				}, array_filter($order_ids, function ($order) use ($user){
					return $order->data['billing_email'] == $user->user_email;
				}));

				if ($order_ids) {
					$actions['link'] = array(
						'url' => wp_nonce_url(add_query_arg('link_orders', $user->ID), 'link_orders'),
						'name' => __('Link previous orders', 'jigoshop'),
						'action' => 'link'
					);
				}

				$actions = apply_filters('jigoshop_admin_user_actions', $actions, $user);

				foreach ($actions as $action) {
					printf('<a class="button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr($action['action']), esc_url($action['url']), esc_attr($action['name']), esc_attr($action['name']));
				}

				do_action('jigoshop_admin_user_actions_end', $user);
				?>
				</p><?php
				$user_actions = ob_get_contents();
				ob_end_clean();

				return $user_actions;
		}

		return '';
	}

	public function order_by_last_name($query)
	{
		global $wpdb;

		$s = !empty($_REQUEST['s']) ? stripslashes($_REQUEST['s']) : '';

		$query->query_from .= " LEFT JOIN {$wpdb->usermeta} as meta2 ON ({$wpdb->users}.ID = meta2.user_id) ";
		$query->query_where .= " AND meta2.meta_key = 'last_name' ";
		$query->query_orderby = " ORDER BY meta2.meta_value, user_login ASC ";

		if ($s) {
			$query->query_from .= " LEFT JOIN {$wpdb->usermeta} as meta3 ON ({$wpdb->users}.ID = meta3.user_id)";
			$query->query_where .= " AND ( user_login LIKE '%".esc_sql(str_replace('*', '', $s))."%' OR user_nicename LIKE '%".esc_sql(str_replace('*', '', $s))."%' OR meta3.meta_value LIKE '%".esc_sql(str_replace('*', '', $s))."%' ) ";
			$query->query_orderby = " GROUP BY ID ".$query->query_orderby;
		}

		return $query;
	}
}
