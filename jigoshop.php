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
 *
 * Plugin Name:         Jigoshop
 * Plugin URI:          http://jigoshop.com/
 * Description:         Jigoshop, a WordPress eCommerce plugin that works.
 * Author:              Jigoshop
 * Author URI:          http://jigoshop.com
 *
 * Version:             1.6.5
 * Requires at least:   3.5
 * Tested up to:        3.5.1
 *
 * Text Domain:         jigoshop
 * Domain Path:         /languages/
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

if ( !defined( "JIGOSHOP_VERSION" )) define( "JIGOSHOP_VERSION", 1303180) ;
if ( !defined( "JIGOSHOP_OPTIONS" )) define( "JIGOSHOP_OPTIONS", 'jigoshop_options' );
if ( !defined( 'JIGOSHOP_TEMPLATE_URL' ) ) define( 'JIGOSHOP_TEMPLATE_URL', 'jigoshop/' );
if ( !defined( "PHP_EOL" )) define( "PHP_EOL", "\r\n" );

/**
 * Include core files and classes
 **/
include_once( 'classes/abstract/jigoshop_base.class.php' );
include_once( 'classes/abstract/jigoshop_singleton.class.php' );
include_once( 'classes/jigoshop_options.class.php' );
include_once( 'classes/jigoshop_session.class.php' );

include_once( 'classes/jigoshop_sanitize.class.php' );
include_once( 'classes/jigoshop_validation.class.php' );
include_once( 'classes/jigoshop_forms.class.php' );
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

include_once( 'gateways/gateways.class.php' );
include_once( 'gateways/gateway.class.php' );
include_once( 'gateways/bank_transfer.php' );
include_once( 'gateways/cheque.php' );
include_once( 'gateways/cod.php' );
include_once( 'gateways/paypal.php' );
include_once( 'gateways/skrill.php' );

include_once( 'shipping/shipping_method.class.php' );
include_once( 'shipping/jigoshop_calculable_shipping.php' );
include_once( 'shipping/flat_rate.php' );
include_once( 'shipping/free_shipping.php' );
include_once( 'shipping/local_pickup.php' );

include_once( 'classes/jigoshop_query.class.php' );
include_once( 'classes/jigoshop.class.php' );
include_once( 'classes/jigoshop_cart.class.php' );
include_once( 'classes/jigoshop_checkout.class.php' );
include_once( 'classes/jigoshop_cron.class.php' );

include_once( 'shortcodes/init.php' );
include_once( 'widgets/init.php' );

include_once( 'jigoshop_templates.php' );
include_once( 'jigoshop_template_actions.php' );
include_once( 'jigoshop_emails.php' );
include_once( 'jigoshop_actions.php' );


/**
 * IIS compat fix/fallback
 **/
if ( ! isset( $_SERVER['REQUEST_URI'] )) {
	$_SERVER['REQUEST_URI'] = substr( $_SERVER['PHP_SELF'], 1 );
	if ( isset( $_SERVER['QUERY_STRING'] )) { $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING']; }
}


// Load administration & check if we need to install
if ( is_admin() ) {
	include_once( 'admin/jigoshop-admin.php' );
	register_activation_hook( __FILE__, 'install_jigoshop' );
}


/**
 * Jigoshop Inits
 **/
add_action( 'init', 'jigoshop_init', 0 );
function jigoshop_init() {

	/* ensure nothing is output to the browser prior to this (other than headers) */
	ob_start();
	
	// http://www.geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
	// this means that all Jigoshop extensions, shipping modules and gateways must load their text domains on the 'init' action hook
	// 
	// Override default translations with custom .mo's found in wp-content/languages/jigoshop first.
	load_textdomain( 'jigoshop', WP_LANG_DIR.'/jigoshop/jigoshop-'.get_locale().'.mo' );
	load_plugin_textdomain( 'jigoshop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// instantiate options -after- loading text domains
    $jigoshop_options = Jigoshop_Base::get_options();

	jigoshop_post_type();                       // register taxonomies

	new jigoshop_cron();                        // -after- text domains and Options instantiation allows settings translations

	jigoshop_set_image_sizes();                 // called -after- our Options are loaded

	// add Singletons here so that the taxonomies are loaded before calling them.
	jigoshop_session::instance();               // Start sessions if they aren't already
	jigoshop::instance();                       // Utility functions, uses sessions
	jigoshop_customer::instance();              // Customer class, sorts session data such as location
	
	// Jigoshop will instantiate gateways and shipping methods on this same 'init' action hook
	// with a very low priority to ensure text domains are loaded first prior to installing any external options
	jigoshop_shipping::instance();              // Shipping class. loads shipping methods
	jigoshop_payment_gateways::instance();      // Payment gateways class. loads payment methods

	jigoshop_cart::instance();                  // Cart class, uses sessions

	if ( ! is_admin()) {

		/* Catalog Filters */
		add_filter( 'loop-shop-query', create_function( '', 'return array("orderby" => "' . $jigoshop_options->get_option('jigoshop_catalog_sort_orderby') . '","order" => "' . $jigoshop_options->get_option('jigoshop_catalog_sort_direction') . '");' ) );
		add_filter( 'loop_shop_columns' , create_function( '', 'return ' . $jigoshop_options->get_option('jigoshop_catalog_columns') . ';' ) );
		add_filter( 'loop_shop_per_page', create_function( '', 'return ' . $jigoshop_options->get_option('jigoshop_catalog_per_page') . ';' ) );

		jigoshop_catalog_query::instance();		// front end queries class

	}

	jigoshop_roles_init();

}

/**
 * Include template functions here with a low priority so they are pluggable by themes
 **/
add_action( 'init', 'jigoshop_load_template_functions', 999 );
function jigoshop_load_template_functions() {
	include_once( 'jigoshop_template_functions.php' );
}


function jigoshop_get_core_capabilities() {
	$capabilities = array();

	$capabilities['core'] = array(
		'manage_jigoshop',
		'view_jigoshop_reports',
		'manage_jigoshop_orders',
		'manage_jigoshop_coupons',
		'manage_jigoshop_products'
	);

	$capability_types = array( 'product', 'shop_order', 'shop_coupon' );

	foreach( $capability_types as $capability_type ) {

		$capabilities[ $capability_type ] = array(

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

function jigoshop_roles_init() {
	global $wp_roles;

	if ( class_exists('WP_Roles') )
		if ( ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();

	if ( is_object( $wp_roles ) ) {

		// Customer role
		add_role( 'customer', __('Customer', 'jigoshop'), array(
			'read'						=> true,
			'edit_posts'				=> false,
			'delete_posts'				=> false
		) );

		// Shop manager role
		add_role( 'shop_manager', __('Shop Manager', 'jigoshop'), array(
			'read'						=> true,
			'read_private_pages'		=> true,
			'read_private_posts'		=> true,
			'edit_users'				=> true,
			'edit_posts' 				=> true,
			'edit_pages' 				=> true,
			'edit_published_posts'		=> true,
			'edit_published_pages'		=> true,
			'edit_private_pages'		=> true,
			'edit_private_posts'		=> true,
			'edit_others_posts' 		=> true,
			'edit_others_pages' 		=> true,
			'publish_posts' 			=> true,
			'publish_pages'				=> true,
			'delete_posts' 				=> true,
			'delete_pages' 				=> true,
			'delete_private_pages'		=> true,
			'delete_private_posts'		=> true,
			'delete_published_pages'	=> true,
			'delete_published_posts'	=> true,
			'delete_others_posts' 		=> true,
			'delete_others_pages' 		=> true,
			'manage_categories' 		=> true,
			'manage_links'				=> true,
			'moderate_comments'			=> true,
			'unfiltered_html'			=> true,
			'upload_files'				=> true,
			'export'					=> true,
			'import'					=> true,
		) );
		
		$capabilities = jigoshop_get_core_capabilities();

		foreach( $capabilities as $cap_group ) {
			foreach( $cap_group as $cap ) {
				$wp_roles->add_cap( 'administrator', $cap );
				$wp_roles->add_cap( 'shop_manager', $cap );
			}
		}
		
	}
}

/**
 * Jigoshop Frontend Styles and Scripts
 **/
add_action( 'template_redirect', 'jigoshop_frontend_scripts' );
function jigoshop_frontend_scripts() {

	if ( ! is_jigoshop() && is_admin() ) return false;

    $jigoshop_options = Jigoshop_Base::get_options();

	/**
	 * Frontend Styles
	 *
	 * For Jigoshop 1.3 or better there are 2 CSS related options
	 * The ususal 'jigoshop_disable_css' must be off to use -any- Jigoshop CSS
	 *      otherwise the theme is expected to provide -all- CSS located wherever it likes, loaded by the theme
	 * A user can also copy both frontend.less and frontend.css to a 'jigoshop' folder in the theme folder and
	 *      rename them to style.less and style.css and compile changes with SimpLESS
	 *      http://wearekiss.com/simpless
	 *      If Jigoshop finds this file, it will load -only- that file, or if not found will default to our frontend.css
	 * A user can also with the second option 'jigoshop_frontend_with_theme_css' enabled, load -both- the default frontend.css
	 *      -and- any extra bits found in the same 'theme/jigoshop/style.css'
	 *      This allows only a few modifications to be added in after the default frontend.css
	 *      It will also allow for easier additions to frontend.css that get missed if themers don't upgrade their version.
	 * With these 2 options, users should either provide the complete frontend.css (style.css in the theme jigoshop folder)
	 *      or just the few changes again in the style.css in the theme jigoshop folder and set the 2nd option accordingly
	 */
	$frontend_css = jigoshop::assets_url() . '/assets/css/frontend.css';
	$theme_css = file_exists( get_stylesheet_directory() . '/jigoshop/style.css')
		? get_stylesheet_directory_uri() . '/jigoshop/style.css'
		: jigoshop::assets_url() . '/assets/css/frontend.css';

	if ( $jigoshop_options->get_option( 'jigoshop_disable_css' ) == 'no' ) {
		if ( $jigoshop_options->get_option( 'jigoshop_frontend_with_theme_css' ) == 'yes' ) {
			wp_enqueue_style( 'jigoshop_frontend_styles', $frontend_css );
		}
		wp_enqueue_style( 'jigoshop_theme_styles', $theme_css );
	}

	 if ( $jigoshop_options->get_option( 'jigoshop_disable_fancybox' ) == 'no' ) {
		wp_enqueue_style( 'jigoshop_fancybox_styles', jigoshop::assets_url() . '/assets/css/fancybox.css' );
		wp_enqueue_script( 'fancybox', jigoshop::assets_url().'/assets/js/jquery.fancybox-1.3.4.pack.js', array('jquery'));
	}

	wp_enqueue_style( 'jqueryui_styles', jigoshop::assets_url().'/assets/css/ui.css' );
	wp_enqueue_script( 'jqueryui', jigoshop::assets_url().'/assets/js/jquery-ui-1.9.2.min.js', array('jquery'), '1.9.2' );

	wp_enqueue_script( 'jigoshop_blockui', jigoshop::assets_url().'/assets/js/blockui.js', array('jquery'));
	wp_enqueue_script( 'jigoshop_frontend', jigoshop::assets_url().'/assets/js/jigoshop_frontend.js', array('jquery'));
	wp_enqueue_script( 'jigoshop_script', jigoshop::assets_url().'/assets/js/script.js', array('jquery'));

	/* Script.js variables */
	$jigoshop_params = array(
		'ajax_url' 						=> admin_url('admin-ajax.php'),
		'assets_url' 					=> jigoshop::assets_url(),
		'checkout_url'					=> admin_url('admin-ajax.php?action=jigoshop-checkout'),
		'countries' 					=> json_encode(jigoshop_countries::$states),
		'currency_symbol' 				=> get_jigoshop_currency_symbol(),
		'get_variation_nonce' 			=> wp_create_nonce("get-variation"),
		'load_fancybox'					=> $jigoshop_options->get_option( 'jigoshop_disable_fancybox' )=='no'?true:false,
		'option_guest_checkout'			=> $jigoshop_options->get_option('jigoshop_enable_guest_checkout'),
		'select_state_text' 			=> __('Select a state&hellip;', 'jigoshop'),
		'state_text' 					=> __('state', 'jigoshop'),
		'update_order_review_nonce' 	=> wp_create_nonce("update-order-review"),
        'billing_state'                 => jigoshop_customer::get_state(),
        'shipping_state'                => jigoshop_customer::get_shipping_state()
	);

	if ( isset( jigoshop_session::instance()->min_price ))
		$jigoshop_params['min_price'] = $_GET['min_price'];

	if ( isset( jigoshop_session::instance()->max_price ))
		$jigoshop_params['max_price'] = $_GET['max_price'];

	$jigoshop_params['is_checkout'] = ( is_page( jigoshop_get_page_id( 'checkout' )) || is_page( jigoshop_get_page_id( 'pay' )) );

	$jigoshop_params = apply_filters('jigoshop_params', $jigoshop_params);

	wp_localize_script( 'jigoshop_script', 'jigoshop_params', $jigoshop_params );

}


/**
 * Add a "Settings" link to the plugins.php page for Jigoshop
 **/
add_filter( 'plugin_action_links', 'jigoshop_add_settings_link', 10, 2 );
function jigoshop_add_settings_link( $links, $file ) {
	$this_plugin = plugin_basename( __FILE__ );
	if ( $file == $this_plugin ) {
		$settings_link = '<a href="admin.php?page=jigoshop_settings">' . __( 'Settings', 'jigoshop' ) . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}


/**
 * Add post thumbnail support to WordPress if needed
 **/
add_action( 'after_setup_theme', 'jigoshop_check_thumbnail_support', 99 );
function jigoshop_check_thumbnail_support() {
	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails' );
		remove_post_type_support( 'post', 'thumbnail' );
		remove_post_type_support( 'page', 'thumbnail' );
	} else {
		add_post_type_support( 'product', 'thumbnail' );
	}
}


add_action( 'admin_enqueue_scripts', 'jigoshop_admin_styles' );
function jigoshop_admin_styles() {

	/* Our setting icons */
	wp_enqueue_style( 'jigoshop_admin_icons_style', jigoshop::assets_url() . '/assets/css/admin-icons.css' );

	if ( ! jigoshop_is_admin_page() ) return false;
	wp_enqueue_style( 'jigoshop_admin_styles', jigoshop::assets_url() . '/assets/css/admin.css' );
	wp_enqueue_style( 'jigoshop-select2', jigoshop::assets_url() . '/assets/css/select2.css', '', '3.1', 'screen' );
	wp_enqueue_style( 'jquery-ui-jigoshop-styles', jigoshop::assets_url() . '/assets/css/jquery-ui-1.8.16.jigoshop.css' );
	wp_enqueue_style( 'thickbox' );

}


add_action( 'admin_print_scripts', 'jigoshop_admin_scripts' );
function jigoshop_admin_scripts() {

	if ( !jigoshop_is_admin_page() ) return false;

	$pagenow = jigoshop_is_admin_page();

	wp_enqueue_script( 'jigoshop-select2', jigoshop::assets_url().'/assets/js/select2.min.js', array( 'jquery' ), '3.1' );
	wp_enqueue_script( 'jquery-ui-datepicker', jigoshop::assets_url().'/assets/js/jquery-ui-datepicker-1.8.16.min.js', array( 'jquery' ), '1.8.16' );
	wp_enqueue_script( 'jigoshop_blockui', jigoshop::assets_url() . '/assets/js/blockui.js', array( 'jquery' ), '2.4.6' );
	wp_enqueue_script( 'jigoshop_backend', jigoshop::assets_url() . '/assets/js/jigoshop_backend.js', array( 'jquery' ), '1.0' );
	wp_enqueue_script( 'thickbox' );

	if ( $pagenow == 'jigoshop_page_jigoshop_reports' || $pagenow == 'toplevel_page_jigoshop' ) {
		wp_enqueue_script('jquery_flot', jigoshop::assets_url().'/assets/js/jquery.flot.min.js', array( 'jquery' ), '1.0' );
		wp_enqueue_script('jquery_flot_pie', jigoshop::assets_url().'/assets/js/jquery.flot.pie.min.js', array( 'jquery' ), '1.0' );
	}

	/**
	 * Disable autosaves on the order and coupon pages. Prevents the javascript alert when modifying.
	 * `wp_deregister_script( 'autosave' )` would produce errors, so we use a filter instead.
	 */
	if ( $pagenow == 'shop_order' || $pagenow == 'shop_coupon' )
		add_filter( 'script_loader_src', 'jigoshop_disable_autosave', 10, 2 );

}


//### Functions #########################################################

/**
 * Set Jigoshop Product Image Sizes for WordPress based on Admin->Jigoshop->Settings->Images
 **/
function jigoshop_set_image_sizes() {

    $jigoshop_options = Jigoshop_Base::get_options();

	$sizes = array(
		'shop_tiny'      => 'tiny',
		'shop_thumbnail' => 'thumbnail',
		'shop_small'     => 'catalog',
		'shop_large'     => 'featured'
	);

	foreach ( $sizes as $size => $altSize )
		add_image_size(
			$size,
			$jigoshop_options->get_option('jigoshop_' . $size . '_w'),
			$jigoshop_options->get_option('jigoshop_' . $size . '_h'),
			( $jigoshop_options->get_option( 'jigoshop_use_wordpress_' . $altSize . '_crop', 'no' ) == 'yes' )
		);

	/* The elephant in the room (._. ) */
	add_image_size( 'admin_product_list', 32, 32, $jigoshop_options->get_option( 'jigoshop_use_wordpress_tiny_crop', 'no' ) == 'yes' ? true : false );

}


/**
 * Get Jigoshop Product Image Size based on Admin->Jigoshop->Settings->Images
 * @param string $size - one of the 4 defined Jigoshop image sizes
 * @return array - an array containing the width and height of the required size
 * @since 0.9.9
 **/
function jigoshop_get_image_size( $size ) {

    $jigoshop_options = Jigoshop_Base::get_options();
	if ( is_array( $size ) )
		return $size;

	switch ( $size ) :
		case 'admin_product_list':
			$image_size = array( 32, 32 );
			break;
		case 'shop_tiny':
			$image_size = array( $jigoshop_options->get_option('jigoshop_shop_tiny_w'), $jigoshop_options->get_option('jigoshop_shop_tiny_h') );
			break;
		case 'shop_thumbnail':
			$image_size = array( $jigoshop_options->get_option('jigoshop_shop_thumbnail_w'), $jigoshop_options->get_option('jigoshop_shop_thumbnail_h') );
			break;
		case 'shop_small':
			$image_size = array( $jigoshop_options->get_option('jigoshop_shop_small_w'), $jigoshop_options->get_option('jigoshop_shop_small_h') );
			break;
		case 'shop_large':
			$image_size = array( $jigoshop_options->get_option('jigoshop_shop_large_w'), $jigoshop_options->get_option('jigoshop_shop_large_h') );
			break;
		default:
			$image_size = array( $jigoshop_options->get_option('jigoshop_shop_small_w'), $jigoshop_options->get_option('jigoshop_shop_small_h') );
			break;
	endswitch;

	return $image_size;
}


function jigoshop_is_admin_page() {

	global $current_screen;

	if ( $current_screen->post_type == 'product' || $current_screen->post_type == 'shop_order' || $current_screen->post_type == 'shop_coupon'  )
		return $current_screen->post_type;

	if ( strstr( $current_screen->id, 'jigoshop' ) )
		return $current_screen->id;

	return false;

}


function jigoshop_disable_autosave( $src, $handle ) {
    if ( 'autosave' != $handle ) return $src;
    return '';
}


/**
 * jigoshop_demo_store
 * Adds a demo store banner to the site
 */
add_action( 'wp_footer', 'jigoshop_demo_store' );
function jigoshop_demo_store() {

	if ( Jigoshop_Base::get_options()->get_option( 'jigoshop_demo_store' ) == 'yes' && is_jigoshop() ) :
		
		$bannner_text = apply_filters( 'jigoshop_demo_banner_text', __('This is a demo store for testing purposes &mdash; no orders shall be fulfilled.', 'jigoshop') );
		echo '<p class="demo_store">'.$bannner_text.'</p>';

	endif;
}


/**
 * jigoshop_sharethis
 * Adds social sharing code to footer
 */
add_action( 'wp_footer', 'jigoshop_sharethis' );
function jigoshop_sharethis() {

    $jigoshop_options = Jigoshop_Base::get_options();
	if (is_single() && $jigoshop_options->get_option('jigoshop_sharethis')) :

		if (is_ssl()) :
			$sharethis = 'https://ws.sharethis.com/button/buttons.js';
		else :
			$sharethis = 'http://w.sharethis.com/button/buttons.js';
		endif;

		echo '<script type="text/javascript">var switchTo5x=true;</script><script type="text/javascript" src="'.$sharethis.'"></script><script type="text/javascript">stLight.options({publisher:"'.$jigoshop_options->get_option('jigoshop_sharethis').'"});</script>';

	endif;
}


/**
 * Mail from name/email
 **/
add_filter( 'wp_mail_from_name', 'jigoshop_mail_from_name' );
function jigoshop_mail_from_name( $name ) {
	$name = get_bloginfo('name');
	$name = esc_attr($name);
	return $name;
}


/*
add_filter( 'wp_mail_from', 'jigoshop_mail_from' );
function jigoshop_mail_from( $email ) {
	$email = Jigoshop_Base::get_options()->get_option('jigoshop_email');
	return $email;
}
*/


/**
 * Allow product_cat in the permalinks for products.
 *
 * @param string $permalink The existing permalink URL.
 */
add_filter( 'post_type_link', 'jigoshop_product_cat_filter_post_link', 10, 4 );
function jigoshop_product_cat_filter_post_link( $permalink, $post, $leavename, $sample ) {

    if ($post->post_type!=='product') return $permalink;

    // Abort early if the placeholder rewrite tag isn't in the generated URL
    if ( false === strpos( $permalink, '%product_cat%' ) ) return $permalink;

    // Get the custom taxonomy terms in use by this post
    $terms = get_the_terms( $post->ID, 'product_cat' );

    if ( empty( $terms ) ) :
    	// If no terms are assigned to this post, use a string instead
        $permalink = str_replace( '%product_cat%', _x('product', 'slug', 'jigoshop'), $permalink );
    else :
    	// Replace the placeholder rewrite tag with the first term's slug
        $first_term = apply_filters( 'jigoshop_product_cat_permalink_terms', array_shift( $terms ), $terms);
        $permalink = str_replace( '%product_cat%', $first_term->slug, $permalink );
    endif;

    return $permalink;
}


/**
 * Evaluates to true only on the Shop page, not Product categories and tags
 * Note:is used to replace is_page( jigoshop_get_page_id( 'shop' ) )
 *
 * @return bool
 * @since 0.9.9
 */
function is_shop() {
	return is_post_type_archive( 'product' ) || is_page( jigoshop_get_page_id('shop') );
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
 * Jigoshop page IDs
 *
 * returns -1 if no page is found
 **/
if (!function_exists('jigoshop_get_page_id')) {
	function jigoshop_get_page_id( $page ) {
        $jigoshop_options = Jigoshop_Base::get_options();
		$page = apply_filters('jigoshop_get_' . $page . '_page_id', $jigoshop_options->get_option('jigoshop_' . $page . '_page_id'));
		return ($page) ? $page : -1;
	}
}

/**
 * Evaluates to true only on the Order Tracking page
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_order_tracker() {
	return is_page( jigoshop_get_page_id('track_order'));
}

/**
 * Evaluates to true only on the Cart page
 *
 * @return bool
 * @since 0.9.8
 */
function is_cart() {
	return is_page( jigoshop_get_page_id('cart'));
}

/**
 * Evaluates to true only on the Checkout or Pay pages
 *
 * @return bool
 * @since 0.9.8
 */
function is_checkout() {
	return is_page( jigoshop_get_page_id('checkout')) | is_page( jigoshop_get_page_id('pay'));
}

/**
 * Evaluates to true only on the main Account or any sub-account pages
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_account() {
	$is_account = false;
	$is_account |= is_page( jigoshop_get_page_id('myaccount') );
	$is_account |= is_page( jigoshop_get_page_id('edit_address') );
	$is_account |= is_page( jigoshop_get_page_id('change_password') );
	$is_account |= is_page( jigoshop_get_page_id('view_order') );
	return $is_account;
}

if (!function_exists('is_ajax')) {
	function is_ajax() {
		if ( defined('DOING_AJAX') ) return true;
		return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' );
	}
}

function jigoshop_force_ssl() {
	if (is_checkout() && !is_ssl()) :
		wp_safe_redirect( str_replace('http:', 'https:', get_permalink(jigoshop_get_page_id('checkout'))), 301 );
		exit;
	endif;
}
if (!is_admin() && Jigoshop_Base::get_options()->get_option('jigoshop_force_ssl_checkout')=='yes') add_action( 'wp', 'jigoshop_force_ssl');

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


function get_jigoshop_currency_symbol() {

    $jigoshop_options = Jigoshop_Base::get_options();
	$currency = $jigoshop_options->get_option('jigoshop_currency');
	$symbols = jigoshop::currency_symbols();
	$currency_symbol = $symbols[$currency];
	
	return apply_filters('jigoshop_currency_symbol', $currency_symbol, $currency);
	
}

function jigoshop_price( $price, $args = array() ) {

    $jigoshop_options = Jigoshop_Base::get_options();
	extract(shortcode_atts(array(
		'ex_tax_label' 	=> 0, // 0 for no label, 1 for ex. tax, 2 for inc. tax
        'with_currency' => true
	), $args));


    $tax_label = '';

    if ($ex_tax_label === 1) {
        $tax_label = __(' <small>(ex. tax)</small>', 'jigoshop');
    } else if ($ex_tax_label === 2) {
        $tax_label = __(' <small>(inc. tax)</small>', 'jigoshop');
    } else {
        $tax_label = '';
    }

	$return = '';
	$price = number_format(
		(double) $price,
		(int) $jigoshop_options->get_option('jigoshop_price_num_decimals'),
		$jigoshop_options->get_option('jigoshop_price_decimal_sep'),
		$jigoshop_options->get_option('jigoshop_price_thousand_sep')
	);

    $return = $price;

    if ($with_currency) :

        $currency_pos = $jigoshop_options->get_option('jigoshop_currency_pos');
        $currency_symbol = get_jigoshop_currency_symbol();
        $currency_code = $jigoshop_options->get_option('jigoshop_currency');

        switch ($currency_pos) :
            case 'left' :
                $return = $currency_symbol . $price;
            break;
            case 'left_space' :
                $return = $currency_symbol . ' ' . $price;
            break;
            case 'right' :
                $return = $price . $currency_symbol;
            break;
            case 'right_space' :
                $return = $price . ' ' . $currency_symbol;
            break;
            case 'left_code' :
                $return = $currency_code . $price;
            break;
            case 'left_code_space' :
                $return = $currency_code . ' ' . $price;
            break;
            case 'right_code' :
                $return = $price . $currency_code;
            break;
            case 'right_code_space' :
                $return = $price . ' ' . $currency_code;
            break;
            case 'code_symbol' :
                $return = $currency_code . $price . $currency_symbol;
            break;
            case 'code_symbol_space' :
                $return = $currency_code . ' ' . $price . ' ' . $currency_symbol;
            break;
            case 'symbol_code' :
                $return = $currency_symbol . $price . $currency_code;
            break;
            case 'symbol_code_space' :
                $return = $currency_symbol . ' ' . $price . ' ' . $currency_code;
            break;
        endswitch;

        // only show tax label (ex. tax) if we are going to show the price with currency as well. Otherwise we just want the formatted price
        if ($jigoshop_options->get_option('jigoshop_calc_taxes')=='yes') $return .= $tax_label;

    endif;

	return apply_filters( 'jigoshop_price_display_filter', $return);
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
				$name = jigoshop_product::attribute_label('pa_'.$name);
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
function jigoshop_filter_request($qv) {
	if (isset($qv['feed']) && !empty($qv['withcomments']))
	{
		add_filter('comment_feed_where', 'jigoshop_comment_feed_where');
	}
	return $qv;
}

function jigoshop_comment_feed_where($cwhere) {
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

// Returns a float value
function jigoshop_sanitize_num( $var ) {
	// TODO: as it stands, it doesn't allow negative values (-JAP-)
	// should be - preg_replace("/^[^[\-\+]0-9\.]/","",$var)
	// currently only used for prices in product-data-save.php
	return strip_tags(stripslashes(floatval(preg_replace("/^[^0-9\.]/","",$var))));
}

// Author: Sergey Biryukov
// Plugin URI: http://wordpress.org/extend/plugins/allow-cyrillic-usernames/
add_filter('sanitize_user', 'jigoshop_sanitize_user', 10, 3);
function jigoshop_sanitize_user($username, $raw_username, $strict) {
	$username = wp_strip_all_tags( $raw_username );
	$username = remove_accents( $username );
	$username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
	$username = preg_replace( '/&.+?;/', '', $username ); // Kill entities

	if ( $strict )
		$username = preg_replace( '|[^a-z?-?0-9 _.\-@]|iu', '', $username );

	$username = trim( $username );
	$username = preg_replace( '|\s+|', ' ', $username );

	return $username;
}

add_action( 'wp_head', 'jigoshop_head_version' );
function jigoshop_head_version() {
	echo "\n" . '<!-- Jigoshop Version: '.jigoshop::jigoshop_version().' -->' . "\n";
}

global $jigoshop_body_classes;

add_action('wp_head', 'jigoshop_page_body_classes');
function jigoshop_page_body_classes() {

	global $jigoshop_body_classes;

	$jigoshop_body_classes = (array) $jigoshop_body_classes;

	if ( is_order_tracker() ) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-tracker' ) );

	if ( is_checkout() ) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-checkout' ) );

	if ( is_cart() ) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-cart' ) );

	if ( is_page(jigoshop_get_page_id('thanks'))) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-thanks' ) );

	if ( is_page(jigoshop_get_page_id('pay'))) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-pay' ) );

	if ( is_account() ) jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-myaccount' ) );

}

function jigoshop_add_body_class( $class = array() ) {

	global $jigoshop_body_classes;

	$jigoshop_body_classes = (array) $jigoshop_body_classes;

	$jigoshop_body_classes = array_unique( array_merge( $class, $jigoshop_body_classes ));
}

add_filter('body_class','jigoshop_body_class');
function jigoshop_body_class($classes) {

	global $jigoshop_body_classes;

	$jigoshop_body_classes = (array) $jigoshop_body_classes;

	$classes = array_unique( array_merge( $classes, $jigoshop_body_classes ));

	return $classes;
}

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
		wp_die( __('Please rate the product.',"jigoshop") );
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
				<?php if ( $rating = get_comment_meta( $comment->comment_ID, 'rating', true ) ): ?>
				<div class="star-rating" title="<?php echo esc_attr( $rating ); ?>">
					<span style="width:<?php echo $rating*16; ?>px"><?php echo $rating; ?> <?php _e('out of 5', 'jigoshop'); ?></span>
				</div>
				<?php endif; ?>
				<?php if ($comment->comment_approved == '0') : ?>
					<p class="meta"><em><?php _e('Your comment is awaiting approval','jigoshop'); ?></em></p>
				<?php else : ?>
					<p class="meta">
						<?php _e('Rating by','jigoshop'); ?> <strong class="reviewer vcard"><span class="fn"><?php comment_author(); ?></span></strong> <?php _e('on','jigoshop'); ?> <?php echo date_i18n(get_option('date_format'), strtotime(get_comment_date('Y-m-d'))); ?>:
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
add_filter( 'comments_clauses', 'jigoshop_exclude_order_admin_comments', 10, 1);
function jigoshop_exclude_order_admin_comments( $clauses ) {

	global $wpdb, $typenow, $pagenow;

	// NOTE: bit of a hack, tests if we're in the admin & its an ajax call
	if ( is_admin() && ( $typenow == 'shop_order' || $pagenow == 'admin-ajax.php' ) && current_user_can( 'manage_jigoshop' ) )
		return $clauses; // Don't hide when viewing orders in admin

	if ( ! $clauses['join'] ) $clauses['join'] = '';

	if ( ! strstr( $clauses['join'], "JOIN $wpdb->posts" ) )
		$clauses['join'] .= " LEFT JOIN $wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID ";

	if ( $clauses['where'] ) $clauses['where'] .= ' AND ';

	$clauses['where'] .= " $wpdb->posts.post_type NOT IN ('shop_order') ";

	return $clauses;

}

/**
 * Support for Import/Export
 *
 * WordPress import should work - however, it fails to import custom product attribute taxonomies.
 * This code grabs the file before it is imported and ensures the taxonomies are created.
 **/
function jigoshop_import_start() {

	global $wpdb;
    $jigoshop_options = Jigoshop_Base::get_options();

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

								$exists_in_db = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_id FROM ".$wpdb->prefix . "jigoshop_attribute_taxonomies WHERE attribute_name = %s;", $nicename ) );

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
												'name'             => $nicename,
												'singular_name'    => $nicename,
												'search_items'     =>  __( 'Search ', 'jigoshop') . $nicename,
												'all_items'        => __( 'All ', 'jigoshop') . $nicename,
												'parent_item'      => __( 'Parent ', 'jigoshop') . $nicename,
												'parent_item_colon'=> __( 'Parent ', 'jigoshop') . $nicename . ':',
												'edit_item'        => __( 'Edit ', 'jigoshop') . $nicename,
												'update_item'      => __( 'Update ', 'jigoshop') . $nicename,
												'add_new_item'     => __( 'Add New ', 'jigoshop') . $nicename,
												'new_item_name'    => __( 'New ', 'jigoshop') . $nicename
							            ),
										'show_ui'  => false,
										'query_var'=> true,
										'rewrite'  => array( 'slug'=> sanitize_title($nicename), 'with_front'=> false, 'hierarchical'=> true ),
							        )
							    );

								$jigoshop_options->set_option('jigowatt_update_rewrite_rules', '1');

							endif;

						endif;

					endforeach;

				endif;

			endif;

		endforeach;

	endif;

}
add_action('import_start', 'jigoshop_import_start');


if(!function_exists('jigoshop_log')){

    /**
     * Logs to the debug log when you enable wordpress debug mode.
     *
     * @param string $from_class is the name of the php file that you are logging from.
     * defaults to jigoshop if non is supplied.
     * @param mixed $message this can be a regular string, array or object
     */
    function jigoshop_log( $message, $from_class = 'jigoshop' ) {

        if( WP_DEBUG === true ) :
            if( is_array( $message ) || is_object( $message ) ) :
                error_log( $from_class . ': ' . print_r( $message, true ) );
            else :
                error_log( $from_class . ': ' . $message );
            endif;
        endif;

    }
}
