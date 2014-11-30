<?php

namespace Jigoshop\Core;

use Jigoshop\Core;
use Monolog\Registry;
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
			Registry::getInstance('jigoshop')->addNotice('Installing Jigoshop.');
			$this->_createTables();
			$this->_createPages();
			$this->cron->clear();
		}

		$this->wp->flushRewriteRules();
		$this->wp->updateSiteOption('jigoshop_database_version', self::DB_VERSION);
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
		if (!$wpdb->query($query)) {
			Registry::getInstance('jigoshop')->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_tax', $wpdb->last_error));
		}

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
		if (!$wpdb->query($query)) {
			Registry::getInstance('jigoshop')->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_tax_location', $wpdb->last_error));
		}

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
				FOREIGN KEY item_order (order_id) REFERENCES {$wpdb->posts} (ID) ON DELETE CASCADE
			) {$collate};
		";
		if (!$wpdb->query($query)) {
			Registry::getInstance('jigoshop')->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_order_item', $wpdb->last_error));
		}

		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_order_item_meta (
				item_id INT,
				meta_key VARCHAR(255) NOT NULL,
				meta_value VARCHAR(255) NOT NULL,
				PRIMARY KEY id (item_id, meta_key),
				FOREIGN KEY order_item (item_id) REFERENCES {$wpdb->prefix}jigoshop_order_item (id) ON DELETE CASCADE
			) {$collate};
		";
		if (!$wpdb->query($query)) {
			Registry::getInstance('jigoshop')->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_order_item_meta', $wpdb->last_error));
		}

		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_attribute (
				id INT(9) NOT NULL AUTO_INCREMENT,
				is_local INT UNSIGNED DEFAULT 1,
				slug VARCHAR(255) NOT NULL,
				label VARCHAR(255) NOT NULL,
				type INT NOT NULL,
				PRIMARY KEY id (id)
			) {$collate};
		";
		if (!$wpdb->query($query)) {
			Registry::getInstance('jigoshop')->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_attribute', $wpdb->last_error));
		}

		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_attribute_option (
				id INT(9) NOT NULL AUTO_INCREMENT,
				attribute_id INT(9),
				label VARCHAR(255) NOT NULL,
				value VARCHAR(255) NOT NULL,
				PRIMARY KEY id (id),
				UNIQUE KEY attribute_value (attribute_id, value),
				FOREIGN KEY product_attribute (attribute_id) REFERENCES {$wpdb->prefix}jigoshop_attribute (id) ON DELETE CASCADE
			) {$collate};
		";
		if (!$wpdb->query($query)) {
			Registry::getInstance('jigoshop')->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_attribute_option', $wpdb->last_error));
		}

		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_product_attribute (
				product_id BIGINT UNSIGNED NOT NULL,
				attribute_id INT(9) NOT NULL,
				value VARCHAR(255) NOT NULL,
				PRIMARY KEY id (product_id, attribute_id),
				FOREIGN KEY attribute (attribute_id) REFERENCES {$wpdb->prefix}jigoshop_attribute (id) ON DELETE CASCADE,
				FOREIGN KEY product (product_id) REFERENCES {$wpdb->posts} (ID) ON DELETE CASCADE
			) {$collate};
		";
		if (!$wpdb->query($query)) {
			Registry::getInstance('jigoshop')->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_product_attribute', $wpdb->last_error));
		}

		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_product_attribute_meta (
				id INT(9) NOT NULL AUTO_INCREMENT,
				product_id BIGINT UNSIGNED NOT NULL,
				attribute_id INT(9) NOT NULL,
				meta_key VARCHAR(255) NOT NULL,
				meta_value VARCHAR(255) NOT NULL,
				PRIMARY KEY id (id),
				FOREIGN KEY product_attribute (product_id, attribute_id) REFERENCES {$wpdb->prefix}jigoshop_product_attribute (product_id, attribute_id) ON DELETE CASCADE
			) {$collate};
		";
		if (!$wpdb->query($query)) {
			Registry::getInstance('jigoshop')->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_product_attribute_meta', $wpdb->last_error));
		}

		$wpdb->show_errors();
	}

	private function _createPages()
	{
		// start out with basic page parameters, modify as we go
		$data = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_author' => $this->wp->getCurrentUserId(),
			'post_name' => '',
			'post_content' => '',
			'comment_status' => 'closed',
			'ping_status' => false,
		);

		$this->_createPage(Pages::SHOP, array_merge($data, array(
			'post_title' => __('Shop', 'jigoshop'),
		)));
		$this->_createPage(Pages::CART, array_merge($data, array(
			'post_title' => __('Cart', 'jigoshop'),
		)));
		$this->_createPage(Pages::CHECKOUT, array_merge($data, array(
			'post_title' => __('Checkout', 'jigoshop'),
		)));
		$this->_createPage(Pages::THANK_YOU, array_merge($data, array(
			'post_title' => __('Checkout - thank you', 'jigoshop'),
		)));
		$this->_createPage(Pages::ACCOUNT, array_merge($data, array(
			'post_title' => __('My account', 'jigoshop'),
		)));
	}

	private function _createPage($slug, $data)
	{
		$wpdb = $this->wp->getWPDB();
		$slug = esc_sql(_x($slug, 'page_slug', 'jigoshop'));
		$page_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_status = 'publish' AND post_status <> 'trash' LIMIT 1", $slug));

		if (!$page_id) {
			Registry::getInstance('jigoshop')->addDebug(sprintf('Installing page "%s".', $slug));
			$data['post_name'] = $slug;
			$page_id = $this->wp->wpInsertPost($data);
		}

		$this->options->setPageId($slug, $page_id);
		$this->options->update('advanced.pages.'.$slug, $page_id);
	}
}
