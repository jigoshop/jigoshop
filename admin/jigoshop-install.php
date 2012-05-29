<?php
/**
 * Plugin install script which adds default pages, taxonomies, and database tables
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 * Install jigoshop
 *
 * Calls each function to install bits, and clears the cron jobs and rewrite rules
 *
 * @since 		1.0
 */

function install_jigoshop() {

	global $wpdb;

	if (function_exists('is_multisite') && is_multisite()) {

		if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
			$old_blog = $wpdb->blogid;
			$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				_install_jigoshop();
			}
			switch_to_blog($old_blog);
			return;
		}
	}

	_install_jigoshop();

}

function _install_jigoshop() {

	if( ! get_option('jigoshop_db_version') )  {

		jigoshop_tables_install();		/* we need tables installed first to eliminate installation errors */

		// Get options
		require_once ( 'jigoshop-admin-settings-options.php' );

		// Do install
		jigoshop_default_options();
		jigoshop_create_pages();

		jigoshop_post_type();
		jigoshop_default_taxonomies();

		// Clear cron
		wp_clear_scheduled_hook('jigoshop_update_sale_prices_schedule_check');
		update_option('jigoshop_update_sale_prices', 'no');

		// Flush Rules
		flush_rewrite_rules( false );

		// Update version
		update_option( "jigoshop_db_version", JIGOSHOP_VERSION );
	}
}

/**
 * Default options
 *
 * Sets up the default options used on the settings page
 *
 * @since 		1.0
 */
function jigoshop_default_options() {
	global $jigoshop_options_settings;

	foreach ($jigoshop_options_settings as $value) :

        if (isset($value['std'])) :

				if ($value['type']=='image_size') :

					add_option($value['id'].'_w', $value['std']);
					add_option($value['id'].'_h', $value['std']);

				else :

					add_option($value['id'], $value['std']);

				endif;

		endif;

    endforeach;

    add_option('jigoshop_shop_slug', 'shop');
}

/**
 * Create pages
 *
 * Creates pages that the plugin relies on, storing page id's in options.
 *
 * @since 		0.9.9.1
 */
function jigoshop_create_pages() {

	// start out with basic page parameters, modify as we go
	$page_data = array(
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'post_author'    => 1,
		'post_name'      => '',
		'post_title'     => __('Shop', 'jigoshop'),
		'post_content'   => '',
		'comment_status' => 'closed'
	);
	jigoshop_create_single_page( 'shop', 'jigoshop_shop_page_id', $page_data );

	$shop_page = get_option('jigoshop_shop_page_id');
	update_option( 'jigoshop_shop_redirect_page_id' , $shop_page );

	$page_data['post_title']   = __('Cart', 'jigoshop');
	$page_data['post_content'] = '[jigoshop_cart]';
	jigoshop_create_single_page( 'cart', 'jigoshop_cart_page_id', $page_data );

	$page_data['post_title']   = __('Track your order', 'jigoshop');
	$page_data['post_content'] = '[jigoshop_order_tracking]';
	jigoshop_create_single_page( 'order-tracking', 'jigoshop_track_order_page_id', $page_data );

	$page_data['post_title']   = __('My Account', 'jigoshop');
	$page_data['post_content'] = '[jigoshop_my_account]';
	jigoshop_create_single_page( 'my-account', 'jigoshop_myaccount_page_id', $page_data );

	$page_data['post_title']   = __('Edit My Address', 'jigoshop');
	$page_data['post_content'] = '[jigoshop_edit_address]';
	$page_data['post_parent']  = jigoshop_get_page_id('myaccount');
	jigoshop_create_single_page( 'edit-address', 'jigoshop_edit_address_page_id', $page_data );

	$page_data['post_title']   = __('Change Password', 'jigoshop');
	$page_data['post_content'] = '[jigoshop_change_password]';
	$page_data['post_parent']  = jigoshop_get_page_id('myaccount');
	jigoshop_create_single_page( 'change-password', 'jigoshop_change_password_page_id', $page_data );

	$page_data['post_title']   = __('View Order', 'jigoshop');
	$page_data['post_content'] = '[jigoshop_view_order]';
	$page_data['post_parent']  = jigoshop_get_page_id('myaccount');
	jigoshop_create_single_page( 'view-order', 'jigoshop_view_order_page_id', $page_data );

	$page_data['post_title']   = __('Checkout', 'jigoshop');
	$page_data['post_content'] = '[jigoshop_checkout]';
	unset( $page_data['post_parent'] );
	jigoshop_create_single_page( 'checkout', 'jigoshop_checkout_page_id', $page_data );

	$page_data['post_title']   = __('Checkout &rarr; Pay', 'jigoshop');
	$page_data['post_content'] = '[jigoshop_pay]';
	$page_data['post_parent']  = jigoshop_get_page_id('checkout');
	jigoshop_create_single_page( 'pay', 'jigoshop_pay_page_id', $page_data );

	$page_data['post_title']   = __('Thank you', 'jigoshop');
	$page_data['post_content'] = '[jigoshop_thankyou]';
	$page_data['post_parent']  = jigoshop_get_page_id('checkout');
	jigoshop_create_single_page( 'thanks', 'jigoshop_thanks_page_id', $page_data );

}

/**
 * Install a single Jigoshop Page
 *
 * @param string $page_slug - is the slug for the page to create (shop|cart|thank-you|etc)
 * @param string $page_option - the database options entry for page ID storage
 * @param array $page_data - preset default parameters for creating the page - this will finish the slug
 *
 * @since 1.3
 */
function jigoshop_create_single_page( $page_slug, $page_option, $page_data ) {

	global $wpdb;

	$slug    = esc_sql( _x( $page_slug, 'page_slug', 'jigoshop' ) );
	$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_status = 'publish' AND post_status <> 'trash' LIMIT 1", $slug ) );

	if ( ! $page_id ) {
		$page_data['post_name'] = $slug;
		$page_id = wp_insert_post( $page_data );
	}

	update_option( $page_option, $page_id );

	$ids = get_option ( 'jigoshop_page-ids' );
	$ids[] = $page_id;

	update_option( 'jigoshop_page-ids', $ids );

}

/**
 * Table Install
 *
 * Sets up the database tables which the plugin needs to function.
 *
 * @since 		1.0
 */
function jigoshop_tables_install() {
	global $wpdb;

	//$wpdb->show_errors();

    $collate = '';
    if($wpdb->supports_collation()) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
    }

    $sql = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "jigoshop_attribute_taxonomies" ." (
        `attribute_id` 			mediumint(9) NOT NULL AUTO_INCREMENT,
        `attribute_name`		varchar(200) NOT NULL,
		`attribute_label`		longtext NULL,
        `attribute_type`		varchar(200) NOT NULL,
        PRIMARY KEY id (`attribute_id`)) $collate;";
    $wpdb->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "jigoshop_downloadable_product_permissions" ." (
        `product_id` 			mediumint(9) NOT NULL,
        `user_email`			varchar(200) NOT NULL,
        `user_id`				mediumint(9) NULL,
        `order_key`				varchar(200) NOT NULL,
        `downloads_remaining`	varchar(9) NULL,
        PRIMARY KEY id (`product_id`, `order_key`)) $collate;";
    $wpdb->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "jigoshop_termmeta" ." (
		`meta_id` 				bigint(20) NOT NULL AUTO_INCREMENT,
      	`jigoshop_term_id` 		bigint(20) NOT NULL,
      	`meta_key` 				varchar(255) NULL,
      	`meta_value` 			longtext NULL,
      	PRIMARY KEY id (`meta_id`)) $collate;";
    $wpdb->query($sql);

}

/**
 * Default taxonomies
 *
 * Adds the default terms for taxonomies - product types and order statuses. Modify at your own risk.
 *
 * @since 		1.0
 */
function jigoshop_default_taxonomies() {

	$product_types = array(
		'simple',
		'external',
		'grouped',
		'configurable',
		'downloadable',
		'virtual'
	);

	foreach($product_types as $type) {
		if (!$type_id = get_term_by( 'slug', sanitize_title($type), 'product_type')) {
			wp_insert_term($type, 'product_type');
		}
	}

	$order_status = array(
		'pending',
		'on-hold',
		'processing',
		'completed',
		'refunded',
		'cancelled'
	);

	foreach($order_status as $status) {
		if (!$status_id = get_term_by( 'slug', sanitize_title($status), 'shop_order_status')) {
			wp_insert_term($status, 'shop_order_status');
		}
	}

}