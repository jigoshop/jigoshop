<?php
/**
 * Jigoshop Upgrade API
 * DISCLAIMER
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
 
 if ( !defined('ABSPATH') ){
	die("Not to be accessed directly");
}
 
function jigoshop_upgrade()
{
	// Get the db version
	$jigoshop_db_version = get_site_option('jigoshop_db_version');
	if ($jigoshop_db_version == JIGOSHOP_DB_VERSION) {
		return;
	}
	if ($jigoshop_db_version < 1307110) {
		jigoshop_upgrade_1_8_0();
	}
	if ($jigoshop_db_version < 1407060) {
		jigoshop_upgrade_1_10_0();
	}
	if ($jigoshop_db_version < 1408200) {
		jigoshop_upgrade_1_10_3();
	}
	if ($jigoshop_db_version < 1409050) {
		jigoshop_upgrade_1_10_6();
	}
	if ($jigoshop_db_version < 1411270) {
		jigoshop_upgrade_1_13_3();
	}
	if ($jigoshop_db_version < 1503040) {
		jigoshop_upgrade_1_16_0();
	}
	if ($jigoshop_db_version < 1503180) {
		jigoshop_upgrade_1_16_1();
	}
	// Update the db option
	update_site_option('jigoshop_db_version', JIGOSHOP_DB_VERSION);
}

/**
 * Execute changes made in Jigoshop 1.8
 *
 * @since 1.8
 */
function jigoshop_upgrade_1_8_0()
{
	Jigoshop_Base::get_options()->add('jigoshop_complete_processing_orders', 'no');
}

function jigoshop_upgrade_1_10_0()
{
	/** @var $wpdb wpdb */
	global $wpdb;
	$data = $wpdb->get_results("SELECT umeta_id, user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE 'billing-%' OR meta_key LIKE 'shipping-%'", ARRAY_A);
	if (!empty($data)) {
		$query = "REPLACE INTO {$wpdb->usermeta} VALUES ";
		foreach ($data as $item) {
			$key = str_replace(array('billing-', 'shipping-'), array(
				'billing_',
				'shipping_'
			), $item['meta_key']);
			$query .= "({$item['umeta_id']}, {$item['user_id']}, '{$key}', '{$item['meta_value']}'),";
		}
		unset($data);
		$query = rtrim($query, ',');
		$wpdb->query($query);
	}
	$options = Jigoshop_Base::get_options();
	$options->add('jigoshop_address_1', $options->get('jigoshop_address_line1'));
	$options->add('jigoshop_address_2', $options->get('jigoshop_address_line2'));
	$options->delete('jigoshop_address_line1');
	$options->delete('jigoshop_address_line2');
	// Set default customer country
	$options->add('jigoshop_default_country_for_customer', $options->get('jigoshop_default_country'));
}

function jigoshop_upgrade_1_10_3()
{
	$options = Jigoshop_Base::get_options();
	$options->add('jigoshop_country_base_tax', 'billing_country');
}

function jigoshop_upgrade_1_10_6()
{
	/** @var WP_Rewrite $wp_rewrite */
	global $wp_rewrite;
	$wp_rewrite->flush_rules(true);
}

function jigoshop_upgrade_1_13_3()
{
	$args = array(
		'post_type' => 'shop_email',
		'post_status' => 'publish',
	);

	$emails_array = get_posts($args);
	if (empty($emails_array)) {
		do_action('jigoshop_install_emails');
	}
}

function jigoshop_upgrade_1_16_0()
{
	wp_insert_term('waiting-for-payment', 'shop_order_status');
}

function jigoshop_upgrade_1_16_1()
{
	$options = Jigoshop_Base::get_options();
	$options->add('jigoshop_enable_html_emails', 'no');
	$options->update_options();

	// Remove unnecessary Shop Cache experiment
	@unlink(JIGOSHOP_DIR.'/jigoshop-shop-cache.php');
}
