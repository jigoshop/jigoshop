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
 * @package    Jigoshop
 * @category   Core
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

if( !defined('WP_UNINSTALL_PLUGIN') ) exit();

global $wpdb, $wp_roles;

// Remove the widget cache entry
delete_transient( 'jigoshop_widget_cache' );

// Roles
remove_role( 'customer' );

// Pages
wp_delete_post( jigoshop_get_page_id('shop'), true );
wp_delete_post( jigoshop_get_page_id('cart'), true );
wp_delete_post( jigoshop_get_page_id('checkout'), true );
wp_delete_post( jigoshop_get_page_id('order-tracking'), true );
wp_delete_post( jigoshop_get_page_id('my-account'), true );
wp_delete_post( jigoshop_get_page_id('edit-address'), true );
wp_delete_post( jigoshop_get_page_id('view-order'), true );
wp_delete_post( jigoshop_get_page_id('change-password'), true );
wp_delete_post( jigoshop_get_page_id('pay'), true );
wp_delete_post( jigoshop_get_page_id('thanks'), true );

// Tables
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."jigoshop_attribute_taxonomies");
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."jigoshop_downloadable_product_permissions");
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."jigoshop_termmeta");

// Delete options
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'jigoshop_%';");