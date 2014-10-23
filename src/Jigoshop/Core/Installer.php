<?php

namespace Jigoshop\Core;

use Jigoshop\Core;
use WPAL\Wordpress;

/**
 * Jigoshop installer class.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class Installer
{
	const DB_VERSION = 1;

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Core\Cron */
	private $cron;

	public function __construct(Wordpress $wp, Options $options, Cron $cron)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->cron = $cron;
	}

	public function install()
	{
		$db = false;//$this->wp->getOption('jigoshop_database_version');

		if ($db === false) {
			$this->_createTables();
			$this->_createPages();
			$this->cron->clear();
		}

		// TODO: Remove flush_rewrite_rules() call in order to make Jigoshop testable
		flush_rewrite_rules(false);
		// TODO: Remove update_site_option() call in order to make Jigoshop testable
		update_site_option('jigoshop_database_version', self::DB_VERSION);
	}

	private function _createTables()
	{
		$wpdb = $this->wp->getWPDB();
		$wpdb->hide_errors();

		$collate = '';
		if ($wpdb->has_cap('collation')) {
			if (!empty($wpdb->charset)) {
				$collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}
			if (!empty($wpdb->collate)) {
				$collate .= " COLLATE {$wpdb->collate}";
			}
		}

		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_tax (
				id INT NOT NULL AUTO_INCREMENT,
				class VARCHAR(255) NOT NULL,
				label VARCHAR(255) NOT NULL,
				rate DOUBLE NOT NULL,
				PRIMARY KEY id (id)
			) {$collate};
		";
		$wpdb->query($query);
		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_tax_location (
				id INT NOT NULL AUTO_INCREMENT,
				tax_id INT NOT NULL,
				country VARCHAR(255) NOT NULL,
				state VARCHAR(255),
				postcode VARCHAR(255),
				PRIMARY KEY id (id),
				FOREIGN KEY tax (tax_id) REFERENCES {$wpdb->prefix}jigoshop_tax (id) ON DELETE CASCADE,
				UNIQUE KEY tax_definition (tax_id, country, state, postcode)
			) {$collate};
		";
		$wpdb->query($query);

		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_order_item (
				id INT NOT NULL AUTO_INCREMENT,
				order_id BIGINT(20) UNSIGNED,
				product_id BIGINT(20) UNSIGNED,
				product_type VARCHAR(255) NOT NULL,
				title VARCHAR(255) NOT NULL,
				price DECIMAL(12,4) NOT NULL,
				tax DECIMAL(12,4) NOT NULL,
				quantity INT NOT NULL DEFAULT 1,
				cost DECIMAL(13,4) NOT NULL,
				PRIMARY KEY id (id),
				FOREIGN KEY item_product (product_id) REFERENCES {$wpdb->posts} (ID) ON DELETE SET NULL,
				FOREIGN KEY item_order (order_id) REFERENCES {$wpdb->posts} (ID) ON DELETE CASCADE,
				UNIQUE KEY item_definition (order_id, product_id)
			) {$collate};
		";
		$wpdb->query($query);
		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_order_item_meta (
				item_id INT,
				meta_key VARCHAR(255) NOT NULL,
				meta_value VARCHAR(255) NOT NULL,
				PRIMARY KEY id (item_id, meta_key),
				FOREIGN KEY order_item (item_id) REFERENCES {$wpdb->prefix}jigoshop_order_item (id) ON DELETE CASCADE
			) {$collate};
		";
		$wpdb->query($query);
		/*
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_attribute (
				id INT(9) NOT NULL AUTO_INCREMENT,
				attribute_name VARCHAR(255) NOT NULL,
				attribute_label LONGTEXT NULL,
				attribute_type INT NOT NULL,
				attribute_order INT NOT NULL,
				PRIMARY KEY id (attribute_id)
			) {$collate};
		 */
		// TODO: Is attribute_meta table needed?
//		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_termmeta (
//			meta_id BIGINT(20) NOT NULL AUTO_INCREMENT,
//			jigoshop_term_id BIGINT(20) NOT NULL,
//			meta_key VARCHAR(255) NULL,
//			meta_value LONGTEXT NULL,
//			PRIMARY KEY id (meta_id)
//		) {$collate}";
	}

	private function _createPages()
	{
		// start out with basic page parameters, modify as we go
		$data = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_author' => get_current_user_id(),
			'post_name' => '',
			'post_content' => '',
			'comment_status' => 'closed',
		);

		$this->_createPage(Pages::SHOP, array_merge($data, array(
			'page_title' => __('Shop', 'jigoshop'),
		)));
		$this->_createPage(Pages::CART, array_merge($data, array(
			'page_title' => __('Cart', 'jigoshop'),
		)));
//		$this->_createPage(Pages::CHECKOUT, array_merge($data, array(
//			'page_title' => __('My account', 'jigoshop'),
//			'post_content' => '[jigoshop_checkout]',
//		)));
//		$this->_createPage(Pages::ACCOUNT, array_merge($data, array(
//			'page_title' => __('My account', 'jigoshop'),
//			'post_content' => '[jigoshop_my_account]',
//		)));
//		$this->_createPage(Pages::ORDER_TRACKING, array_merge($data, array(
//			'page_title' => __('Track your order', 'jigoshop'),
//			'post_content' => '[jigoshop_order_tracking]',
//		)));
	}

	private function _createPage($slug, $data)
	{
		$wpdb = $this->wp->getWPDB();
		$slug = esc_sql(_x($slug, 'page_slug', 'jigoshop'));
		$page_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_status = 'publish' AND post_status <> 'trash' LIMIT 1", $slug));

		if (!$page_id) {
			$data['post_name'] = $slug;
			// TODO: Remove wp_insert_post() call in order to make Jigoshop testable
			$page_id = wp_insert_post($data);
		}

		$this->options->setPageId($slug, $page_id);
	}
}
