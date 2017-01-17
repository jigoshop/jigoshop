<?php
/**
 *   ./////////////////////////////.
 *  //////////////////////////////////
 *  ///////////    ///////////////////
 *  ////////     /////////////////////
 *  //////    ////////////////////////
 *  /////    /////////    ////////////
 *  //////     /////////     /////////
 *  /////////     /////////    ///////
 *  ///////////    //////////    /////
 *  ////////////////////////    //////
 *  /////////////////////    /////////
 *  ///////////////////    ///////////
 *  //////////////////////////////////
 *   `//////////////////////////////`
 *
 * Plugin Name:         Jigoshop
 * Plugin URI:          https://www.jigoshop.com
 * Description:         Jigoshop, a WordPress eCommerce plugin that works.
 * Author:              Jigoshop Limited
 * Author URI:          https://www.jigoshop.com
 * Version:             1.18.3
 * Requires at least:   4.0
 * Tested up to:        4.7.1
 * Text Domain:         jigoshop
 * Domain Path:         /languages/
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2015 Jigoshop.
 * @license             GNU General Public License v3
 */

if ( !defined('ABSPATH') ){
	die("Not to be accessed directly");
}
if (!defined('JIGOSHOP_VERSION')) {
	define('JIGOSHOP_VERSION', '1.18.1');
}
if (!defined('JIGOSHOP_DB_VERSION')) {
	define('JIGOSHOP_DB_VERSION', 1503180);
}
if (!defined('JIGOSHOP_OPTIONS')) {
	define('JIGOSHOP_OPTIONS', 'jigoshop_options');
}
if (!defined('JIGOSHOP_TEMPLATE_URL')) {
	define('JIGOSHOP_TEMPLATE_URL', 'jigoshop/');
}
if (!defined('JIGOSHOP_DIR')) {
	define('JIGOSHOP_DIR', dirname(__FILE__));
}
if (!defined('JIGOSHOP_URL')) {
	define('JIGOSHOP_URL', plugins_url('', __FILE__));
}
if (!defined('JIGOSHOP_LOG_DIR')) {
	$upload_dir = wp_upload_dir();
	define('JIGOSHOP_LOG_DIR', $upload_dir['basedir'].'/jigoshop-logs/');
}

define('JIGOSHOP_REQUIRED_MEMORY', 64);
define('JIGOSHOP_REQUIRED_WP_MEMORY', 64);
define('JIGOSHOP_PHP_VERSION', '5.4');
define('JIGOSHOP_WORDPRESS_VERSION', '3.8');

if(!version_compare(PHP_VERSION, JIGOSHOP_PHP_VERSION, '>=')){
	function jigoshop_required_version(){
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Error!</strong> Jigoshop requires at least PHP %s! Your version is: %s. Please upgrade.', 'jigoshop'), JIGOSHOP_PHP_VERSION, PHP_VERSION).
		'</p></div>';
	}
	add_action('admin_notices', 'jigoshop_required_version');
	return;
}

include ABSPATH.WPINC.'/version.php';
/** @noinspection PhpUndefinedVariableInspection */
if(!version_compare($wp_version, JIGOSHOP_WORDPRESS_VERSION, '>=')){
	function jigoshop_required_wordpress_version()
	{
		include ABSPATH.WPINC.'/version.php';
		/** @noinspection PhpUndefinedVariableInspection */
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Error!</strong> Jigoshop requires at least WordPress %s! Your version is: %s. Please upgrade.', 'jigoshop'), JIGOSHOP_WORDPRESS_VERSION, $wp_version).
			'</p></div>';
	}
	add_action('admin_notices', 'jigoshop_required_wordpress_version');
	return;
}

$ini_memory_limit = trim(ini_get('memory_limit'));
preg_match('/^(\d+)(\w*)?$/', $ini_memory_limit, $memory);
$memory_limit = $memory[1];
if (isset($memory[2])) {
	switch ($memory[2]) {
		case 'M':
		case 'm':
			$memory_limit *= 1024;
		case 'K':
		case 'k':
			$memory_limit *= 1024;
	}
}
if($memory_limit < JIGOSHOP_REQUIRED_MEMORY*1024*1024){
	function jigoshop_required_memory_warning()
	{
		$ini_memory_limit = ini_get('memory_limit');
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Warning!</strong> Jigoshop requires at least %sM of memory! Your system currently has: %s.', 'jigoshop'), JIGOSHOP_REQUIRED_MEMORY, $ini_memory_limit).
			'</p></div>';
	}
	add_action('admin_notices', 'jigoshop_required_memory_warning');
}

preg_match('/^(\d+)(\w*)?$/', trim(WP_MEMORY_LIMIT), $memory);
$memory_limit = $memory[1];
if (isset($memory[2])) {
	switch ($memory[2]) {
		case 'M':
		case 'm':
			$memory_limit *= 1024;
		case 'K':
		case 'k':
			$memory_limit *= 1024;
	}
}

if($memory_limit < JIGOSHOP_REQUIRED_WP_MEMORY*1024*1024){
	function jigoshop_required_wp_memory_warning()
	{
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Warning!</strong> Jigoshop requires at least %sM of memory for WordPress! Your system currently has: %s. <a href="%s" target="_blank">How to change?</a>', 'jigoshop'),
				JIGOSHOP_REQUIRED_MEMORY, WP_MEMORY_LIMIT, 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP').
			'</p></div>';
	}
	add_action('admin_notices', 'jigoshop_required_wp_memory_warning');
}

if(get_option('migration_terms_accept', false) == false) {
	add_action('admin_notices', function(){
		$user = wp_get_current_user();
		echo '<div class="notice notice-info"><p>'.
            sprintf(
                __('Hi <b>%s</b>! Jigoshop 1.x will no longer be supported from the end of March 2017. We strongly recommend upgrading to Jigoshop eCommerce which is a completely brand new product. You can check your plugins\' compatibility with Jigoshop eCommerce <a href="%s">here</a>. The migration guide can be found <a href="%s">here</a>.<br /><b>Please note, that we strongly recommend to create a full-site backup before migrating.</b>', 'jigoshop'),
                $user->display_name,
                admin_url( 'admin.php?page=jigoshop_migration_information'),
	            'https://www.jigoshop.com/migration-guide/'
            ).
            '</p></div>';
	});
}

/**
 * Include core files and classes
 */
include_once('classes/abstract/jigoshop_base.class.php');
include_once('classes/abstract/jigoshop_singleton.class.php');
include_once('classes/jigoshop_options.class.php');
include_once('classes/jigoshop_session.class.php');

include_once('classes/jigoshop_sanitize.class.php');
include_once('classes/jigoshop_validation.class.php');
include_once('classes/jigoshop_forms.class.php');
include_once('jigoshop_taxonomy.php');

include_once('classes/jigoshop_countries.class.php');
include_once('classes/jigoshop_customer.class.php');
include_once('classes/jigoshop_product.class.php');
include_once('classes/jigoshop_product_variation.class.php');
include_once('classes/jigoshop_order.class.php');
include_once('classes/jigoshop_orders.class.php');
include_once('classes/jigoshop_tax.class.php');
include_once('classes/jigoshop_shipping.class.php');
include_once('classes/jigoshop_coupons.class.php');
include_once('classes/jigoshop_licence_validator.class.php');
include_once('classes/jigoshop_emails.class.php');

include_once('gateways/gateways.class.php');
include_once('gateways/gateway.class.php');
include_once('gateways/bank_transfer.php');
include_once('gateways/cheque.php');
include_once('gateways/cod.php');
include_once('gateways/paypal.php');
include_once('gateways/futurepay.php');
include_once('gateways/worldpay.php');
include_once('gateways/no_payment.php');

include_once('shipping/shipping_method.class.php');
include_once('shipping/jigoshop_calculable_shipping.php');
include_once('shipping/flat_rate.php');
include_once('shipping/free_shipping.php');
include_once('shipping/local_pickup.php');

include_once('classes/jigoshop_query.class.php');
include_once('classes/jigoshop_request_api.class.php');

include_once('classes/jigoshop.class.php');
include_once('classes/jigoshop_cart.class.php');
include_once('classes/jigoshop_checkout.class.php');
include_once('classes/jigoshop_cron.class.php');

include_once('shortcodes/init.php');
include_once('widgets/init.php');

include_once('jigoshop_functions.php');
include_once('jigoshop_templates.php');
include_once('jigoshop_template_actions.php');
include_once('jigoshop_emails.php');
include_once('jigoshop_actions.php');

// Plugins
include_once('plugins/jigoshop-cart-favicon-count/jigoshop-cart-favicon-count.php');

/**
 * IIS compat fix/fallback
 **/
if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
	if (isset($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
	}
}

// Load administration & check if we need to install
if (is_admin()) {
	include_once('admin/jigoshop-admin.php');
	register_activation_hook(__FILE__, 'install_jigoshop');
}


function jigoshop_admin_footer($text) {
	$screen = get_current_screen();

	if (strpos($screen->base, 'jigoshop') === false && strpos($screen->parent_base, 'jigoshop') === false && !in_array($screen->post_type, array('product', 'shop_order'))) {
		return $text;
	}

	return sprintf(
		'<a target="_blank" href="https://www.jigoshop.com/support/">%s</a> | %s',
		__('Contact support', 'jigoshop'),
		str_replace(
			array('[stars]','[link]','[/link]'),
			array(
				'<a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/jigoshop#postform" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
				'<a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/jigoshop#postform" >',
				'</a>'
			),
			__('Add your [stars] on [link]wordpress.org[/link] and keep this plugin essentially free.', 'jigoshop')
		)
	);
}
add_filter('admin_footer_text', 'jigoshop_admin_footer');

/**
 * Adds Jigoshop items to admin bar.
 */
function jigoshop_admin_toolbar() {
	/** @var WP_Admin_Bar $wp_admin_bar */
	global $wp_admin_bar;
	$manage_products = current_user_can('manage_jigoshop_products');
	$manage_orders = current_user_can('manage_jigoshop_orders');
	$manage_jigoshop = current_user_can('manage_jigoshop');
	$view_reports = current_user_can('view_jigoshop_reports');
	$manege_emails = current_user_can('manage_jigoshop_emails');

	if (!is_admin() && ($manage_jigoshop || $manage_products || $manage_orders || $view_reports)) {
		$wp_admin_bar->add_node(array(
			'id' => 'jigoshop',
			'title' => __('Jigoshop', 'jigoshop'),
			'href' => $manage_jigoshop ? admin_url('admin.php?page=jigoshop') : '',
			'parent' => false,
			'meta' => array(
				'class' => 'jigoshop-toolbar'
			),
		));

		if ($manage_jigoshop) {
			$wp_admin_bar->add_node(array(
				'id' => 'jigoshop_dashboard',
				'title' => __('Dashboard', 'jigoshop'),
				'parent' => 'jigoshop',
				'href' => admin_url('admin.php?page=jigoshop'),
			));
		}

		if ($manage_products) {
			$wp_admin_bar->add_node(array(
				'id' => 'jigoshop_products',
				'title' => __('Products', 'jigoshop'),
				'parent' => 'jigoshop',
				'href' => admin_url('edit.php?post_type=product'),
			));
		}

		if ($manage_orders) {
			$wp_admin_bar->add_node(array(
				'id' => 'jigoshop_orders',
				'title' => __('Orders', 'jigoshop'),
				'parent' => 'jigoshop',
				'href' => admin_url('edit.php?post_type=shop_order'),
			));
		}

		if ($manage_jigoshop) {
			$wp_admin_bar->add_node(array(
				'id' => 'jigoshop_settings',
				'title' => __('Settings', 'jigoshop'),
				'parent' => 'jigoshop',
				'href' => admin_url('admin.php?page=jigoshop_settings'),
			));
		}

		if($manege_emails) {
			$wp_admin_bar->add_node(array(
				'id' => 'jigoshop_emils',
				'title' => __('Emails', 'jigoshop'),
				'parent' => 'jigoshop',
				'href' => admin_url('edit.php?post_type=shop_email'),
			));
		}
	}
}

add_action('admin_bar_menu', 'jigoshop_admin_toolbar', 35);

function jigoshop_admin_bar_links($links)
{
	return array_merge(array(
		'<a href="'.admin_url('admin.php?page=jigoshop_settings').'">'.__('Settings', 'jigoshop').'</a>',
		'<a href="https://www.jigoshop.com/migration-guide/">'.__('Upgrade to Jigoshop eCommerce', 'jigoshop').'</a>',
	), $links);
}

function jigoshop_admin_bar_edit($location, $term_id, $taxonomy)
{
	if (in_array($taxonomy, array('product_cat', 'product_tag')) && strpos($location, 'post_type=product') === false) {
		$location .= '&post_type=product';
	}

	return $location;
}
add_filter('get_edit_term_link', 'jigoshop_admin_bar_edit', 10, 3);

/**
 * Jigoshop Init
 */
add_action('init', 'jigoshop_init', 0);
function jigoshop_init()
{
	// Override default translations with custom .mo's found in wp-content/languages/jigoshop first.
	load_textdomain('jigoshop', WP_LANG_DIR.'/jigoshop/jigoshop-'.get_locale().'.mo');
	load_plugin_textdomain('jigoshop', false, dirname(plugin_basename(__FILE__)).'/languages/');
	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'jigoshop_admin_bar_links');

	do_action('before_jigoshop_init');
	// instantiate options -after- loading text domains
	$options = Jigoshop_Base::get_options();

	jigoshop_post_type(); // register taxonomies
	new jigoshop_cron(); // -after- text domains and Options instantiation allows settings translations
	jigoshop_set_image_sizes(); // called -after- our Options are loaded

	// add Singletons here so that the taxonomies are loaded before calling them.
	jigoshop_session::instance(); // Start sessions if they aren't already
	jigoshop::instance(); // Utility functions, uses sessions
	jigoshop_customer::instance(); // Customer class, sorts session data such as location

	// Jigoshop will instantiate gateways and shipping methods on this same 'init' action hook
	// with a very low priority to ensure text domains are loaded first prior to installing any external options
	jigoshop_shipping::instance(); // Shipping class. loads shipping methods
	jigoshop_payment_gateways::instance(); // Payment gateways class. loads payment methods
	jigoshop_cart::instance(); // Cart class, uses sessions

	add_filter( 'mce_external_plugins', 'jigoshop_register_shortcode_editor' );
	add_filter( 'mce_buttons', 'jigoshop_register_shortcode_buttons' );

	if (!is_admin()) {
		/* Catalog Filters */
		add_filter('loop-shop-query', create_function('', 'return array("orderby" => "'.$options->get('jigoshop_catalog_sort_orderby').'","order" => "'.$options->get('jigoshop_catalog_sort_direction').'");'));
		add_filter('loop_shop_columns', create_function('', 'return '.$options->get('jigoshop_catalog_columns').';'));
		add_filter('loop_shop_per_page', create_function('', 'return '.$options->get('jigoshop_catalog_per_page').';'));

		jigoshop_catalog_query::instance(); // front end queries class
		jigoshop_request_api::instance(); // front end request api for URL's
	}

	jigoshop_roles_init();
	do_action('jigoshop_initialize_plugins');
}

/**
 * Include template functions here with a low priority so they are pluggable by themes
 */
add_action('init', 'jigoshop_load_template_functions', 999);
function jigoshop_load_template_functions()
{
	include_once('jigoshop_template_functions.php');
}


function jigoshop_get_core_capabilities()
{
	$capabilities = array();

	$capabilities['core'] = array(
		'manage_jigoshop',
		'view_jigoshop_reports',
		'manage_jigoshop_orders',
		'manage_jigoshop_coupons',
		'manage_jigoshop_products',
		'manage_jigoshop_emails'
	);

	$capability_types = array('product', 'shop_order', 'shop_coupon', 'shop_email');
	foreach ($capability_types as $capability_type) {
		$capabilities[$capability_type] = array(
			// Post type
			"edit_{$capability_type}",
			"read_{$capability_type}",
			"delete_{$capability_type}",
			"edit_{$capability_type}s",
			"edit_others_{$capability_type}s",
			"publish_{$capability_type}s",
			"read_private_{$capability_type}s",
			"delete_{$capability_type}s",
			"delete_private_{$capability_type}s",
			"delete_published_{$capability_type}s",
			"delete_others_{$capability_type}s",
			"edit_private_{$capability_type}s",
			"edit_published_{$capability_type}s",
			// Terms
			"manage_{$capability_type}_terms",
			"edit_{$capability_type}_terms",
			"delete_{$capability_type}_terms",
			"assign_{$capability_type}_terms"
		);
	}

	return $capabilities;
}

function jigoshop_roles_init()
{
	global $wp_roles;

	if (class_exists('WP_Roles')) {
		if (!isset($wp_roles)) {
			$wp_roles = new WP_Roles();
		}
	}

	if (is_object($wp_roles)) {
		// Customer role
		add_role('customer', __('Customer', 'jigoshop'), array(
			'read' => true,
			'edit_posts' => false,
			'delete_posts' => false
		));

		// Shop manager role
		add_role('shop_manager', __('Shop Manager', 'jigoshop'), array(
			'read' => true,
			'read_private_pages' => true,
			'read_private_posts' => true,
			'edit_users' => true,
			'edit_posts' => true,
			'edit_pages' => true,
			'edit_published_posts' => true,
			'edit_published_pages' => true,
			'edit_private_pages' => true,
			'edit_private_posts' => true,
			'edit_others_posts' => true,
			'edit_others_pages' => true,
			'publish_posts' => true,
			'publish_pages' => true,
			'delete_posts' => true,
			'delete_pages' => true,
			'delete_private_pages' => true,
			'delete_private_posts' => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'delete_others_posts' => true,
			'delete_others_pages' => true,
			'manage_categories' => true,
			'manage_links' => true,
			'moderate_comments' => true,
			'unfiltered_html' => true,
			'upload_files' => true,
			'export' => true,
			'import' => true,
		));

		$capabilities = jigoshop_get_core_capabilities();
		foreach ($capabilities as $cap_group) {
			foreach ($cap_group as $cap) {
				$wp_roles->add_cap('administrator', $cap);
				$wp_roles->add_cap('shop_manager', $cap);
			}
		}
	}
}

function jigoshop_prepare_dashboard_title($title)
{
	$result = '<span>'.preg_replace('/ /', '</span> ', $title, 1);
	if (strpos($result, '</span>') === false) {
		$result .= '</span>';
	}

	return $result;
}

/**
 * Enqueues script.
 * Calls filter `jrto_enqueue_script`. If the filter returns empty value the script is omitted.
 * Available options:
 *   * version - Wordpress script version number
 *   * in_footer - is this script required to add to the footer?
 *   * page - list of pages to use the script
 * Options could be extended by plugins.
 *
 * @param string $handle Handle name.
 * @param bool $src Source file.
 * @param array $dependencies List of dependencies to the script.
 * @param array $options List of options.
 */
function jigoshop_add_script($handle, $src, array $dependencies = array(), array $options = array())
{
	$page = isset($options['page']) ? (array)$options['page'] : array('all');

	if (is_jigoshop_page($page)) {
		$version = isset($options['version']) ? $options['version'] : false;
		$footer = isset($options['in_footer']) ? $options['in_footer'] : false;
		wp_enqueue_script($handle, $src, $dependencies, $version, $footer);
	}
}

/**
 * Removes script from enqueued list.
 * Calls filter `jigoshop_remove_script`. If the filter returns empty value the script is omitted.
 * Available options:
 *   * page - list of pages to use the script
 * Options could be extended by plugins.
 *
 * @param string $handle Handle name.
 * @param array $options List of options.
 */
function jigoshop_remove_script($handle, array $options = array())
{
	$page = isset($options['page']) ? (array)$options['page'] : array('all');

	if (is_jigoshop_page($page)) {
		wp_deregister_script($handle);
	}
}

/**
 * Localizes script.
 * Calls filter `jigoshop_localize_script`. If the filter returns empty value the script is omitted.
 *
 * @param string $handle Handle name.
 * @param string $object Object name.
 * @param array $values List of values to localize.
 */
function jigoshop_localize_script($handle, $object, array $values)
{
	wp_localize_script($handle, $object, $values);
}

/**
 * Enqueues stylesheet.
 * Calls filter `jigoshop_add_style`. If the filter returns empty value the style is omitted.
 * Available options:
 *   * version - Wordpress script version number
 *   * media - CSS media this script represents
 *   * page - list of pages to use the style
 * Options could be extended by plugins.
 *
 * @param string $handle Handle name.
 * @param bool $src Source file.
 * @param array $dependencies List of dependencies to the stylesheet.
 * @param array $options List of options.
 */
function jigoshop_add_style($handle, $src, array $dependencies = array(), array $options = array())
{
	$page = isset($options['page']) ? (array)$options['page'] : array('all');

	if (is_jigoshop_page($page)) {
		$version = isset($options['version']) ? $options['version'] : false;
		$media = isset($options['media']) ? $options['media'] : 'all';
		wp_enqueue_style($handle, $src, $dependencies, $version, $media);
	}
}

/**S
 * Removes style from enqueued list.
 * Calls filter `jigoshop_remove_style`. If the filter returns empty value the style is omitted.
 * Available options:
 *   * page - list of pages to use the style
 * Options could be extended by plugins.
 *
 * @param string $handle Handle name.
 * @param array $options List of options.
 */
function jigoshop_remove_style($handle, array $options = array())
{
	$page = isset($options['page']) ? (array)$options['page'] : array('all');

	if (is_jigoshop_page($page)) {
		wp_deregister_style($handle);
	}
}

/**
 * Checks if current page is one of given page types.
 *
 * @param string|array $pages List of page types to check.
 * @return bool Is current page one of provided?
 */
function is_jigoshop_page($pages)
{
	$result = false;
	$pages = is_array($pages) ? $pages : array($pages);

	foreach ($pages as $page) {
		$result = $result || is_jigoshop_single_page($page);
	}

	return $result;
}

// Define all Jigoshop page constants
define('JIGOSHOP_CART', 'cart');
define('JIGOSHOP_CHECKOUT', 'checkout');
define('JIGOSHOP_PAY', 'pay');
define('JIGOSHOP_THANK_YOU', 'thanks');
define('JIGOSHOP_MY_ACCOUNT', 'myaccount');
define('JIGOSHOP_EDIT_ADDRESS', 'edit_address');
define('JIGOSHOP_VIEW_ORDER', 'view_order');
define('JIGOSHOP_CHANGE_PASSWORD', 'change_password');
define('JIGOSHOP_PRODUCT', 'product');
define('JIGOSHOP_PRODUCT_CATEGORY', 'product_category');
define('JIGOSHOP_PRODUCT_LIST', 'product_list');
define('JIGOSHOP_PRODUCT_TAG', 'product_tag');
define('JIGOSHOP_ALL', 'all');

/**
 * Returns list of pages supported by is_jigoshop_single_page() and is_jigoshop_page().
 *
 * @return array List of supported pages.
 */
function jigoshop_get_available_pages()
{
	return array(
		JIGOSHOP_CART,
		JIGOSHOP_PAY,
		JIGOSHOP_CHECKOUT,
		JIGOSHOP_THANK_YOU,
		JIGOSHOP_EDIT_ADDRESS,
		JIGOSHOP_MY_ACCOUNT,
		JIGOSHOP_VIEW_ORDER,
		JIGOSHOP_CHANGE_PASSWORD,
		JIGOSHOP_PRODUCT,
		JIGOSHOP_PRODUCT_CATEGORY,
		JIGOSHOP_PRODUCT_TAG,
		JIGOSHOP_PRODUCT_LIST,
		JIGOSHOP_ALL,
	);
}

/**
 * Checks if current page is of given page type.
 *
 * @param string $page Page type.
 * @return bool Is current page the one from name?
 */
function is_jigoshop_single_page($page)
{
	switch ($page) {
		case JIGOSHOP_CART:
			return is_cart();
		case JIGOSHOP_CHECKOUT:
			return is_checkout();
		case JIGOSHOP_PAY:
			return is_page(jigoshop_get_page_id(JIGOSHOP_PAY));
		case JIGOSHOP_THANK_YOU:
			return is_page(jigoshop_get_page_id(JIGOSHOP_THANK_YOU));
		case JIGOSHOP_MY_ACCOUNT:
			return is_page(jigoshop_get_page_id(JIGOSHOP_MY_ACCOUNT));
		case JIGOSHOP_EDIT_ADDRESS:
			return is_page(jigoshop_get_page_id(JIGOSHOP_EDIT_ADDRESS));
		case JIGOSHOP_VIEW_ORDER:
			return is_page(jigoshop_get_page_id(JIGOSHOP_VIEW_ORDER));
		case JIGOSHOP_CHANGE_PASSWORD:
			return is_page(jigoshop_get_page_id(JIGOSHOP_CHANGE_PASSWORD));
		case JIGOSHOP_PRODUCT:
			return is_product();
		case JIGOSHOP_PRODUCT_CATEGORY:
			return is_product_category();
		case JIGOSHOP_PRODUCT_LIST:
			return is_product_list();
		case JIGOSHOP_PRODUCT_TAG:
			return is_product_tag();
		case JIGOSHOP_ALL:
			return true;
		default:
			return jigoshop_is_admin_page() == $page;
	}
}

/**
 * Jigoshop Frontend Styles and Scripts
 */
add_action('init', 'jigoshop_frontend_scripts', 1);
function jigoshop_frontend_scripts()
{
	$options = Jigoshop_Base::get_options();
	$frontend_css = JIGOSHOP_URL.'/assets/css/frontend.css';
	$theme_css = file_exists(get_stylesheet_directory().'/jigoshop/style.css')
		? get_stylesheet_directory_uri().'/jigoshop/style.css'
		: $frontend_css;

	if ($options->get('jigoshop_disable_css') == 'no') {
		if ($options->get('jigoshop_frontend_with_theme_css') == 'yes' && $frontend_css != $theme_css) {
			jrto_enqueue_style('frontend', 'jigoshop_theme_styles', $frontend_css);
		}
		jrto_enqueue_style('frontend', 'jigoshop_styles', $theme_css);
	}

	wp_enqueue_script('jquery');
	wp_register_script('jquery-blockui', '//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js', array('jquery'), '2.66.0');
	wp_enqueue_script('jquery-blockui');
	jrto_enqueue_script('frontend', 'jigoshop_global', JIGOSHOP_URL.'/assets/js/global.js', array('jquery'), array('in_footer' => true));

	if ($options->get('jigoshop_disable_fancybox') == 'no') {
		jrto_enqueue_script('frontend', 'prettyPhoto', JIGOSHOP_URL.'/assets/js/jquery.prettyPhoto.js', array('jquery'), array('in_footer' => true));
		jrto_enqueue_style('frontend', 'prettyPhoto', JIGOSHOP_URL.'/assets/css/prettyPhoto.css');
	}

	jrto_enqueue_script('frontend', 'jigoshop-cart', JIGOSHOP_URL.'/assets/js/cart.js', array('jquery'), array('in_footer' => true, 'page' => JIGOSHOP_CART));
	jrto_enqueue_script('frontend', 'jigoshop-checkout', JIGOSHOP_URL.'/assets/js/checkout.js', array('jquery', 'jquery-blockui'), array('in_footer' => true, 'page' => array(JIGOSHOP_CHECKOUT, JIGOSHOP_PAY)));
	jrto_enqueue_script('frontend', 'jigoshop-validation', JIGOSHOP_URL.'/assets/js/validation.js', array(), array('in_footer' => true, 'page' => JIGOSHOP_CHECKOUT));
	jrto_enqueue_script('frontend', 'jigoshop-payment', JIGOSHOP_URL.'/assets/js/pay.js', array('jquery'), array('page' => JIGOSHOP_PAY));
	jrto_enqueue_script('frontend', 'jigoshop-single-product', JIGOSHOP_URL.'/assets/js/single-product.js', array('jquery'), array('in_footer' => true, 'page' => JIGOSHOP_PRODUCT));
	jrto_enqueue_script('frontend', 'jigoshop-countries', JIGOSHOP_URL.'/assets/js/countries.js', array(), array(
		'in_footer' => true,
		'page' => array(JIGOSHOP_CHECKOUT, JIGOSHOP_CART, JIGOSHOP_EDIT_ADDRESS)
	));


	/* Script.js variables */
	// TODO: clean this up, a lot aren't even used anymore, do away with it
	$jigoshop_params = array(
		'ajax_url' => admin_url('admin-ajax.php', 'jigoshop'),
		'assets_url' => JIGOSHOP_URL,
		'validate_postcode' => $options->get('jigoshop_enable_postcode_validating', 'no'),
		'checkout_url' => admin_url('admin-ajax.php?action=jigoshop-checkout', 'jigoshop'),
		'currency_symbol' => get_jigoshop_currency_symbol(),
		'get_variation_nonce' => wp_create_nonce("get-variation"),
		'load_fancybox' => $options->get('jigoshop_disable_fancybox') == 'no',
		'option_guest_checkout' => $options->get('jigoshop_enable_guest_checkout'),
		'select_state_text' => __('Select a state&hellip;', 'jigoshop'),
		'state_text' => __('state', 'jigoshop'),
		'ratings_message' => __('Please select a star to rate your review.', 'jigoshop'),
		'update_order_review_nonce' => wp_create_nonce("update-order-review"),
		'billing_state' => jigoshop_customer::get_state(),
		'shipping_state' => jigoshop_customer::get_shipping_state(),
		'is_checkout' => (is_page(jigoshop_get_page_id('checkout')) || is_page(jigoshop_get_page_id('pay'))),
		'error_hide_time' => Jigoshop_Base::get_options()->get('jigoshop_error_disappear_time', 8000),
		'message_hide_time' => Jigoshop_Base::get_options()->get('jigoshop_message_disappear_time', 4000),
	);

	if (isset(jigoshop_session::instance()->min_price)) {
		$jigoshop_params['min_price'] = $_GET['min_price'];
	}

	if (isset(jigoshop_session::instance()->max_price)) {
		$jigoshop_params['max_price'] = $_GET['max_price'];
	}

	$jigoshop_params = apply_filters('jigoshop_params', $jigoshop_params);
	jrto_localize_script('jigoshop_global', 'jigoshop_params', $jigoshop_params);
}

/**
 * Add post thumbnail support to WordPress if needed
 */
add_action('after_setup_theme', 'jigoshop_check_thumbnail_support', 99);
function jigoshop_check_thumbnail_support()
{
	if (!current_theme_supports('post-thumbnails')) {
		add_theme_support('post-thumbnails');
		remove_post_type_support('post', 'thumbnail');
		remove_post_type_support('page', 'thumbnail');
	} else {
		add_post_type_support('product', 'thumbnail');
	}
}

add_action('current_screen', 'jigoshop_admin_styles');
function jigoshop_admin_styles()
{
	/* Our setting icons */
	jrto_enqueue_style('admin', 'jigoshop_admin_icons_style', JIGOSHOP_URL.'/assets/css/admin-icons.css');

	global $current_screen;
	if ($current_screen === null || (!jigoshop_is_admin_page() && $current_screen->base !== 'user-edit')) {
		return;
	}

	jrto_enqueue_style('admin', 'jigoshop-select2', JIGOSHOP_URL.'/assets/css/select2.css');

	if (jigoshop_is_admin_page()) {
		wp_enqueue_style('thickbox');
		jrto_enqueue_style('admin', 'jigoshop_admin_styles', JIGOSHOP_URL.'/assets/css/admin.css');
		jrto_enqueue_style('admin', 'jigoshop-jquery-ui', JIGOSHOP_URL.'/assets/css/jquery-ui.css');
		jrto_enqueue_style('admin', 'prettyPhoto', JIGOSHOP_URL.'/assets/css/prettyPhoto.css');
	}
}

add_action('admin_enqueue_scripts', 'jigoshop_admin_scripts', 1);
function jigoshop_admin_scripts()
{
	global $current_screen;
	if (!jigoshop_is_admin_page() && $current_screen->base !== 'user-edit') {
		return;
	}

	jrto_enqueue_script('admin', 'jigoshop-select2', JIGOSHOP_URL.'/assets/js/select2.min.js', array('jquery'));
	jrto_enqueue_script('admin', 'jigoshop-editor-shortcodes', JIGOSHOP_URL.'/assets/js/editor-shortcodes.js', array('jquery'));
	wp_register_script('jquery-blockui', '//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js', array('jquery'));

	if (jigoshop_is_admin_page()) {
		wp_enqueue_media();
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-blockui');
		jrto_enqueue_script('admin', 'jigoshop_datetimepicker', JIGOSHOP_URL.'/assets/js/jquery-ui-timepicker-addon.min.js', array('jquery', 'jquery-ui-datepicker'));
		jrto_enqueue_script('admin', 'jigoshop_media', JIGOSHOP_URL.'/assets/js/media.js', array('jquery', 'media-editor'));
		jrto_enqueue_script('admin', 'jigoshop_backend', JIGOSHOP_URL.'/assets/js/backend.js', array('jquery'), array('version' => JIGOSHOP_VERSION));
		if($current_screen->base == 'edit-tags' && Jigoshop_Base::get_options()->get('jigoshop_enable_draggable_categories') == 'yes') {
			jrto_enqueue_script('admin', 'jigoshop_draggable_categories', JIGOSHOP_URL.'/assets/js/draggable_categories.js', array('jquery'), array('version' => JIGOSHOP_VERSION));
		}
		jrto_enqueue_script('admin', 'jquery_flot', JIGOSHOP_URL.'/assets/js/admin/jquery.flot.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop')
			)
		);
		jrto_enqueue_script('admin', 'jquery_flot_pie', JIGOSHOP_URL.'/assets/js/admin/jquery.flot.pie.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop')
			)
		);
		jrto_enqueue_script('admin', 'jquery_flot_resize', JIGOSHOP_URL.'/assets/js/admin/jquery.flot.resize.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop')
			)
		);
		jrto_enqueue_script('admin', 'jquery_flot_stack', JIGOSHOP_URL.'/assets/js/admin/jquery.flot.stack.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop')
			)
		);
		jrto_enqueue_script('admin', 'jquery_flot_time', JIGOSHOP_URL.'/assets/js/admin/jquery.flot.time.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop')
			)
		);
		jrto_enqueue_script('admin', 'jigoshop_reports', JIGOSHOP_URL.'/assets/js/admin/reports.js', array('jquery'), array(
				'version' => JIGOSHOP_VERSION,
				'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop')
			)
		);
		jrto_enqueue_script('admin', 'jquery.tiptip', JIGOSHOP_URL.'/assets/js/admin/jquery.tipTip.min.js', array('jquery'), array(
				'version' => '1.3',
				'page' => array('jigoshop_page_jigoshop_reports', 'jigoshop_page_jigoshop_system_info', 'toplevel_page_jigoshop')
			)
		);
		jrto_enqueue_script('admin', 'jquery.zeroclipboard', JIGOSHOP_URL.'/assets/js/admin/jquery.zeroclipboard.min.js', array('jquery'), array(
				'version' => '0.2.0',
				'page' => array('jigoshop_page_jigoshop_system_info', 'toplevel_page_jigoshop')
			)
		);

		jrto_localize_script('jigoshop_backend', 'jigoshop_params', array(
			'ajax_url' => admin_url('admin-ajax.php', 'jigoshop'),
			'search_products_nonce' => wp_create_nonce("search-products"),
		));

		$pagenow = jigoshop_is_admin_page();
		/**
		 * Disable autosaves on the order and coupon pages. Prevents the javascript alert when modifying.
		 * `wp_deregister_script( 'autosave' )` would produce errors, so we use a filter instead.
		 */
		if ($pagenow == 'shop_order' || $pagenow == 'shop_coupon') {
			add_filter('script_loader_src', 'jigoshop_disable_autosave', 10, 2);
		}
	}
}

function jigoshop_register_shortcode_editor( $plugin_array ) {
	$plugin_array['jigoshopShortcodes'] = JIGOSHOP_URL.'/assets/js/editor-shortcodes.js';
	return $plugin_array;
}

function jigoshop_register_shortcode_buttons( $buttons ) {

	array_push( $buttons, "jrto_enqueue_cart" );
	array_push( $buttons, "jigoshop_show_product" );
	array_push( $buttons, "jigoshop_show_category" );
	array_push( $buttons, "jigoshop_show_featured_products" );
	array_push( $buttons, "jigoshop_show_selected_products" );
	array_push( $buttons, "jigoshop_product_search" );
	array_push( $buttons, "jigoshop_recent_products" );
	array_push( $buttons, "jigoshop_sale_products" );

	return $buttons;
}

/**
 *  Load required CSS files when frontend styles
 */
add_action('init', 'jigoshop_check_required_css', 99);
function jigoshop_check_required_css()
{
	global $wp_styles;
	$options = Jigoshop_Base::get_options();

	if (empty($wp_styles->registered['jigoshop_styles'])) {
		jrto_enqueue_style('frontend', 'jigoshop-jquery-ui', JIGOSHOP_URL.'/assets/css/jquery-ui.css');
		jrto_enqueue_style('frontend', 'jigoshop-select2', JIGOSHOP_URL.'/assets/css/select2.css');
		if ($options->get('jigoshop_disable_fancybox') == 'no') {
			jrto_enqueue_style('frontend', 'prettyPhoto', JIGOSHOP_URL.'/assets/css/prettyPhoto.css');
		}
	}
}


//### Functions #########################################################

/**
 * Set Jigoshop Product Image Sizes for WordPress based on Admin->Jigoshop->Settings->Images
 */
function jigoshop_set_image_sizes()
{
	$options = Jigoshop_Base::get_options();

	$sizes = array(
		'shop_tiny' => 'tiny',
		'shop_thumbnail' => 'thumbnail',
		'shop_small' => 'catalog',
		'shop_large' => 'featured'
	);

	foreach ($sizes as $size => $altSize) {
		add_image_size(
			$size,
			$options->get('jigoshop_'.$size.'_w'),
			$options->get('jigoshop_'.$size.'_h'),
			($options->get('jigoshop_use_wordpress_'.$altSize.'_crop', 'no') == 'yes')
		);
	}

	/* The elephant in the room (._. ) */
	add_image_size('admin_product_list', 32, 32, $options->get('jigoshop_use_wordpress_tiny_crop', 'no') == 'yes' ? true : false);
}

/**
 * Get Jigoshop Product Image Size based on Admin->Jigoshop->Settings->Images
 *
 * @param string $size - one of the 4 defined Jigoshop image sizes
 * @return array - an array containing the width and height of the required size
 * @since 0.9.9
 */
function jigoshop_get_image_size($size)
{
	$options = Jigoshop_Base::get_options();
	if (is_array($size)) {
		return $size;
	}

	switch ($size) {
		case 'admin_product_list':
			$image_size = array(32, 32);
			break;
		case 'shop_tiny':
			$image_size = array($options->get('jigoshop_shop_tiny_w'), $options->get('jigoshop_shop_tiny_h'));
			break;
		case 'shop_thumbnail':
			$image_size = array($options->get('jigoshop_shop_thumbnail_w'), $options->get('jigoshop_shop_thumbnail_h'));
			break;
		case 'shop_small':
			$image_size = array($options->get('jigoshop_shop_small_w'), $options->get('jigoshop_shop_small_h'));
			break;
		case 'shop_large':
			$image_size = array($options->get('jigoshop_shop_large_w'), $options->get('jigoshop_shop_large_h'));
			break;
		default:
			$image_size = array($options->get('jigoshop_shop_small_w'), $options->get('jigoshop_shop_small_h'));
			break;
	}

	return $image_size;
}

function jigoshop_is_admin_page()
{
	global $current_screen;

	if ($current_screen == null) {
		return false;
	}

	if ($current_screen->post_type == 'product' || $current_screen->post_type == 'shop_order' || $current_screen->post_type == 'shop_coupon' || $current_screen->post_type == 'shop_email') {
		return $current_screen->post_type;
	}

	if (strstr($current_screen->id, 'jigoshop')) {
		return $current_screen->id;
	}

	return false;
}

function jigoshop_disable_autosave($src, $handle)
{
	if ('autosave' != $handle) {
		return $src;
	}

	return '';
}

/**
 * Adds a demo store banner to the site
 */
function jigoshop_demo_store()
{
	if (Jigoshop_Base::get_options()->get('jigoshop_demo_store') == 'yes' && is_jigoshop()){
		$bannner_text = apply_filters('jigoshop_demo_banner_text', __('This is a demo store for testing purposes &mdash; no orders shall be fulfilled.', 'jigoshop'));
		echo '<p class="demo_store">'.$bannner_text.'</p>';
	}
}
add_action('wp_footer', 'jigoshop_demo_store');

/**
 * Adds social sharing code to footer
 */
function jigoshop_sharethis()
{
	$option = Jigoshop_Base::get_options();
	if (is_single() && $option->get('jigoshop_sharethis')) {
		if (is_ssl()) {
			$sharethis = 'https://ws.sharethis.com/button/buttons.js';
		} else {
			$sharethis = 'http://w.sharethis.com/button/buttons.js';
		}

		echo '<script type="text/javascript">var switchTo5x=true;</script><script type="text/javascript" src="'.$sharethis.'"></script><script type="text/javascript">stLight.options({publisher:"'.$option->get('jigoshop_sharethis').'"});</script>';
	}
}
add_action('wp_footer', 'jigoshop_sharethis');

/**
 * Jigoshop Mail 'from' name on emails
 * We will add a filter to WordPress to get this as the site name when emails are sent
 */
function jigoshop_mail_from_name()
{
	return esc_attr(get_bloginfo('name'));
}

/**
 * Allow product_cat in the permalinks for products.
 *
 * @param string $permalink The existing permalink URL.
 * @param WP_Post $post
 * @return string
 */
function jigoshop_product_cat_filter_post_link($permalink, $post)
{
	if ($post->post_type !== 'product') {
		return $permalink;
	}

	// Abort early if the placeholder rewrite tag isn't in the generated URL
	if (false === strpos($permalink, '%product_cat%')) {
		return $permalink;
	}

	// Get the custom taxonomy terms in use by this post
	$terms = get_the_terms($post->ID, 'product_cat');

	if (empty($terms)) {
		// If no terms are assigned to this post, use a string instead
		$permalink = str_replace('%product_cat%', _x('product', 'slug', 'jigoshop'), $permalink);
	} else {
		// Replace the placeholder rewrite tag with the first term's slug
		$first_term = apply_filters('jigoshop_product_cat_permalink_terms', array_shift($terms), $terms);
		$permalink = str_replace('%product_cat%', $first_term->slug, $permalink);
	}

	return $permalink;
}
add_filter('post_type_link', 'jigoshop_product_cat_filter_post_link', 10, 2);

/**
 * Helper function to locate proper template and set up environment based on passed array.
 *
 * @param string $template Template name.
 * @param array $variables Template variables
 */
function jigoshop_render($template, array $variables) {
	$file = jigoshop_locate_template($template);
	extract($variables);
	/** @noinspection PhpIncludeInspection */
	require($file);
}

/**
 * Helper function to locate proper template and set up environment based on passed array.
 * Returns value of rendered template as a string.
 *
 * @param string $template Template name.
 * @param array $variables Template variables
 * @return string
 */
function jigoshop_render_result($template, array $variables) {
	ob_start();
	jigoshop_render($template, $variables);
	return ob_get_clean();
}

/**
 * Evaluates to true only on the Shop page, not Product categories and tags
 * Note:is used to replace is_page( jigoshop_get_page_id( 'shop' ) )
 *
 * @return bool
 * @since 0.9.9
 */
function is_shop()
{
	return is_post_type_archive('product') || is_page(jigoshop_get_page_id('shop'));
}

/**
 * Evaluates to true only on the Category Pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_product_category()
{
	return is_tax('product_cat');
}

/**
 * Evaluates to true only on the Tag Pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_product_tag()
{
	return is_tax('product_tag');
}

/**
 * Evaluates to true only on the Single Product Page
 *
 * @return bool
 * @since 0.9.9
 */
function is_product()
{
	return is_singular(array('product'));
}

/**
 * Evaluates to true only on Shop, Product Category, and Product Tag pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_product_list()
{
	$is_list = false;
	$is_list |= is_shop();
	$is_list |= is_product_tag();
	$is_list |= is_product_category();

	return $is_list;
}

/**
 * Evaluates to true for all Jigoshop pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_jigoshop()
{
	$is_jigo = false;
	$is_jigo |= is_content_wrapped();
	$is_jigo |= is_account();
	$is_jigo |= is_cart();
	$is_jigo |= is_checkout();
	$is_jigo |= is_order_tracker();

	return $is_jigo;
}

/**
 * Evaluates to true only on the Shop, Category, Tag and Single Product Pages
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_content_wrapped()
{
	$is_wrapped = false;
	$is_wrapped |= is_product_list();
	$is_wrapped |= is_product();

	return $is_wrapped;
}

/**
 * Jigoshop page IDs
 * returns -1 if no page is found
 */
if (!function_exists('jigoshop_get_page_id')) {
	function jigoshop_get_page_id($page)
	{
		$jigoshop_options = Jigoshop_Base::get_options();
		$page = apply_filters('jigoshop_get_'.$page.'_page_id', $jigoshop_options->get('jigoshop_'.$page.'_page_id'));

		return ($page) ? $page : -1;
	}
}

/**
 * Evaluates to true only on the Order Tracking page
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_order_tracker()
{
	return is_page(jigoshop_get_page_id('track_order'));
}

/**
 * Evaluates to true only on the Cart page
 *
 * @return bool
 * @since 0.9.8
 */
function is_cart()
{
	return is_page(jigoshop_get_page_id('cart'));
}

/**
 * Evaluates to true only on the Checkout or Pay pages
 *
 * @return bool
 * @since 0.9.8
 */
function is_checkout()
{
	return is_page(jigoshop_get_page_id('checkout')) | is_page(jigoshop_get_page_id('pay'));
}

/**
 * Evaluates to true only on the main Account or any sub-account pages
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_account()
{
	$is_account = false;
	$is_account |= is_page(jigoshop_get_page_id('myaccount'));
	$is_account |= is_page(jigoshop_get_page_id('edit_address'));
	$is_account |= is_page(jigoshop_get_page_id('change_password'));
	$is_account |= is_page(jigoshop_get_page_id('view_order'));

	return $is_account;
}

if (!function_exists('is_ajax')) {
	function is_ajax()
	{
		if (defined('DOING_AJAX')) {
			return true;
		}

		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}
}

function jigoshop_force_ssl()
{
	if (is_checkout() && !is_ssl()) {
		wp_safe_redirect(str_replace('http:', 'https:', get_permalink(jigoshop_get_page_id('checkout'))), 301);
		exit;
	}
}

if (!is_admin() && Jigoshop_Base::get_options()->get('jigoshop_force_ssl_checkout') == 'yes') {
	add_action('wp', 'jigoshop_force_ssl');
}

function jigoshop_force_ssl_images($content)
{
	if (is_ssl()) {
		if (is_array($content)) {
			$content = array_map('jigoshop_force_ssl_images', $content);
		} else {
			$content = str_replace('http:', 'https:', $content);
		}
	}

	return $content;
}

add_filter('post_thumbnail_html', 'jigoshop_force_ssl_images');
add_filter('widget_text', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_url', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_image_attributes', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_url', 'jigoshop_force_ssl_images');

function jigoshop_force_ssl_urls($url)
{
	if (is_ssl()) {
		$url = str_replace('http:', 'https:', $url);
	}

	return $url;
}

add_filter('option_siteurl', 'jigoshop_force_ssl_urls');
add_filter('option_home', 'jigoshop_force_ssl_urls');
add_filter('option_url', 'jigoshop_force_ssl_urls');
add_filter('option_wpurl', 'jigoshop_force_ssl_urls');
add_filter('option_stylesheet_url', 'jigoshop_force_ssl_urls');
add_filter('option_template_url', 'jigoshop_force_ssl_urls');
add_filter('script_loader_src', 'jigoshop_force_ssl_urls');
add_filter('style_loader_src', 'jigoshop_force_ssl_urls');

function get_jigoshop_currency_symbol()
{
	$options = Jigoshop_Base::get_options();
	$currency = $options->get('jigoshop_currency', 'USD');
	$symbols = jigoshop::currency_symbols();
	$currency_symbol = $symbols[$currency];

	return apply_filters('jigoshop_currency_symbol', $currency_symbol, $currency);
}

function jigoshop_price($price, $args = array())
{
	$options = Jigoshop_Base::get_options();
	$ex_tax_label = 0;
	$with_currency = true;

	extract(shortcode_atts(array(
		'ex_tax_label' => 0, // 0 for no label, 1 for ex. tax, 2 for inc. tax
		'with_currency' => true
	), $args));

	if ($ex_tax_label === 1) {
		$tax_label = __(' <small>(ex. tax)</small>', 'jigoshop');
	} else {
		if ($ex_tax_label === 2) {
			$tax_label = __(' <small>(inc. tax)</small>', 'jigoshop');
		} else {
			$tax_label = '';
		}
	}

	$price = number_format(
		(double)$price,
		(int)$options->get('jigoshop_price_num_decimals'),
		$options->get('jigoshop_price_decimal_sep'),
		$options->get('jigoshop_price_thousand_sep')
	);

	$return = $price;

	if ($with_currency) {
		$currency_pos = $options->get('jigoshop_currency_pos');
		$currency_symbol = get_jigoshop_currency_symbol();
		$currency_code = $options->get('jigoshop_currency');

		switch ($currency_pos) {
			case 'left':
				$return = $currency_symbol.$price;
				break;
			case 'left_space':
				$return = $currency_symbol.' '.$price;
				break;
			case 'right':
				$return = $price.$currency_symbol;
				break;
			case 'right_space':
				$return = $price.' '.$currency_symbol;
				break;
			case 'left_code':
				$return = $currency_code.$price;
				break;
			case 'left_code_space':
				$return = $currency_code.' '.$price;
				break;
			case 'right_code':
				$return = $price.$currency_code;
				break;
			case 'right_code_space':
				$return = $price.' '.$currency_code;
				break;
			case 'code_symbol':
				$return = $currency_code.$price.$currency_symbol;
				break;
			case 'code_symbol_space':
				$return = $currency_code.' '.$price.' '.$currency_symbol;
				break;
			case 'symbol_code':
				$return = $currency_symbol.$price.$currency_code;
				break;
			case 'symbol_code_space':
				$return = $currency_symbol.' '.$price.' '.$currency_code;
				break;
		}

		// only show tax label (ex. tax) if we are going to show the price with currency as well. Otherwise we just want the formatted price
		if ($options->get('jigoshop_calc_taxes') == 'yes') {
			$return .= $tax_label;
		}
	}

	return apply_filters('jigoshop_price_display_filter', $return);
}

/** Show variation info if set
 *
 * @param jigoshop_product $product
 * @param array $variation_data
 * @param bool $flat
 * @return string
 */
function jigoshop_get_formatted_variation(jigoshop_product $product, $variation_data = array(), $flat = false)
{
	$return = '';
	if (!is_array($variation_data)) {
		$variation_data = array();
	}

	if ($product instanceof jigoshop_product_variation) {
		$variation_data = array_merge(array_filter($variation_data), array_filter($product->variation_data));

		if (!$flat) {
			$return = '<dl class="variation">';
		}

		$variation_list = array();
		$added = array();

		foreach ($variation_data as $name => $value) {
			if (empty($value)) {
				continue;
			}

			$name = str_replace('tax_', '', $name);

			if (in_array($name, $added)) {
				continue;
			}

			$added[] = $name;

			if (taxonomy_exists('pa_'.$name)) {
				$terms = get_terms('pa_'.$name, array('orderby' => 'slug', 'hide_empty' => '0'));
				foreach ($terms as $term) {
					if ($term->slug == $value) {
						$value = $term->name;
					}
				}
				$name = get_taxonomy('pa_'.$name)->labels->name;
				$name = $product->attribute_label('pa_'.$name);
			}

			// TODO: if it is a custom text attribute, 'pa_' taxonomies are not created and we
			// have no way to get the 'label' as submitted on the Edit Product->Attributes tab.
			// (don't ask me why not, I don't know, but it seems that we should be creating taxonomies)
			// this function really requires the product passed to it for: $product->attribute_label( $name )
			if ($flat) {
				$variation_list[] = $name.': '.$value;
			} else {
				$variation_list[] = '<dt>'.$name.':</dt><dd>'.$value.'</dd>';
			}
		}

		if ($flat) {
			$return .= implode(', ', $variation_list);
		} else {
			$return .= implode('', $variation_list);
		}

		if (!$flat) {
			$return .= '</dl>';
		}
	}

	return $return;
}

// Remove pingbacks/trackbacks from Comments Feed
// betterwp.net/wordpress-tips/remove-pingbackstrackbacks-from-comments-feed/
add_filter('request', 'jigoshop_filter_request');
function jigoshop_filter_request($qv)
{
	if (isset($qv['feed']) && !empty($qv['withcomments'])) {
		add_filter('comment_feed_where', 'jigoshop_comment_feed_where');
	}

	return $qv;
}

function jigoshop_comment_feed_where($cwhere)
{
	$cwhere .= " AND comment_type != 'jigoshop' ";

	return $cwhere;
}

function jigoshop_let_to_num($v)
{
	$l = substr($v, -1);
	$ret = substr($v, 0, -1);
	switch (strtoupper($l)) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
	}

	return $ret;
}

function jigowatt_clean($var)
{
	return strip_tags(stripslashes(trim($var)));
}

//function jigowatt_clean($var)
//{
//	return jigoshop_clean($var);
//}

// Returns a float value
function jigoshop_sanitize_num($var)
{
	return strip_tags(stripslashes(floatval(preg_replace('/^[^[\-\+]0-9\.]/', '', $var))));
}

// Author: Sergey Biryukov
// Plugin URI: http://wordpress.org/extend/plugins/allow-cyrillic-usernames/
add_filter('sanitize_user', 'jigoshop_sanitize_user', 10, 3);
function jigoshop_sanitize_user($username, $raw_username, $strict)
{
	$username = wp_strip_all_tags($raw_username);
	$username = remove_accents($username);
	$username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
	$username = preg_replace('/&.+?;/', '', $username); // Kill entities

	if ($strict) {
		$username = preg_replace('|[^a-z?-?0-9 _.\-@]|iu', '', $username);
	}

	$username = trim($username);
	$username = preg_replace('|\s+|', ' ', $username);

	return $username;
}

add_action('wp_head', 'jigoshop_head_version');
function jigoshop_head_version()
{
	echo '<!-- Jigoshop Version: '.JIGOSHOP_VERSION.' -->'."\n";
}

global $jigoshop_body_classes;
add_action('wp_head', 'jigoshop_page_body_classes');
function jigoshop_page_body_classes()
{
	global $jigoshop_body_classes;
	$jigoshop_body_classes = (array)$jigoshop_body_classes;

	if (is_order_tracker()) {
		jigoshop_add_body_class(array('jigoshop', 'jigoshop-tracker'));
	}

	if (is_checkout()) {
		jigoshop_add_body_class(array('jigoshop', 'jigoshop-checkout'));
	}

	if (is_cart()) {
		jigoshop_add_body_class(array('jigoshop', 'jigoshop-cart'));
	}

	if (is_page(jigoshop_get_page_id('thanks'))) {
		jigoshop_add_body_class(array('jigoshop', 'jigoshop-thanks'));
	}
	if (is_page(jigoshop_get_page_id('pay'))) {

		jigoshop_add_body_class(array('jigoshop', 'jigoshop-pay'));
	}

	if (is_account()) {
		jigoshop_add_body_class(array('jigoshop', 'jigoshop-myaccount'));
	}
}

function jigoshop_add_body_class($class = array())
{
	global $jigoshop_body_classes;
	$jigoshop_body_classes = (array)$jigoshop_body_classes;
	$jigoshop_body_classes = array_unique(array_merge($class, $jigoshop_body_classes));
}

add_filter('body_class', 'jigoshop_body_class');
function jigoshop_body_class($classes)
{
	global $jigoshop_body_classes;
	$jigoshop_body_classes = (array)$jigoshop_body_classes;
	$classes = array_unique(array_merge($classes, $jigoshop_body_classes));
	return $classes;
}

//### Extra Review Field in comments #########################################################

function jigoshop_add_comment_rating($comment_id)
{
	if (isset($_POST['rating'])) {
		if (!$_POST['rating'] || $_POST['rating'] > 5 || $_POST['rating'] < 0) {
			$_POST['rating'] = 5;
		}

		add_comment_meta($comment_id, 'rating', $_POST['rating'], true);
	}
}
add_action('comment_post', 'jigoshop_add_comment_rating', 1);

function jigoshop_check_comment_rating($comment_data)
{
	// If posting a comment (not trackback etc) and not logged in
	if (isset($_POST['rating']) && !jigoshop::verify_nonce('comment_rating')) {
		wp_die(__('You have taken too long. Please go back and refresh the page.', 'jigoshop'));
	} else if (isset($_POST['rating']) && empty($_POST['rating']) && $comment_data['comment_type'] == '') {
		wp_die(__('Please rate the product.', "jigoshop"));
		exit;
	}

	return $comment_data;
}
add_filter('preprocess_comment', 'jigoshop_check_comment_rating', 0);

//### Comments #########################################################

function jigoshop_comments($comment)
{
	$GLOBALS['comment'] = $comment; ?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
	<div id="comment-<?php comment_ID(); ?>" class="comment_container">
		<?php echo get_avatar($comment, $size = '60'); ?>
		<div class="comment-text">
			<?php if ($rating = get_comment_meta($comment->comment_ID, 'rating', true)): ?>
				<div class="star-rating" title="<?php echo esc_attr($rating); ?>">
					<span style="width:<?php echo $rating * 16; ?>px"><?php echo $rating; ?> <?php _e('out of 5', 'jigoshop'); ?></span>
				</div>
			<?php endif; ?>
			<?php if ($comment->comment_approved == '0'): ?>
				<p class="meta"><em><?php _e('Your comment is awaiting approval', 'jigoshop'); ?></em></p>
			<?php else : ?>
				<p class="meta">
					<?php _e('Rating by', 'jigoshop'); ?> <strong class="reviewer vcard"><span
							class="fn"><?php comment_author(); ?></span></strong> <?php _e('on', 'jigoshop'); ?> <?php echo date_i18n(get_option('date_format'), strtotime(get_comment_date('Y-m-d'))); ?>
					:
				</p>
			<?php endif; ?>
			<div class="description"><?php comment_text(); ?></div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
</li>
<?php
}

//### Exclude order comments from front end #########################################################
add_filter('comments_clauses', 'jigoshop_exclude_order_admin_comments', 10, 1);
function jigoshop_exclude_order_admin_comments($clauses)
{
	global $wpdb, $typenow, $pagenow;

	// NOTE: bit of a hack, tests if we're in the admin & its an ajax call
	if (is_admin() && ($typenow == 'shop_order' || $pagenow == 'admin-ajax.php') && current_user_can('manage_jigoshop')) {
		return $clauses; // Don't hide when viewing orders in admin
	}
	if (!$clauses['join']) {
		$clauses['join'] = '';
	}
	if (!strstr($clauses['join'], "JOIN $wpdb->posts")) {
		$clauses['join'] .= " LEFT JOIN $wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID ";
	}
	if ($clauses['where']) {
		$clauses['where'] .= ' AND ';
	}

	$clauses['where'] .= " $wpdb->posts.post_type NOT IN ('shop_order') ";

	return $clauses;
}

/**
 * Support for Import/Export
 * WordPress import should work - however, it fails to import custom product attribute taxonomies.
 * This code grabs the file before it is imported and ensures the taxonomies are created.
 */
function jigoshop_import_start()
{
	global $wpdb;
	$jigoshop_options = Jigoshop_Base::get_options();

	$id = (int)$_POST['import_id'];
	$file = get_attached_file($id);

	$parser = new WXR_Parser();
	$import_data = $parser->parse($file);

	if (isset($import_data['posts'])) {
		$posts = $import_data['posts'];

		if ($posts && sizeof($posts) > 0) foreach ($posts as $post) {
			if ($post['post_type'] == 'product') {
				if ($post['terms'] && sizeof($post['terms']) > 0) {
					foreach ($post['terms'] as $term) {
						$domain = $term['domain'];
						if (strstr($domain, 'pa_')) {
							// Make sure it exists!
							if (!taxonomy_exists($domain)) {
								$nicename = sanitize_title(str_replace('pa_', '', $domain));

								$exists_in_db = $wpdb->get_var($wpdb->prepare("SELECT attribute_id FROM ".$wpdb->prefix."jigoshop_attribute_taxonomies WHERE attribute_name = %s;", $nicename));

								// Create the taxonomy
								if (!$exists_in_db) {
									$wpdb->insert($wpdb->prefix."jigoshop_attribute_taxonomies", array('attribute_name' => $nicename, 'attribute_type' => 'select'), array('%s', '%s'));
								}

								// Register the taxonomy now so that the import works!
								register_taxonomy($domain,
									array('product'),
									array(
										'hierarchical' => true,
										'labels' => array(
											'name' => $nicename,
											'singular_name' => $nicename,
											'search_items' => __('Search ', 'jigoshop').$nicename,
											'all_items' => __('All ', 'jigoshop').$nicename,
											'parent_item' => __('Parent ', 'jigoshop').$nicename,
											'parent_item_colon' => __('Parent ', 'jigoshop').$nicename.':',
											'edit_item' => __('Edit ', 'jigoshop').$nicename,
											'update_item' => __('Update ', 'jigoshop').$nicename,
											'add_new_item' => __('Add New ', 'jigoshop').$nicename,
											'new_item_name' => __('New ', 'jigoshop').$nicename
										),
										'show_ui' => false,
										'query_var' => true,
										'rewrite' => array('slug' => sanitize_title($nicename), 'with_front' => false, 'hierarchical' => true),
									)
								);

								$jigoshop_options->set('jigowatt_update_rewrite_rules', '1');
							}
						}
					}
				}
			}
		}
	}
}
add_action('import_start', 'jigoshop_import_start');

if (!function_exists('jigoshop_log')) {
	/**
	 * Logs to the debug log when you enable wordpress debug mode.
	 *
	 * @param string $from_class is the name of the php file that you are logging from.
	 * defaults to jigoshop if non is supplied.
	 * @param mixed $message this can be a regular string, array or object
	 */
	function jigoshop_log($message, $from_class = 'jigoshop')
	{
		if (WP_DEBUG === true) {
			if (is_array($message) || is_object($message)) {
				error_log($from_class.': '.print_r($message, true));
			} else {
				error_log($from_class.': '.$message);
			}
		}
	}
}
