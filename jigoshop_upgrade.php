<?php
/**
 * Jigoshop Upgrade API
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
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * Run Jigoshop Upgrade functions.
*/
function jigoshop_upgrade() {
	// Get the db version
	$jigoshop_db_version = get_site_option( 'jigoshop_db_version' );

	if ( $jigoshop_db_version == JIGOSHOP_DB_VERSION )
		return;

 	if ( $jigoshop_db_version < 1307110 ) {
 		jigoshop_upgrade_1_8_0();
 	}

	if ($jigoshop_db_version < 1407060 ) {
		jigoshop_upgrade_1_10_0();
	}

	// Update the db option
	update_site_option( 'jigoshop_db_version', JIGOSHOP_DB_VERSION );
}

/**
 * Execute changes made in Jigoshop 1.8
 *
 * @since 1.8
 */
function jigoshop_upgrade_1_8_0() {
	Jigoshop_Base::get_options()->add_option( 'jigoshop_complete_processing_orders', 'no' );
}

function jigoshop_upgrade_1_10_0(){
	// TODO: Convert {billing,shipping}-address(2)? to {billing,shipping}_address_{1,2} format in orders
	// TODO: Convert items from {billing,shipping}-field format to {billing,shipping}_ format in orders
	// TODO: Convert user-meta from {billing,shipping}-field into {billing,shipping}_field format
	// TODO: Move jigoshop_address_line{1,2} to jigoshop_address_{1,2}
}
