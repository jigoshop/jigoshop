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
if ( !defined( "JIGOSHOP_OPTIONS" )) define( "JIGOSHOP_OPTIONS", 'jigoshop_options' );
include_once( 'classes/abstract/jigoshop_base.class.php' );
include_once( 'classes/jigoshop_options.class.php' );
$page_ids = Jigoshop_Base::get_options()->get_option( 'jigoshop_page-ids' );
if ( !empty( $page_ids ) && is_array( $page_ids ) )
	foreach ( $page_ids as $id )
		wp_delete_post( $id );

// Tables
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."jigoshop_attribute_taxonomies");
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."jigoshop_downloadable_product_permissions");
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."jigoshop_termmeta");

// Order Status
$wpdb->query("DELETE FROM $wpdb->terms WHERE term_id IN (select term_id FROM $wpdb->term_taxonomy WHERE taxonomy IN ('product_type', 'shop_order_status'))");
$wpdb->query("DELETE FROM $wpdb->term_taxonomy WHERE taxonomy = 'shop_order_status'");

// Delete options
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'jigoshop_%'");
