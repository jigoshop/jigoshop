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
 * Plugin Name:        Jigoshop - WordPress eCommerce
 * Plugin URI:         http://jigoshop.com
 * Description:        An eCommerce plugin for WordPress.
 * Author:             Jigowatt
 * Author URI:         http://jigowatt.co.uk
 *
 * Version:            1.0
 * Requires at least:  3.1
 * Tested up to:       3.3.1
 *
 * @package            Jigoshop
 * @category           Core
 * @author             Jigowatt
 * @copyright          Copyright (c) 2011 Jigowatt Ltd.
 * @license            http://jigoshop.com/license/commercial-edition
 */

if (!defined("JIGOSHOP_VERSION")) define("JIGOSHOP_VERSION", 1202010);
if (!defined("PHP_EOL")) define("PHP_EOL", "\r\n");

load_plugin_textdomain('jigoshop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');

// Load administration & check if we need to install
if ( is_admin() ) {
	include_once( 'admin/jigoshop-admin.php' );
	register_activation_hook( __FILE__, 'install_jigoshop' );
}
/**
 * Include core files and classes
 **/
include_once( 'classes/abstract/jigoshop_base.class.php' );
include_once( 'classes/abstract/jigoshop_singleton.php' );
include_once( 'classes/jigoshop_sanitize.class.php' );
include_once( 'classes/jigoshop_validation.class.php' );
include_once( 'jigoshop_taxonomy.php' );

include_once( 'classes/jigoshop_countries.class.php' );
include_once( 'classes/jigoshop_customer.class.php' );
include_once( 'classes/jigoshop_product.class.php' );
include_once( 'classes/jigoshop_product_variation.class.php' );
include_once( 'classes/jigoshop_order.class.php' );
include_once( 'classes/jigoshop_orders.class.php' );
include_once( 'classes/jigoshop_tax.class.php' );
include_once( 'classes/jigoshop_shipping.class.php' );
include_once( 'classes/jigoshop_coupons.class.php' );
include_once( 'classes/jigoshop_session.class.php' );

include_once( 'gateways/gateways.class.php' );
include_once( 'gateways/gateway.class.php' );
include_once( 'gateways/bank_transfer.php' );
include_once( 'gateways/cheque.php' );
include_once( 'gateways/dibs.php' );
include_once( 'gateways/paypal.php' );
include_once( 'gateways/skrill.php' );

include_once( 'shipping/shipping_method.class.php' );
include_once( 'shipping/jigoshop_calculable_shipping.php' );
include_once( 'shipping/flat_rate.php' );
include_once( 'shipping/free_shipping.php' );

include_once( 'classes/jigoshop_query.class.php' );
include_once( 'classes/jigoshop.class.php' );
include_once( 'classes/jigoshop_cart.class.php' );
include_once( 'classes/jigoshop_checkout.class.php' );

include_once( 'widgets/init.php' );

include_once( 'jigoshop_shortcodes.php' );
include_once( 'jigoshop_templates.php' );
include_once( 'jigoshop_template_actions.php' );
include_once( 'jigoshop_emails.php' );
include_once( 'jigoshop_actions.php' );
//include_once( 'jigoshop_cron.php' );	/* we may use this at some point, leaving -JAP- */

// Constants
if (!defined('JIGOSHOP_USE_CSS')) :
	if (get_option('jigoshop_disable_css')=='yes') define('JIGOSHOP_USE_CSS', false);
	else define('JIGOSHOP_USE_CSS', true);
endif;
if (!defined('JIGOSHOP_LOAD_FANCYBOX')) :
	if (get_option('jigoshop_disable_fancybox')=='yes') define('JIGOSHOP_LOAD_FANCYBOX', false);
	else define('JIGOSHOP_LOAD_FANCYBOX', true);
endif;
if ( !defined('JIGOSHOP_TEMPLATE_URL') ) define('JIGOSHOP_TEMPLATE_URL', 'jigoshop/');

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
		remove_post_type_support( 'post', 'thumbnail' );
		remove_post_type_support( 'page', 'thumbnail' );
	} else {
		add_post_type_support( 'product', 'thumbnail' );
	}
}
add_action( 'after_setup_theme', 'jigoshop_check_thumbnail_support', 99 );

/**
 * Mail from name/email
 **/
function jigoshop_mail_from_name( $name ) {
	$name = get_bloginfo('name');
	$name = esc_attr($name);
	return $name;
}
add_filter( 'wp_mail_from_name', 'jigoshop_mail_from_name' );

function jigoshop_mail_from( $email ) {
	$email = get_option('jigoshop_email');
	return $email;
}
add_filter( 'wp_mail_from', 'jigoshop_mail_from' );


//### Functions #########################################################

/**
 * Set Jigoshop Product Image Sizes for WordPress based on Admin->Jigoshop->Settings->Images
 * @since 0.9.9
 **/
function jigoshop_set_image_sizes(){
	add_image_size( 'admin_product_list', 32, 32, 'true' );
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

	if ( is_array( $size ) )
		return $size;

	switch ( $size ) :
		case 'admin_product_list':
			$image_size = array( 32, 32 );
			break;
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
	
	/* ensure nothing is output to the browser prior to this (other than headers) */
	ob_start();
	
	jigoshop_session::instance()->test = 'val';
	
    $array = array(0 => "3.15");
    
    foreach ($array as $a) :
        $an = explode(':', $a);
    endforeach;
	jigoshop_post_type();	/* register taxonomies */
	
	// add Singletons here so that the taxonomies are loaded before calling them.
	$jigoshop 					= jigoshop::instance();
	$jigoshop_customer 			= jigoshop_customer::instance();		// Customer class, sorts session data such as location
	$jigoshop_shipping 			= jigoshop_shipping::instance();		// Shipping class. loads shipping methods
	$jigoshop_payment_gateways 	= jigoshop_payment_gateways::instance();// Payment gateways class. loads payment methods
	$jigoshop_cart 				= jigoshop_cart::instance();			// Cart class, stores the cart contents

//	if ( ! is_admin() ) $jigoshop_query = &new jigoshop_catalog_query();
	if ( ! is_admin() ) $jigoshop_query = jigoshop_catalog_query::instance();
	
	// Image sizes
	jigoshop_set_image_sizes();

	// Include template functions here so they are pluggable by themes
	include_once( 'jigoshop_template_functions.php' );

	add_role('customer', 'Customer', array(
	    'read' => true,
	    'edit_posts' => false,
	    'delete_posts' => false
	));

	$css = file_exists(get_stylesheet_directory() . '/jigoshop/style.css') ? get_stylesheet_directory_uri() . '/jigoshop/style.css' : jigoshop::assets_url() . '/assets/css/frontend.css';
    if (JIGOSHOP_USE_CSS) wp_register_style('jigoshop_frontend_styles', $css );

    if ( !is_admin()) :
    	wp_register_style( 'jqueryui_styles', jigoshop::assets_url() . '/assets/css/ui.css' );

    	wp_enqueue_style('jigoshop_frontend_styles');
    	wp_enqueue_style('jqueryui_styles');
    
    	if( JIGOSHOP_LOAD_FANCYBOX ) {
   			wp_register_style( 'jigoshop_fancybox_styles', jigoshop::assets_url() . '/assets/css/fancybox.css' );
    		wp_enqueue_style('jigoshop_fancybox_styles');
    	}
    	
    endif;
}
add_action('init', 'jigoshop_init', 0);

add_action( 'admin_enqueue_scripts', 'jigoshop_admin_styles' );
function jigoshop_admin_styles() {
	wp_register_style('jigoshop_admin_styles', jigoshop::assets_url() . '/assets/css/admin.css');
    wp_enqueue_style('jigoshop_admin_styles');
   	wp_register_style('jquery-ui-jigoshop-styles', jigoshop::assets_url() . '/assets/css/jquery-ui-1.8.16.jigoshop.css');
    wp_enqueue_style('jquery-ui-jigoshop-styles');
}

function jigoshop_admin_scripts() {

    wp_register_script('jquery-ui-datepicker', jigoshop::assets_url() . '/assets/js/jquery-ui-datepicker-1.8.16.min.js', array( 'jquery' ), '1.8.16', true );
    wp_enqueue_script('jquery-ui-datepicker');
	wp_register_script( 'jigoshop_backend', jigoshop::assets_url() . '/assets/js/jigoshop_backend.js', array('jquery'), '1.0' );
    wp_enqueue_script('jigoshop_backend');

}
add_action('admin_print_scripts', 'jigoshop_admin_scripts');

function jigoshop_frontend_scripts() {

	if( JIGOSHOP_LOAD_FANCYBOX ) {
   		wp_register_script( 'fancybox', jigoshop::assets_url() . '/assets/js/jquery.fancybox-1.3.4.pack.js', array('jquery'), '1.0' );
		wp_enqueue_script('fancybox');
	}
	
	wp_register_script( 'jigoshop_frontend', jigoshop::assets_url() . '/assets/js/jigoshop_frontend.js', array('jquery'), '1.0' );
	wp_register_script( 'jigoshop_script', jigoshop::assets_url() . '/assets/js/script.js', array('jquery'), '1.0' );
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
		'assets_url' 					=> jigoshop::assets_url(),
		'ajax_url' 						=> (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'),
		'get_variation_nonce' 			=> wp_create_nonce("get-variation"),
		'update_order_review_nonce' 	=> wp_create_nonce("update-order-review"),
		'option_guest_checkout'			=> get_option('jigoshop_enable_guest_checkout'),
		'checkout_url'					=> admin_url('admin-ajax.php?action=jigoshop-checkout'),
		'load_fancybox'					=> JIGOSHOP_LOAD_FANCYBOX
	);

	if (isset( jigoshop_session::instance()->min_price )) :
		$params['min_price'] = $_GET['min_price'];
	endif;
	if (isset( jigoshop_session::instance()->max_price )) :
		$params['max_price'] = $_GET['max_price'];
	endif;

	if ( is_page(get_option('jigoshop_checkout_page_id')) || is_page(get_option('jigoshop_pay_page_id')) ) :
		$params['is_checkout'] = 1;
	else :
		$params['is_checkout'] = 0;
	endif;
	
	$params = apply_filters('jigoshop_params', $params);

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
add_action( 'wp_footer', 'jigoshop_demo_store' );

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
add_action( 'wp_footer', 'jigoshop_sharethis' );

/**
 * Evaluates to true only on the Shop page, not Product categories and tags
 * Note:is used to replace is_page( get_option( 'jigoshop_shop_page_id' ) )
 * 
 * @return bool
 * @since 0.9.9
 */
function is_shop() {
	return is_post_type_archive( 'product' ) | is_page( get_option('jigoshop_shop_page_id') );
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

/**
 * Evaluates to true for all Jigoshop pages
 * 
 * @return bool
 * @since 0.9.9
 */
function is_jigoshop() {
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
 * Evaluates to true only on the Checkout or Pay pages
 * 
 * @return bool
 * @since 0.9.8
 */
function is_checkout() {
	return is_page( get_option('jigoshop_checkout_page_id')) | is_page( get_option('jigoshop_pay_page_id'));
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
		wp_safe_redirect( str_replace('http:', 'https:', get_permalink(get_option('jigoshop_checkout_page_id'))), 301 );
		exit;
	endif;
}
if (!is_admin() && get_option('jigoshop_force_ssl_checkout')=='yes') add_action( 'wp', 'jigoshop_force_ssl');

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
add_filter('post_thumbnail_html', 'jigoshop_force_ssl_images');
add_filter('widget_text', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_url', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_image_attributes', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_url', 'jigoshop_force_ssl_images');

function jigoshop_force_ssl_urls( $url ) {
	if (is_ssl()) :
		$url = str_replace('http:', 'https:', $url);
	endif;
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

// http://www.xe.com/symbols.php
function get_jigoshop_currency_symbol() {
	$currency = get_option('jigoshop_currency');
	$currency_symbol = '';
	switch ($currency) :
		case 'AED' : $currency_symbol = '&#1583;&#46;&#1573;'; break;
		case 'AUD' : $currency_symbol = '&#36;'; break;
		case 'BRL' : $currency_symbol = '&#82;&#36;'; break;
		case 'CAD' : $currency_symbol = '&#36;'; break;
		case 'CHF' : $currency_symbol = '&#8355;'; break;
		case 'CNY' : $currency_symbol = '&#165;'; break;
		case 'CZK' : $currency_symbol = '&#75;&#269;'; break;
		case 'DKK' : $currency_symbol = 'kr'; break;
		case 'EUR' : $currency_symbol = '&euro;'; break;
		case 'GBP' : $currency_symbol = '&pound;'; break;
		case 'HKD' : $currency_symbol = '&#36;'; break;
		case 'HRK' : $currency_symbol = '&#107;&#110;'; break;
		case 'HUF' : $currency_symbol = '&#70;&#116;'; break;
		case 'IDR' : $currency_symbol = '&#82;&#112;'; break;
		case 'ILS' : $currency_symbol = '&#8362;'; break;
		case 'INR' : $currency_symbol = '&#8360;'; break;
		case 'JPY' : $currency_symbol = '&yen;'; break;
		case 'MXN' : $currency_symbol = '&#162;'; break;
		case 'MYR' : $currency_symbol = 'RM'; break;
		case 'NGN' : $currency_symbol = '&#8358;'; break;
		case 'NOK' : $currency_symbol = 'kr'; break;
		case 'NZD' : $currency_symbol = '&#36;'; break;
		case 'PHP' : $currency_symbol = '&#8369;'; break;
		case 'PLN' : $currency_symbol = '&#122;&#322;'; break;
		case 'RON' : $currency_symbol = '&#108;&#101;&#105;'; break;
		case 'RUB' : $currency_symbol = '&#1088;&#1091;&#1073;'; break;
		case 'SEK' : $currency_symbol = 'kr'; break;
		case 'SGD' : $currency_symbol = '&#36;'; break;
		case 'THB' : $currency_symbol = '&#3647;'; break;
		case 'TRY' : $currency_symbol = '&#8356;'; break;
		case 'TWD' : $currency_symbol = '&#78;&#84;&#36;'; break;
		case 'USD' : $currency_symbol = '&#36;'; break;
		case 'ZAR' : $currency_symbol = 'R'; break;
		default    : $currency_symbol = '&pound;'; break;
	endswitch;
	return apply_filters('jigoshop_currency_symbol', $currency_symbol, $currency);
}

function jigoshop_price( $price, $args = array() ) {

	extract(shortcode_atts(array(
		'ex_tax_label' 	=> '0',
        'with_currency' => true
	), $args));

	$return = '';
	$price = number_format(
		(double) $price, 
		(int) get_option('jigoshop_price_num_decimals'), 
		get_option('jigoshop_price_decimal_sep'), 
		get_option('jigoshop_price_thousand_sep')
	);
    
    $return = $price;
    
    if ($with_currency) :
        
        $currency_pos = get_option('jigoshop_currency_pos');
        $currency_symbol = get_jigoshop_currency_symbol();
        $currency_unit = get_option('jigoshop_currency');

        switch ($currency_pos) :
            case 'left' :
                $return = $currency_symbol . $price;
            break;
            case 'right' :
                $return = $price . $currency_symbol;
            break;
            case 'both' :
                $return = $currency_symbol . $price . $currency_unit;
            break;
            case 'left_space' :
                $return = $currency_symbol . ' ' . $price;
            break;
            case 'right_space' :
                $return = $price . ' ' . $currency_symbol;
            break;
            case 'both_space' :
                $return = $currency_symbol . ' ' . $price . ' ' . $currency_unit;
            break;
        endswitch;
    
        // only show (ex. tax) if we are going to show the price with currency as well. Otherwise we just want the formatted price
        if ($ex_tax_label && get_option('jigoshop_calc_taxes')=='yes') $return .= __(' <small>(ex. tax)</small>', 'jigoshop');
    endif;
    
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

// Remove pingbacks/trackbacks from Comments Feed
// betterwp.net/wordpress-tips/remove-pingbackstrackbacks-from-comments-feed/
add_filter('request', 'jigoshop_filter_request');

function jigoshop_filter_request($qv)
{
	if (isset($qv['feed']) && !empty($qv['withcomments']))
	{
		add_filter('comment_feed_where', 'jigoshop_comment_feed_where');
	}
	return $qv;
}

function jigoshop_comment_feed_where($cwhere)
{
	$cwhere .= " AND comment_type != 'jigoshop' ";
	return $cwhere;
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

function jigoshop_hide_out_of_stock_product( $item_id ) {
	update_post_meta( $item_id, 'visibility', 'hidden' );
}
if ( get_option( 'jigoshop_hide_no_stock_product' )  == 'yes' ) :
	add_action( 'jigoshop_no_stock_notification', 'jigoshop_hide_out_of_stock_product' );
endif;

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

								$nicename = sanitize_title(str_replace('pa_', '', $domain));

								$exists_in_db = $wpdb->get_var("SELECT attribute_id FROM ".$wpdb->prefix . "jigoshop_attribute_taxonomies WHERE attribute_name = '".$nicename."';");

								// Create the taxonomy
								if (!$exists_in_db) :
									$wpdb->insert( $wpdb->prefix . "jigoshop_attribute_taxonomies", array( 'attribute_name' => $nicename, 'attribute_type' => 'select' ), array( '%s', '%s' ) );
								endif;

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
							            'rewrite' => array( 'slug' => sanitize_title($nicename), 'with_front' => false, 'hierarchical' => true ),
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
