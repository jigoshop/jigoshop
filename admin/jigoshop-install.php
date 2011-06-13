<?php
/**
 * JigoShop Install
 * 
 * Plugin install script which adds default pages, taxonomies, and database tables
 *
 * @author 		Jigowatt
 * @category 	Admin
 * @package 	JigoShop
 */

/**
 * Install jigoshop
 * 
 * Calls each function to install bits, and clears the cron jobs and rewrite rules
 *
 * @since 		1.0
 */
function install_jigoshop() {
	jigoshop_default_options();
	jigoshop_create_pages();
	jigoshop_tables_install();
	
	jigoshop_post_type();
	jigoshop_default_taxonomies();
	
	// Clear cron
	wp_clear_scheduled_hook('jigoshop_update_sale_prices_schedule_check');
	update_option('jigoshop_update_sale_prices', 'no');
	
	// Flush Rules
	flush_rewrite_rules();
}

/**
 * Default options
 * 
 * Sets up the default options used on the settings page
 *
 * @since 		1.0
 */
function jigoshop_default_options() {
	global $options_settings;
	foreach ($options_settings as $value) {
        if (isset($value['std'])) add_option($value['id'], $value['std']);
    }
    
    add_option('jigoshop_shop_slug', 'shop');
}

/**
 * Create pages
 * 
 * Creates pages that the plugin relies on, storing page id's in variables.
 *
 * @since 		1.0
 */
function jigoshop_create_pages() {
    global $wpdb;
	
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'cart' LIMIT 1");

    if(!$page_found) {

        $page_data = array(
	        'post_status' => 'publish',
	        'post_type' => 'page',
	        'post_author' => 1,
	        'post_name' => 'cart',
	        'post_title' => 'Cart',
	        'post_content' => '[jigoshop_cart]',
	        'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);

        update_option('jigoshop_cart_page_id', $page_id);

    } else {
    	update_option('jigoshop_cart_page_id', $page_found);
    }
    
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'checkout' LIMIT 1");

    if(!$page_found) {

        $page_data = array(
	        'post_status' => 'publish',
	        'post_type' => 'page',
	        'post_author' => 1,
	        'post_name' => 'checkout',
	        'post_title' => 'Checkout',
	        'post_content' => '[jigoshop_checkout]',
	        'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);

        update_option('jigoshop_checkout_page_id', $page_id);

    } else {
    	update_option('jigoshop_checkout_page_id', $page_found);
    }
    
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'order_tracking' LIMIT 1");

    if(!$page_found) {

        $page_data = array(
	        'post_status' => 'publish',
	        'post_type' => 'page',
	        'post_author' => 1,
	        'post_name' => 'order_tracking',
	        'post_title' => 'Track your order',
	        'post_content' => '[jigoshop_order_tracking]',
	        'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);
    } 
    
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'my-account' LIMIT 1");

    if(!$page_found) {

        $page_data = array(
	        'post_status' => 'publish',
	        'post_type' => 'page',
	        'post_author' => 1,
	        'post_name' => 'my-account',
	        'post_title' => 'My Account',
	        'post_content' => '[jigoshop_my_account]',
	        'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);
        
        update_option('jigoshop_myaccount_page_id', $page_id);

    } else {
    	update_option('jigoshop_myaccount_page_id', $page_found);
    } 
    
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'edit-address' LIMIT 1");

    if(!$page_found) {

        $page_data = array(
	        'post_status' => 'publish',
	        'post_type' => 'page',
	        'post_parent' => get_option('jigoshop_myaccount_page_id'),
	        'post_author' => 1,
	        'post_name' => 'edit-address',
	        'post_title' => 'Edit My Address',
	        'post_content' => '[jigoshop_edit_address]',
	        'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);
        
        update_option('jigoshop_edit_address_page_id', $page_id);

    } else {
    	update_option('jigoshop_edit_address_page_id', $page_found);
    } 
    
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'view-order' LIMIT 1");

    if(!$page_found) {

        $page_data = array(
	        'post_status' => 'publish',
	        'post_type' => 'page',
	        'post_parent' => get_option('jigoshop_myaccount_page_id'),
	        'post_author' => 1,
	        'post_name' => 'view-order',
	        'post_title' => 'View Order',
	        'post_content' => '[jigoshop_view_order]',
	        'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);
        
        update_option('jigoshop_view_order_page_id', $page_id);

    } else {
    	update_option('jigoshop_view_order_page_id', $page_found);
    } 
    
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'change-password' LIMIT 1");

    if(!$page_found) {

        $page_data = array(
	        'post_status' => 'publish',
	        'post_type' => 'page',
	        'post_parent' => get_option('jigoshop_myaccount_page_id'),
	        'post_author' => 1,
	        'post_name' => 'change-password',
	        'post_title' => 'Change Password',
	        'post_content' => '[jigoshop_change_password]',
	        'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);
        
        update_option('jigoshop_change_password_page_id', $page_id);

    } else {
    	update_option('jigoshop_change_password_page_id', $page_found);
    }
    
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'pay' LIMIT 1");

    if(!$page_found) {

        $page_data = array(
	        'post_status' => 'publish',
	        'post_type' => 'page',
	        'post_parent' => get_option('jigoshop_checkout_page_id'),
	        'post_author' => 1,
	        'post_name' => 'pay',
	        'post_title' => 'Checkout &rarr; Pay',
	        'post_content' => '[jigoshop_pay]',
	        'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);

        update_option('jigoshop_pay_page_id', $page_id);

    } else {
    	update_option('jigoshop_pay_page_id', $page_found);
    }
    
    // Thank you Page
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'thanks' LIMIT 1");

	if(!$page_found) {
	
	    $page_data = array(
	        'post_status' => 'publish',
	        'post_type' => 'page',
	        'post_parent' => get_option('jigoshop_checkout_page_id'),
	        'post_author' => 1,
	        'post_name' => 'thanks',
	        'post_title' => 'Thank you',
	        'post_content' => '[jigoshop_thankyou]',
	        'comment_status' => 'closed'
	    );
	    $page_id = wp_insert_post($page_data);
	
	    update_option('jigoshop_thanks_page_id', $page_id);
	
	} else {
		update_option('jigoshop_thanks_page_id', $page_found);
	}
	
	// Thank you Page
    
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
	
	$wpdb->show_errors();
	
    $collate = '';
    if($wpdb->supports_collation()) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
    }
    
    $sql = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "jigoshop_attribute_taxonomies" ." (
        `attribute_id` 			mediumint(9) NOT NULL AUTO_INCREMENT,
        `attribute_name`		varchar(200) NOT NULL,
        `attribute_type`		varchar(200) NOT NULL,
        PRIMARY KEY id (`attribute_id`)) $collate;";
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "jigoshop_downloadable_product_permissions" ." (
        `product_id` 			mediumint(9) NOT NULL,
        `user_email`			varchar(200) NOT NULL,
        `user_id`				mediumint(9) NULL,
        `order_key`				varchar(200) NOT NULL,
        `downloads_remaining`	mediumint(9) NULL,
        PRIMARY KEY id (`product_id`, `order_key`)) $collate;";
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