<?php
if (!defined('ABSPATH')) {
	exit;
}

class Jigoshop_Admin_Status
{
	public static function output()
	{
		$template = jigoshop_locate_template('admin/status/main');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	public static function status_report()
	{
		$template = jigoshop_locate_template('admin/status/report');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	public static function status_tools()
	{
		global $wpdb;

		$tools = self::get_tools();

		if (!empty($_GET['action']) && !empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'debug_action')) {
			switch ($_GET['action']) {
				case 'clear_transients':
					delete_transient('jigoshop_addons_data');
					delete_transient('jigoshop_report_coupon_usage');
					delete_transient('jigoshop_report_customer_list');
					delete_transient('jigoshop_report_customers');
					delete_transient('jigoshop_report_low_in_stock');
					delete_transient('jigoshop_report_most_stocked');
					delete_transient('jigoshop_report_out_of_stock');
					delete_transient('jigoshop_report_sales_by_category');
					delete_transient('jigoshop_report_sales_by_date');
					delete_transient('jigoshop_report_sales_by_product');
					delete_transient('jigoshop_widget_cache');

					$query = new WP_User_Query(array('fields' => 'ids'));
					$users = $query->get_results();
					foreach ($users as $user) {
						delete_transient('jigo_usercart_'.$user);
					}

					echo '<div class="updated"><p>'.__('Jigoshop transients cleared', 'jigoshop').'</p></div>';
					break;
				case 'clear_expired_transients':
					// http://w-shadow.com/blog/2012/04/17/delete-stale-transients/
					$rows = $wpdb->query("
						DELETE
							a, b
						FROM
							{$wpdb->options} a, {$wpdb->options} b
						WHERE
							a.option_name LIKE '_transient_%' AND
							a.option_name NOT LIKE '_transient_timeout_%' AND
							b.option_name = CONCAT(
								'_transient_timeout_',
								SUBSTRING(
									a.option_name,
									CHAR_LENGTH('_transient_') + 1
								)
							)
							AND b.option_value < UNIX_TIMESTAMP()
					");

					$rows2 = $wpdb->query("
						DELETE
							a, b
						FROM
							{$wpdb->options} a, {$wpdb->options} b
						WHERE
							a.option_name LIKE '_site_transient_%' AND
							a.option_name NOT LIKE '_site_transient_timeout_%' AND
							b.option_name = CONCAT(
								'_site_transient_timeout_',
								SUBSTRING(
									a.option_name,
									CHAR_LENGTH('_site_transient_') + 1
								)
							)
							AND b.option_value < UNIX_TIMESTAMP()
					");

					echo '<div class="updated"><p>'.sprintf(__('%d transients rows cleared', 'jigoshop'), $rows + $rows2).'</p></div>';
					break;
				case 'reset_roles':
					// Remove then re-add caps and roles
					/** @var $wp_roles WP_Roles */
					global $wp_roles;
					$capabilities = jigoshop_get_core_capabilities();
					foreach ($capabilities as $cap_group) {
						foreach ($cap_group as $cap) {
							$wp_roles->remove_cap('administrator', $cap);
							$wp_roles->remove_cap('shop_manager', $cap);
						}
					}
					remove_role('customer');
					remove_role('shop_manager');

					// Add roles back
					jigoshop_roles_init();

					echo '<div class="updated"><p>'.__('Roles successfully reset', 'jigoshop').'</p></div>';
					break;
				case 'recount_terms':
					$product_cats = get_terms('product_cat', array(
						'hide_empty' => false,
						'fields' => 'id=>parent'
					));

					_update_post_term_count($product_cats, get_taxonomy('product_cat'));

					$product_tags = get_terms('product_tag', array(
						'hide_empty' => false,
						'fields' => 'id=>parent'
					));

					_update_post_term_count($product_tags, get_taxonomy('product_tag'));

					echo '<div class="updated"><p>'.__('Terms successfully recounted', 'jigoshop').'</p></div>';
					break;
				case 'delete_taxes':
					$options = Jigoshop_Base::get_options();
					$options->set('jigoshop_tax_rates', '');
					$options->update_options();

					echo '<div class="updated"><p>'.__('Tax rates successfully deleted', 'jigoshop').'</p></div>';
					break;
				default:
					$action = esc_attr($_GET['action']);
					if (isset($tools[$action]['callback'])) {
						$callback = $tools[$action]['callback'];
						$return = call_user_func($callback);
						if ($return === false) {
							if (is_array($callback)) {
								echo '<div class="error"><p>'.sprintf(__('There was an error calling %s::%s', 'jigoshop'), get_class($callback[0]), $callback[1]).'</p></div>';

							} else {
								echo '<div class="error"><p>'.sprintf(__('There was an error calling %s', 'jigoshop'), $callback).'</p></div>';
							}
						}
					}
					break;
			}
		}

		// Display message if settings settings have been saved
		if (isset($_REQUEST['settings-updated'])) {
			echo '<div class="updated"><p>'.__('Your changes have been saved.', 'jigoshop').'</p></div>';
		}

		$template = jigoshop_locate_template('admin/status/tools');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	public static function get_tools()
	{
		$tools = array(
			'clear_transients' => array(
				'name' => __('Jigoshop Transients', 'jigoshop'),
				'button' => __('Clear transients', 'jigoshop'),
				'desc' => __('This tool will clear the product/shop transients cache.', 'jigoshop'),
			),
			'clear_expired_transients' => array(
				'name' => __('Expired Transients', 'jigoshop'),
				'button' => __('Clear expired transients', 'jigoshop'),
				'desc' => __('This tool will clear ALL expired transients from WordPress.', 'jigoshop'),
			),
			'recount_terms' => array(
				'name' => __('Term counts', 'jigoshop'),
				'button' => __('Recount terms', 'jigoshop'),
				'desc' => __('This tool will recount product terms - useful when changing your settings in a way which hides products from the catalog.', 'jigoshop'),
			),
			'reset_roles' => array(
				'name' => __('Capabilities', 'jigoshop'),
				'button' => __('Reset capabilities', 'jigoshop'),
				'desc' => __('This tool will reset the admin, customer and shop_manager roles to default. Use this if your users cannot access all of the jigoshop admin pages.', 'jigoshop'),
			),
			'delete_taxes' => array(
				'name' => __('Delete all jigoshop tax rates', 'jigoshop'),
				'button' => __('Delete ALL tax rates', 'jigoshop'),
				'desc' => __('<strong class="red">Note:</strong> This option will delete ALL of your tax rates, use with caution.', 'jigoshop'),
			),
		);

		return apply_filters('jigoshop_debug_tools', $tools);
	}

	public static function status_logs()
	{
		$logs = self::scan_log_files();

		if (!empty($_REQUEST['log_file']) && isset($logs[sanitize_title($_REQUEST['log_file'])])) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			$viewed_log = $logs[sanitize_title($_REQUEST['log_file'])];
		} elseif ($logs) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			$viewed_log = current($logs);
		}

		$template = jigoshop_locate_template('admin/status/logs');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	/**
	 * Scan the log files
	 *
	 * @return array
	 */
	public static function scan_log_files()
	{
		$files = @scandir(JIGOSHOP_LOG_DIR);
		$result = array();

		if ($files) {

			foreach ($files as $key => $value) {

				if (!in_array($value, array('.', '..'))) {
					if (!is_dir($value) && strstr($value, '.log')) {
						$result[sanitize_title($value)] = $value;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Retrieve metadata from a file. Based on WP Core's get_file_data function
	 *
	 * @since  2.1.1
	 * @param  string $file Path to the file
	 * @return string
	 */
	public static function get_file_version($file)
	{
		// Avoid notices if file does not exist
		if (!file_exists($file)) {
			return '';
		}

		// We don't need to write to the file, so just open for reading.
		$fp = fopen($file, 'r');

		// Pull only the first 8kiB of the file in.
		$file_data = fread($fp, 8192);

		// PHP will close file handle, but we are good citizens.
		fclose($fp);

		// Make sure we catch CR-only line endings.
		$file_data = str_replace("\r", "\n", $file_data);
		$version = '';

		if (preg_match('/^[ \t\/*#@]*'.preg_quote('@version', '/').'(.*)$/mi', $file_data, $match) && $match[1]) {
			$version = _cleanup_header_comment($match[1]);
		}

		return $version;
	}

	/**
	 * Scan the template files
	 *
	 * @param  string $template_path
	 * @return array
	 */
	public static function scan_template_files($template_path)
	{
		$files = scandir($template_path);
		$result = array();

		if ($files) {
			foreach ($files as $key => $value) {
				if (!in_array($value, array(".", ".."))) {
					if (is_dir($template_path.DIRECTORY_SEPARATOR.$value)) {
						$sub_files = self::scan_template_files($template_path.DIRECTORY_SEPARATOR.$value);
						foreach ($sub_files as $sub_file) {
							$result[] = $value.DIRECTORY_SEPARATOR.$sub_file;
						}
					} else {
						$result[] = $value;
					}
				}
			}
		}

		return $result;
	}
}
