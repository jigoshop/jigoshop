<?php
/**
 * Jigoshop
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * Plugin Name:			Jigoshop - WordPress eCommerce
 * Plugin URI:			http://jigoshop.com
 * Description:			An eCommerce plugin for WordPress.
 * Author:				Jigowatt
 * Author URI:			http://jigowatt.co.uk
 *
 * Version:				0.9.9.3
 * Requires at least:	3.1
 * Tested up to:		3.3 Beta 3
 *
 * @package    			Jigoshop
 * @category   			Core
 * @author     			Jigowatt
 * @copyright  			Copyright (c) 2011 Jigowatt Ltd.
 * @license    			http://jigoshop.com/license/commercial-edition
 */

@session_start();

if (!defined("JIGOSHOP_VERSION")) define("JIGOSHOP_VERSION", "0.9.9.3");
if (!defined("PHP_EOL")) define("PHP_EOL", "\r\n");

load_plugin_textdomain('jigoshop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');

/**
 * Installs and upgrades
 **/
register_activation_hook( __FILE__, 'install_jigoshop' );

function jigoshop_update_check() {
    if (get_site_option('jigoshop_db_version') != JIGOSHOP_VERSION) install_jigoshop();
}
if (is_admin()) add_action('init', 'jigoshop_update_check');

/**
 * Include core files and classes
 **/

include_once( 'classes/abstract/jigoshop_base.class.php' );
include_once( 'classes/abstract/jigoshop_singleton.php' );
include_once( 'classes/jigoshop_sanitize.class.php' );
include_once( 'classes/jigoshop.class.php' );
include_once( 'jigoshop_taxonomy.php' );
include_once( 'jigoshop_widgets.php' );
include_once( 'jigoshop_shortcodes.php' );
include_once( 'jigoshop_templates.php' );
include_once( 'jigoshop_template_actions.php' );
include_once( 'jigoshop_emails.php' );
include_once( 'jigoshop_query.php' );
include_once( 'jigoshop_actions.php' );
include_once( 'gateways/gateways.class.php' );
include_once( 'gateways/gateway.class.php' );
include_once( 'classes/jigoshop_shipping.class.php' );
include_once( 'shipping/shipping_method.class.php' );
//include_once( 'jigoshop_cron.php' );	/* we may use this at some point, leaving -JAP- */

/**
 * Include admin area
 **/
if (is_admin()) include_once( 'admin/jigoshop-admin.php' );

/**
 * Include all classes, drop-ins and shipping/gateways modules
 */
$include_files = array();

// Classes
$include_files = array_merge($include_files, (array) glob( dirname(__FILE__)."/classes/*.php" ));

// Shipping
$include_files = array_merge($include_files, (array) glob( dirname(__FILE__)."/shipping/*.php" ));

// Payment Gateways
$include_files = array_merge($include_files, (array) glob( dirname(__FILE__)."/gateways/*.php" ));

// Drop-ins (addons, premium features etc)
$include_files = array_merge($include_files, (array) glob( dirname(__FILE__)."/drop-ins/*.php" ));

if ($include_files) :
	foreach($include_files as $filename) :
		if (!empty($filename) && strstr($filename, 'php')) :
			include_once($filename);
		endif;
	endforeach;
endif;

// TODO: as of 0.9.9.2 and prior, Singletons are in use. -JAP-
// These should be looked into being re-factored to allow for easier and more effective Unit Testing.
// Dependency Injection:  http://components.symfony-project.org/dependency-injection/trunk/book/01-Dependency-Injection
$jigoshop 					= jigoshop::instance();
$jigoshop_customer 			= jigoshop_customer::instance();		// Customer class, sorts out session data such as location
$jigoshop_shipping 			= jigoshop_shipping::instance();		// Shipping class. loads and stores shipping methods
$jigoshop_payment_gateways 	= jigoshop_payment_gateways::instance();// Payment gateways class. loads and stores payment methods
$jigoshop_cart 				= jigoshop_cart::instance();			// Cart class, stores the cart contents

// Constants
if (!defined('JIGOSHOP_USE_CSS')) :
	if (get_option('jigoshop_disable_css')=='yes') define('JIGOSHOP_USE_CSS', false);
	else define('JIGOSHOP_USE_CSS', true);
endif;
if (!defined('JIGOSHOP_LOAD_FANCYBOX')) :
	if (get_option('jigoshop_disable_fancybox')=='yes') define('JIGOSHOP_LOAD_FANCYBOX', false);
	else define('JIGOSHOP_LOAD_FANCYBOX', true);
endif;
if (!defined('JIGOSHOP_TEMPLATE_URL')) define('JIGOSHOP_TEMPLATE_URL', 'jigoshop/'); // Trailing slash is important :)

/**
 * IIS compat fix/fallback
 **/
if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
	if (isset($_SERVER['QUERY_STRING'])) { $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING']; }
}

/**
 * Add post thumbnail support to WordPress if needed
 **/
function jigoshop_check_thumbnail_support() {
	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails' );
		add_action( 'init', 'jigoshop_remove_post_type_thumbnail_support' );
	}
}
add_action( 'after_setup_theme', 'jigoshop_check_thumbnail_support', 99 );

function jigoshop_remove_post_type_thumbnail_support() {
	remove_post_type_support( 'post', 'thumbnail' );
	remove_post_type_support( 'page', 'thumbnail' );
}

/**
 * Filters and hooks
 **/
add_action('init', 'jigoshop_init', 0);
add_action('plugins_loaded', 'jigoshop_shipping::method_inits', 1); // Load shipping methods - some may be added by plugins
add_action('plugins_loaded', 'jigoshop_payment_gateways::init', 1); // Load payment methods - some may be added by plugins

if (get_option('jigoshop_force_ssl_checkout')=='yes') add_action( 'wp_head', 'jigoshop_force_ssl');

add_action( 'wp_footer', 'jigoshop_demo_store' );
add_action( 'wp_footer', 'jigoshop_sharethis' );

add_filter('post_thumbnail_html', 'jigoshop_force_ssl_images');
add_filter('widget_text', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_url', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_image_attributes', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_url', 'jigoshop_force_ssl_images');

add_filter( 'loop-shop-query', create_function( '', 'return array("orderby" => "'.get_option('jigoshop_catalog_sort_orderby').'","order" => "'.get_option('jigoshop_catalog_sort_direction').'");' ) );
add_filter( 'loop_shop_columns', create_function( '', 'return '.get_option('jigoshop_catalog_columns').';' ) );
add_filter( 'loop_shop_per_page', create_function( '', 'return '.get_option('jigoshop_catalog_per_page').';' ) );

/**
 * Mail from name/email
 **/
add_filter( 'wp_mail_from', 'jigoshop_mail_from' );
add_filter( 'wp_mail_from_name', 'jigoshop_mail_from_name' );

function jigoshop_mail_from_name( $name ) {
	$name = get_bloginfo('name');
	$name = esc_attr($name);
	return $name;
}
function jigoshop_mail_from( $email ) {
	$email = get_option('admin_email');
	return $email;
}

/**
 * Support for Import/Export
 *
 * WordPress import should work - however, it fails to import custom product attribute taxonomies.
 * This code grabs the file before it is imported and ensures the taxonomies are created.
 **/

function jigoshop_import_start() {

	global $wpdb;

	$id = (int) $_POST['import_id'];
	$file = get_attached_file( $id );

	$parser = new WXR_Parser();
	$import_data = $parser->parse( $file );

	if (isset($import_data['posts'])) :
		$posts = $import_data['posts'];

		if ($posts && sizeof($posts)>0) foreach ($posts as $post) :

			if ($post['post_type']=='product') :

				if ($post['terms'] && sizeof($post['terms'])>0) :

					foreach ($post['terms'] as $term) :

						$domain = $term['domain'];

						if (strstr($domain, 'pa_')) :

							// Make sure it exists!
							if (!taxonomy_exists( $domain )) :

								$nicename = ucfirst(str_replace('pa_', '', $domain));

								// Create the taxonomy
								$wpdb->insert( $wpdb->prefix . "jigoshop_attribute_taxonomies", array( 'attribute_name' => $nicename, 'attribute_type' => 'text' ), array( '%s', '%s' ) );

								// Register the taxonomy now so that the import works!
								register_taxonomy( $domain,
							        array('product'),
							        array(
							            'hierarchical' => true,
							            'labels' => array(
							                    'name' => $nicename,
							                    'singular_name' => $nicename,
							                    'search_items' =>  __( 'Search ', 'jigoshop') . $nicename,
							                    'all_items' => __( 'All ', 'jigoshop') . $nicename,
							                    'parent_item' => __( 'Parent ', 'jigoshop') . $nicename,
							                    'parent_item_colon' => __( 'Parent ', 'jigoshop') . $nicename . ':',
							                    'edit_item' => __( 'Edit ', 'jigoshop') . $nicename,
							                    'update_item' => __( 'Update ', 'jigoshop') . $nicename,
							                    'add_new_item' => __( 'Add New ', 'jigoshop') . $nicename,
							                    'new_item_name' => __( 'New ', 'jigoshop') . $nicename
							            ),
							            'show_ui' => false,
							            'query_var' => true,
							            'rewrite' => array( 'slug' => strtolower(sanitize_title($nicename)), 'with_front' => false, 'hierarchical' => true ),
							        )
							    );

								update_option('jigowatt_update_rewrite_rules', '1');

							endif;

						endif;

					endforeach;

				endif;

			endif;

		endforeach;

	endif;

}

add_action('import_start', 'jigoshop_import_start');



//### Functions #########################################################

/**
 * Set Jigoshop Product Image Sizes for WordPress based on Admin->Jigoshop->Settings->Images
 * @since 0.9.9
 **/
function jigoshop_set_image_sizes(){
	add_image_size( 'shop_tiny', get_option('jigoshop_shop_tiny_w'), get_option('jigoshop_shop_tiny_h'), 'true' );
	add_image_size( 'shop_thumbnail', get_option('jigoshop_shop_thumbnail_w'), get_option('jigoshop_shop_thumbnail_h'), 'true' );
	add_image_size( 'shop_small', get_option('jigoshop_shop_small_w'), get_option('jigoshop_shop_small_h'), 'true' );
	add_image_size( 'shop_large', get_option('jigoshop_shop_large_w'), get_option('jigoshop_shop_large_h'), 'true' );
}

/**
 * Get Jigoshop Product Image Size based on Admin->Jigoshop->Settings->Images
 * @param string $size - one of the 4 defined Jigoshop image sizes
 * @return array - an array containing the width and height of the required size
 * @since 0.9.9
 **/
function jigoshop_get_image_size( $size ) {
	switch ( $size ) :
		case 'shop_tiny':
			$image_size = array( get_option('jigoshop_shop_tiny_w'), get_option('jigoshop_shop_tiny_h') );
			break;
		case 'shop_thumbnail':
			$image_size = array( get_option('jigoshop_shop_thumbnail_w'), get_option('jigoshop_shop_thumbnail_h') );
			break;
		case 'shop_small':
			$image_size = array( get_option('jigoshop_shop_small_w'), get_option('jigoshop_shop_small_h') );
			break;
		case 'shop_large':
			$image_size = array( get_option('jigoshop_shop_large_w'), get_option('jigoshop_shop_large_h') );
			break;
		default:
			$image_size = array( get_option('jigoshop_shop_small_w'), get_option('jigoshop_shop_small_h') );
			break;
	endswitch;

	return $image_size;
}

function jigoshop_init() {

	jigoshop_post_type();

	// Image sizes
	jigoshop_set_image_sizes();

	// Include template functions here so they are pluggable by themes
	include_once( 'jigoshop_template_functions.php' );

	@ob_start();

	add_role('customer', 'Customer', array(
	    'read' => true,
	    'edit_posts' => false,
	    'delete_posts' => false
	));

	$css = file_exists(get_stylesheet_directory() . '/jigoshop/style.css') ? get_stylesheet_directory_uri() . '/jigoshop/style.css' : jigoshop::plugin_url() . '/assets/css/frontend.css';
    if (JIGOSHOP_USE_CSS) wp_register_style('jigoshop_frontend_styles', $css );

    if (is_admin()) :
    	wp_register_style('jigoshop_admin_styles', jigoshop::plugin_url() . '/assets/css/admin.css');
   		wp_register_style('jigoshop_admin_datepicker_styles', jigoshop::plugin_url() . '/assets/css/datepicker.css');
    	wp_enqueue_style('jigoshop_admin_styles');
    	wp_enqueue_style('jigoshop_admin_datepicker_styles');
    else :
    	wp_register_style( 'jqueryui_styles', jigoshop::plugin_url() . '/assets/css/ui.css' );

    	wp_enqueue_style('jigoshop_frontend_styles');
    	wp_enqueue_style('jqueryui_styles');
    
    	if( JIGOSHOP_LOAD_FANCYBOX ) {
   			wp_register_style( 'jigoshop_fancybox_styles', jigoshop::plugin_url() . '/assets/css/fancybox.css' );
    		wp_enqueue_style('jigoshop_fancybox_styles');
    	}
    	
    endif;
}

function jigoshop_admin_scripts() {

	wp_register_script( 'jigoshop_backend', jigoshop::plugin_url() . '/assets/js/jigoshop_backend.js', array('jquery'), '1.0' );
    wp_enqueue_script('jigoshop_backend');

}
add_action('admin_print_scripts', 'jigoshop_admin_scripts');

function jigoshop_frontend_scripts() {

	if( JIGOSHOP_LOAD_FANCYBOX ) {
   		wp_register_script( 'fancybox', jigoshop::plugin_url() . '/assets/js/jquery.fancybox-1.3.4.pack.js', array('jquery'), '1.0' );
		wp_enqueue_script('fancybox');
	}
	
	wp_register_script( 'jigoshop_frontend', jigoshop::plugin_url() . '/assets/js/jigoshop_frontend.js', array('jquery'), '1.0' );
	wp_register_script( 'jigoshop_script', jigoshop::plugin_url() . '/assets/js/script.js', array('jquery'), '1.0' );
	wp_register_script( 'jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js', array('jquery'), '1.0' );

	wp_enqueue_script('jqueryui');
	wp_enqueue_script('jigoshop_frontend');
	wp_enqueue_script('jigoshop_script');

	/* Script.js variables */
	$params = array(
		'currency_symbol' 				=> get_jigoshop_currency_symbol(),
		'countries' 					=> json_encode(jigoshop_countries::$states),
		'select_state_text' 			=> __('Select a state&hellip;', 'jigoshop'),
		'state_text' 					=> __('state', 'jigoshop'),
		'plugin_url' 					=> jigoshop::plugin_url(),
		'ajax_url' 						=> admin_url('admin-ajax.php'),
		'get_variation_nonce' 			=> wp_create_nonce("get-variation"),
		'review_order_url'				=> jigoshop_get_template_file_url('checkout/review_order.php', true),
		'option_guest_checkout'			=> get_option('jigoshop_enable_guest_checkout'),
		'checkout_url'					=> admin_url('admin-ajax.php?action=jigoshop-checkout'),
		'load_fancybox'					=> JIGOSHOP_LOAD_FANCYBOX
	);

	if (isset($_SESSION['min_price'])) :
		$params['min_price'] = $_GET['min_price'];
	endif;
	if (isset($_SESSION['max_price'])) :
		$params['max_price'] = $_GET['max_price'];
	endif;

	if ( is_page(get_option('jigoshop_checkout_page_id')) || is_page(get_option('jigoshop_pay_page_id')) ) :
		$params['is_checkout'] = 1;
	else :
		$params['is_checkout'] = 0;
	endif;

	wp_localize_script( 'jigoshop_script', 'params', $params );

}
add_action('template_redirect', 'jigoshop_frontend_scripts');


/*
	jigoshop_demo_store
	Adds a demo store banner to the site
*/
function jigoshop_demo_store() {

	if (get_option('jigoshop_demo_store')=='yes') :

		echo '<p class="demo_store">'.__('This is a demo store for testing purposes &mdash; no orders shall be fulfilled.', 'jigoshop').'</p>';

	endif;
}

/*
	jigoshop_sharethis
	Adds social sharing code to footer
*/
function jigoshop_sharethis() {
	if (is_single() && get_option('jigoshop_sharethis')) :

		if (is_ssl()) :
			$sharethis = 'https://ws.sharethis.com/button/buttons.js';
		else :
			$sharethis = 'http://w.sharethis.com/button/buttons.js';
		endif;

		echo '<script type="text/javascript">var switchTo5x=true;</script><script type="text/javascript" src="'.$sharethis.'"></script><script type="text/javascript">stLight.options({publisher:"'.get_option('jigoshop_sharethis').'"});</script>';

	endif;
}

/**
 * Evaluates to true only on the Shop page, not Product categories and tags
 * Note:is used to replace is_page( get_option( 'jigoshop_shop_page_id' ) )
 * 
 * @return bool
 * @since 0.9.9
 */
function is_shop() {
	return is_post_type_archive( 'product' );
}

/**
 * Evaluates to true only on the Category Pages
 * 
 * @return bool
 * @since 0.9.9
 */
function is_product_category() {
	return is_tax( 'product_cat' );
}

/**
 * Evaluates to true only on the Tag Pages
 * 
 * @return bool
 * @since 0.9.9
 */
function is_product_tag() {
	return is_tax( 'product_tag' );
}

/**
 * Evaluates to true only on the Single Product Page
 * 
 * @return bool
 * @since 0.9.9
 */
function is_product() {
	return is_singular( array('product') );
}

/**
 * Evaluates to true only on Shop, Product Category, and Product Tag pages
 * 
 * @return bool
 * @since 0.9.9
 */
function is_product_list() {
	$is_list = false;
	$is_list |= is_shop();
	$is_list |= is_product_tag();
	$is_list |= is_product_category();
	return $is_list;
}

// TODO: doesn't reflect All Jigoshop pages
// as of 0.9.9.1, I'll leave this as is for now, it -will- change.  -JAP-
// at this time, it is not used within the codebase, 'is_content_wrapped' contains this functionality
function is_jigoshop() {
	$is_wrapped = false;
	$is_wrapped |= is_product_list();
	$is_wrapped |= is_product();
	return $is_wrapped;
}

/**
 * Evaluates to true only on the Shop, Category, Tag and Single Product Pages
 * 
 * @return bool
 * @since 0.9.9.1
 */
function is_content_wrapped() {
	$is_wrapped = false;
	$is_wrapped |= is_product_list();
	$is_wrapped |= is_product();
	return $is_wrapped;
}

/**
 * Evaluates to true only on the Order Tracking page
 * 
 * @return bool
 * @since 0.9.9.1
 */
function is_order_tracker() {
	return is_page( get_option( 'jigoshop_track_order_page_id' ));
}

/**
 * Evaluates to true only on the Cart page
 * 
 * @return bool
 * @since 0.9.8
 */
function is_cart() {
	return is_page( get_option( 'jigoshop_cart_page_id' ));
}

/**
 * Evaluates to true only on the Checkout page
 * 
 * @return bool
 * @since 0.9.8
 */
function is_checkout() {
	return is_page( get_option( 'jigoshop_checkout_page_id' ));
}

/**
 * Evaluates to true only on the main Account or any sub-account pages
 * 
 * @return bool
 * @since 0.9.9.1
 */
function is_account() {
	$is_account = false;
	$is_account |= is_page( get_option('jigoshop_myaccount_page_id' ) );
	$is_account |= is_page( get_option('jigoshop_edit_address_page_id' ) );
	$is_account |= is_page( get_option('jigoshop_change_password_page_id' ) );
	$is_account |= is_page( get_option('jigoshop_view_order_page_id' ) );
	return $is_account;
}

if (!function_exists('is_ajax')) {
	function is_ajax() {
		if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) return true;
		return false;
	}
}

function jigoshop_force_ssl() {
	if (is_checkout() && !is_ssl()) :
		wp_redirect( str_replace('http:', 'https:', get_permalink(get_option('jigoshop_checkout_page_id'))), 301 );
		exit;
	endif;
}

function jigoshop_force_ssl_images( $content ) {
	if (is_ssl()) :
		if (is_array($content)) :
			$content = array_map('jigoshop_force_ssl_images', $content);
		else :
			$content = str_replace('http:', 'https:', $content);
		endif;
	endif;
	return $content;
}

// http://www.xe.com/symbols.php
function get_jigoshop_currency_symbol() {
	$currency = get_option('jigoshop_currency');
	$currency_symbol = '';
	switch ($currency) :
		case 'AUD' :
		case 'BRL' :
		case 'CAD' :
		case 'HKD' :
		case 'MXN' :
		case 'NZD' :
		case 'SGD' :
		case 'USD' : $currency_symbol = '&#36;'; break; /* Dollar Sign */
		
		case 'EUR' : $currency_symbol = '&euro;'; break; /* European Euro */
		case 'IDR' : $currency_symbol = '&#82;&#112;'; break; /* Indonesia Rupiah */
		case 'INR' : $currency_symbol = '&#8360;'; break; /* India Rupee */
		case 'HRK' : $currency_symbol = '&#107;&#110;'; break; /* Croatia Kuna */
		case 'HUF' : $currency_symbol = '&#70;&#116;'; break; /* Hungary Forint */
		case 'JPY' : $currency_symbol = '&yen;'; break; /* Japanese Yen */
		case 'RUB' : $currency_symbol = '&#1088;&#1091;&#1073;'; break; /* Russia Ruble */
		case 'TRY' : $currency_symbol = '&#8356;'; break; /* Turkey Lira */

		case 'CHF' :
		case 'CZK' :
		case 'DKK' :
		case 'ILS' :
		case 'MYR' :
		case 'NOK' :
		case 'PHP' :
		case 'PLN' :
		case 'SEK' :
		case 'THB' :
		case 'TWD' : $currency_symbol = $currency; break;

		case 'GBP' :
		default    : $currency_symbol = '&pound;'; break;
	endswitch;
	return apply_filters('jigoshop_currency_symbol', $currency_symbol, $currency);
}

function jigoshop_price( $price, $args = array() ) {

	extract(shortcode_atts(array(
		'ex_tax_label' 	=> '0'
	), $args));

	$return = '';
	$num_decimals = (int) get_option('jigoshop_price_num_decimals');
	$currency_pos = get_option('jigoshop_currency_pos');
	$currency_symbol = get_jigoshop_currency_symbol();
	$price = str_replace(array(get_option('jigoshop_price_thousand_sep'),get_option('jigoshop_price_decimal_sep')),array('','.'),$price);
	$price = number_format( (double) $price, $num_decimals, get_option('jigoshop_price_decimal_sep'), get_option('jigoshop_price_thousand_sep') );

	switch ($currency_pos) :
		case 'left' :
			$return = $currency_symbol . $price;
		break;
		case 'right' :
			$return = $price . $currency_symbol;
		break;
		case 'left_space' :
			$return = $currency_symbol . ' ' . $price;
		break;
		case 'right_space' :
			$return = $price . ' ' . $currency_symbol;
		break;
	endswitch;

	if ($ex_tax_label && get_option('jigoshop_calc_taxes')=='yes') $return .= __(' <small>(ex. tax)</small>', 'jigoshop');

	return $return;
}

/** Show variation info if set */
function jigoshop_get_formatted_variation( $variation = '', $flat = false ) {
	if ($variation && is_array($variation)) :

		$return = '';

		if (!$flat) :
			$return = '<dl class="variation">';
		endif;

		$varation_list = array();

		foreach ($variation as $name => $value) :

			$name = str_replace('tax_', '', $name);
			
			if ( taxonomy_exists( 'pa_'.$name )) :
				$terms = get_terms( 'pa_'.$name, array( 'orderby' => 'slug', 'hide_empty' => '0' ) );
				foreach ( $terms as $term ) :
					if ( $term->slug == $value ) $value = $term->name;
				endforeach;
				$name = get_taxonomy( 'pa_'.$name )->labels->name;
			endif;

			if ($flat) :
				$varation_list[] = $name.': '.$value;
			else :
				$varation_list[] = '<dt>'.$name.':</dt><dd>'.$value.'</dd>';
			endif;

		endforeach;

		if ($flat) :
			$return .= implode(', ', $varation_list);
		else :
			$return .= implode('', $varation_list);
		endif;

		if (!$flat) :
			$return .= '</dl>';
		endif;

		return $return;

	endif;
}

function jigoshop_let_to_num($v) {
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);
    switch(strtoupper($l)){
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
        break;
    }
    return $ret;
}

function jigowatt_clean( $var ) {
	return strip_tags(stripslashes(trim($var)));
}

global $jigoshop_body_classes;

function jigoshop_page_body_classes() {

	global $jigoshop_body_classes;

	$jigoshop_body_classes = (array) $jigoshop_body_classes;

	if ( is_order_tracker() ) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-tracker' ) );

	if ( is_checkout() ) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-checkout' ) );

	if ( is_cart() ) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-cart' ) );

	if ( is_page(get_option('jigoshop_thanks_page_id'))) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-thanks' ) );

	if ( is_page(get_option('jigoshop_pay_page_id'))) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-pay' ) );

	if ( is_account() ) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-myaccount' ) );

}
add_action('wp_head', 'jigoshop_page_body_classes');

function jigoshop_add_body_class( $class = array() ) {

	global $jigoshop_body_classes;

	$jigoshop_body_classes = (array) $jigoshop_body_classes;

	$jigoshop_body_classes = array_unique( array_merge( $class, $jigoshop_body_classes ));
}

function jigoshop_body_class($classes) {

	global $jigoshop_body_classes;

	$jigoshop_body_classes = (array) $jigoshop_body_classes;

	$classes = array_unique( array_merge( $classes, $jigoshop_body_classes ));

	return $classes;
}
add_filter('body_class','jigoshop_body_class');

//### Extra Review Field in comments #########################################################

function jigoshop_add_comment_rating($comment_id) {
	if ( isset($_POST['rating']) ) :
		if (!$_POST['rating'] || $_POST['rating'] > 5 || $_POST['rating'] < 0) $_POST['rating'] = 5;
		add_comment_meta( $comment_id, 'rating', $_POST['rating'], true );
	endif;
}
add_action( 'comment_post', 'jigoshop_add_comment_rating', 1 );

function jigoshop_check_comment_rating($comment_data) {
	// If posting a comment (not trackback etc) and not logged in
	if ( isset($_POST['rating']) && !jigoshop::verify_nonce('comment_rating') )
		wp_die( __('You have taken too long. Please go back and refresh the page.', 'jigoshop') );

	elseif ( isset($_POST['rating']) && empty($_POST['rating']) && $comment_data['comment_type']== '' ) {
		wp_die( __('Please rate the product.',"jigowatt") );
		exit;
	}
	return $comment_data;
}
add_filter('preprocess_comment', 'jigoshop_check_comment_rating', 0);

//### Comments #########################################################

function jigoshop_comments($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; global $post; ?>

	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
		<div id="comment-<?php comment_ID(); ?>" class="comment_container">

  			<?php echo get_avatar( $comment, $size='60' ); ?>

			<div class="comment-text">
				<div class="star-rating" title="<?php echo get_comment_meta( $comment->comment_ID, 'rating', true ); ?>">
					<span style="width:<?php echo get_comment_meta( $comment->comment_ID, 'rating', true )*16; ?>px"><?php echo get_comment_meta( $comment->comment_ID, 'rating', true ); ?> <?php _e('out of 5', 'jigoshop'); ?></span>
				</div>
				<?php if ($comment->comment_approved == '0') : ?>
					<p class="meta"><em><?php _e('Your comment is awaiting approval','jigoshop'); ?></em></p>
				<?php else : ?>
					<p class="meta">
						<?php _e('Rating by','jigoshop'); ?> <strong class="reviewer vcard"><span class="fn"><?php comment_author(); ?></span></strong> <?php _e('on','jigoshop'); ?> <?php echo get_comment_date('M jS Y'); ?>:
					</p>
				<?php endif; ?>
  				<div class="description"><?php comment_text(); ?></div>
  				<div class="clear"></div>
  			</div>
			<div class="clear"></div>
		</div>
	<?php
}

//### Exclude order comments from front end #########################################################

function jigoshop_exclude_order_comments( $clauses ) {

	global $wpdb;

	$clauses['join'] = "
		LEFT JOIN $wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID
	";

	if ($clauses['where']) $clauses['where'] .= ' AND ';

	$clauses['where'] .= "
		$wpdb->posts.post_type NOT IN ('shop_order')
	";

	return $clauses;

}
if (!is_admin()) add_filter('comments_clauses', 'jigoshop_exclude_order_comments');

### Update data ################################################################################
function rename_attributes() {
	if( get_option('run_once', true) ) {
	
		// Update dataset to be prefixed with pa_ instead
		global $wpdb;
		
		$q = $wpdb->get_results("SELECT * 
			FROM $wpdb->term_taxonomy
			WHERE taxonomy LIKE 'product_attribute_%'
		");
		
		foreach($q as $item) {
			$taxonomy = str_replace('product_attribute_', 'pa_', $item->taxonomy);
			
			$wpdb->update(
				$wpdb->term_taxonomy,
				array('taxonomy' => $taxonomy),
				array('term_taxonomy_id' => $item->term_taxonomy_id)
			);
		}
		
		add_option('run_once', 0);
	}
}
add_action('init', 'rename_attributes');