<?php defined('ABSPATH') or die('No direct script access.');
/**
 * Jigoshop Upgrade API
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
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

/**
 * Run Jigoshop Upgrade functions.
 *
 * @since 2.1.0
 * @return null
*/
function jigoshop_upgrade() {

	// Get the db version
	$jigoshop_db_version = get_site_option( 'jigoshop_db_version' );

	// 'Cause we aint got shiz to do
	if ( $jigoshop_db_version == JIGOSHOP_VERSION );
		return false;

	if ( ! is_numeric($jigoshop_db_version) ) {
		convert_db_version();
	}

	if ( $jigoshop_db_version < 1109200 ) {
		upgrade_99();
	}

	if ( $jigoshop_db_version < 1202010 ) {
		upgrade_100();
	}

	// Update the db option
	update_site_option( 'jigoshop_db_version', JIGOSHOP_VERSION );
}

/**
 * Updates jigoshop db version to a numeric value for better comparison
 */
function jigoshop_convert_db_version() {
	global $wpdb;

	$jigoshop_db_version = get_site_option('jigoshop_db_version');

	switch ( $jigoshop_db_version ) {
		case '0.9.6':
			update_site_option( 'jigoshop_db_version', 1105310 );
			break;
		case '0.9.7':
			update_site_option( 'jigoshop_db_version', 1105311 );
			break;
		case '0.9.7.1':
			update_site_option( 'jigoshop_db_version', 1105312 );
			break;
		case '0.9.7.2':
			update_site_option( 'jigoshop_db_version', 1105313 );
			break;
		case '0.9.7.3':
			update_site_option( 'jigoshop_db_version', 1106010 );
			break;
		case '0.9.7.4':
			update_site_option( 'jigoshop_db_version', 1106011 );
			break;
		case '0.9.7.5':
			update_site_option( 'jigoshop_db_version', 1106130 );
			break;
		case '0.9.7.6':
			update_site_option( 'jigoshop_db_version', 1106140 );
			break;
		case '0.9.7.7':
			update_site_option( 'jigoshop_db_version', 1106220 );
			break;
		case '0.9.7.8':
			update_site_option( 'jigoshop_db_version', 1106221 );
			break;
		case '0.9.8':
			update_site_option( 'jigoshop_db_version', 1107010 );
			break;
		case '0.9.8.1':
			update_site_option( 'jigoshop_db_version', 1109080 );
			break;
		case '0.9.9':
			update_site_option( 'jigoshop_db_version', 1109200 );
			break;
		case '0.9.9.1':
			update_site_option( 'jigoshop_db_version', 1111090 );
			break;
		case '0.9.9.2':
			update_site_option( 'jigoshop_db_version', 1111091 );
			break;
		case '0.9.9.3':
			update_site_option( 'jigoshop_db_version', 1111092 );
			break;
	}
}

/**
 * Execute changes made in Jigoshop 0.9.9
 *
 * @since 0.9.9
 */
function jigoshop_upgrade_99() {
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
}

/**
 * Execute changes made in Jigoshop 1.0
 *
 * @since 1.0.0
 */
function jigoshop_upgrade_100() {
	global $wpdb;

	// Run upgrade
}