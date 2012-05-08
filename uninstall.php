<?php
/**
 * Uninstall Script
 *
 * Removes all traces of Jigoshop from the wordpress database
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */

if( !defined('WP_UNINSTALL_PLUGIN') ) exit();

global $wpdb, $wp_roles;

// Remove the widget cache entry
delete_transient( 'jigoshop_widget_cache' );

// Roles
remove_role( 'customer' );

// Pages
wp_delete_post( get_option('jigoshop_cart_page_id'), true );
wp_delete_post( get_option('jigoshop_change_password_page_id'), true );
wp_delete_post( get_option('jigoshop_checkout_page_id'), true );
wp_delete_post( get_option('jigoshop_edit_address_page_id'), true );
wp_delete_post( get_option('jigoshop_myaccount_page_id'), true );
wp_delete_post( get_option('jigoshop_pay_page_id'), true );
wp_delete_post( get_option('jigoshop_shop_page_id'), true );
wp_delete_post( get_option('jigoshop_thanks_page_id'), true );
wp_delete_post( get_option('jigoshop_track_order_page_id'), true );
wp_delete_post( get_option('jigoshop_view_order_page_id'), true );

// Tables
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."jigoshop_attribute_taxonomies");
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."jigoshop_downloadable_product_permissions");
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."jigoshop_termmeta");

// Order Status
$wpdb->query("DELETE FROM $wpdb->terms WHERE term_id IN (select term_id FROM $wpdb->term_taxonomy WHERE taxonomy IN ('product_type', 'shop_order_status'))");
$wpdb->query("DELETE FROM $wpdb->term_taxonomy WHERE taxonomy = 'shop_order_status'");

// Delete options
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'jigoshop_%'");
