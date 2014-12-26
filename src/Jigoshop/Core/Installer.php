<?php

namespace Jigoshop\Core;

use Jigoshop\Core;
use Jigoshop\Frontend\Pages;
use Jigoshop\Service\EmailServiceInterface;
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
	/** @var EmailServiceInterface */
	private $emailService;
	/** @var array */
	private $initializers = array();

	public function __construct(Wordpress $wp, Options $options, Cron $cron, EmailServiceInterface $emailService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->cron = $cron;
		$this->emailService = $emailService;
	}

	/**
	 * Adds new initializer to Jigoshop installation.
	 * @param Installer\Initializer $initializer
	 */
	public function addInitializer(Core\Installer\Initializer $initializer)
	{
		$this->initializers[] = $initializer;
	}

	public function install()
	{
		$db = $this->wp->getOption('jigoshop_database_version');

		if ($db === false) {
			Registry::getInstance('jigoshop')->addNotice('Installing Jigoshop.');
			$this->_createTables();
			$this->_createPages();
			$this->_installEmails();

			foreach ($this->initializers as $initializer) {
				/** @var $initializer Core\Installer\Initializer */
				$initializer->initialize($this->wp);
			}

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
				is_compund INT NOT NULL DEFAULT 0,
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
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_order_tax (
				order_id BIGINT(20) UNSIGNED,
				tax_class VARCHAR(255) NOT NULL,
				rate decimal(9,4) NOT NULL,
				is_compund INT NOT NULL DEFAULT 0,
				PRIMARY KEY id (order_id, tax_class),
				FOREIGN KEY order (order_id) REFERENCES {$wpdb->posts} (ID) ON DELETE CASCADE
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

	private function _installEmails()
	{
		$default_emails = array(
			'new_order_admin_notification',
			'customer_order_status_pending_to_processing',
			'customer_order_status_pending_to_on_hold',
			'customer_order_status_on-hold_to_processing',
			'customer_order_status_completed',
			'customer_order_status_refunded',
			'send_customer_invoice',
			'low_stock_notification',
			'no_stock_notification',
			'product_on_backorder_notification'
		);
		$invoice = '==============================<wbr />==============================
		Order details:
		<span class="il">ORDER</span> [order_number]                                              Date: [order_date]
		==============================<wbr />==============================

		[order_items]

		Subtotal:                     [subtotal]
		Shipping:                     [shipping_cost] via [shipping_method]
		Total:                        [total]

		------------------------------<wbr />------------------------------<wbr />--------------------
		CUSTOMER DETAILS
		------------------------------<wbr />------------------------------<wbr />--------------------
		Email:                        <a href="mailto:[billing_email]">[billing_email]</a>
		Tel:                          [billing_phone]

		------------------------------<wbr />------------------------------<wbr />--------------------
		BILLING ADDRESS
		------------------------------<wbr />------------------------------<wbr />--------------------
		[billing_first_name] [billing_last_name]
		[billing_address_1], [billing_address_2], [billing_city]
		[billing_state], [billing_country], [billing_postcode]

		------------------------------<wbr />------------------------------<wbr />--------------------
		SHIPPING ADDRESS
		------------------------------<wbr />------------------------------<wbr />--------------------
		[shipping_first_name] [shipping_last_name]
		[shipping_address_1], [shipping_address_2], [shipping_city]
		[shipping_state], [shipping_country], [shipping_postcode]';

		$title = '';
		$message = '';
		$post_title = '';
		foreach ($default_emails as $email) {
			switch ($email) {
				case 'new_order_admin_notification':
					$post_title = 'New order admin notification';
					$title = '[[shop_name]] New Customer Order - [order_number]';
					$message = 'You have received an order from [billing_first_name] [billing_last_name]. Their order is as follows:<br/>'.$invoice;
					break;
				case 'customer_order_status_pending_to_on_hold':
					$post_title = 'Customer order status pending to on-hold';
					$title = '[[shop_name]] Order Received';
					$message = 'Thank you, we have received your order. Your order\'s details are below:<br/>'.$invoice;
					break;
				case 'customer_order_status_pending_to_processing' :
					$post_title = 'Customer order status pending to processing';
					$title = '[[shop_name]] Order Received';
					$message = 'Thank you, we are now processing your order. Your order\'s details are below:<br/>'.$invoice;
					break;
				case 'customer_order_status_on_hold_to_processing' :
					$post_title = 'Customer order status on-hold to processing';
					$title = '[[shop_name]] Order Received';
					$message = 'Thank you, we are now processing your order. Your order\'s details are below:<br/>'.$invoice;
					break;
				case 'customer_order_status_completed' :
					$post_title = 'Customer order status completed';
					$title = '[[shop_name]] Order Complete';
					$message = 'Your order is complete. Your order\'s details are below:<br/>'.$invoice;
					break;
				case 'customer_order_status_refunded' :
					$post_title = 'Customer order status refunded';
					$title = '[[shop_name]] Order Refunded';
					$message = 'Your order has been refunded. Your order\'s details are below:<br/>'.$invoice;
					break;
				case 'send_customer_invoice' :
					$post_title = 'Send customer invoice';
					$title = 'Invoice for Order: [order_number]';
					$message = $invoice;
					break;
				case 'low_stock_notification' :
					$post_title = 'Low stock notification';
					$title = '[[shop_name]] Product low in stock';
					$message = '#[product_id] [product_name] ([sku]) is low in stock.';
					break;
				case 'no_stock_notification' :
					$post_title = 'No stock notification';
					$title = '[[shop_name]] Product out of stock';
					$message = '#[product_id] [product_name] ([sku]) is out of stock.';
					break;
				case 'product_on_backorder_notification' :
					$post_title = 'Product on backorder notification';
					$title = '[[shop_name]] Product Backorder on Order: [order_number].';
					$message = '#[product_id] [product_name] ([sku]) was found to be on backorder.<br/>'.$invoice;
					break;
			}
			$post_data = array(
				'post_content' => $message,
				'post_title' => $post_title,
				'post_status' => 'publish',
				'post_type' => 'shop_email',
				'post_author' => 1,
				'ping_status' => 'closed',
				'comment_status' => 'closed',
			);
			$post_id = $this->wp->wpInsertPost($post_data);
			$this->wp->updatePostMeta($post_id, 'general.email_subject', $title);
			if ($email == 'new_order_admin_notification') {
				$this->emailService->addTemplate($post_id, array(
					'admin_order_status_pending_to_processing',
					'admin_order_status_pending_to_completed',
					'admin_order_status_pending_to_on_hold'
				));
				$this->wp->updatePostMeta($post_id, 'general.email_actions', array(
					'admin_order_status_pending_to_processing',
					'admin_order_status_pending_to_completed',
					'admin_order_status_pending_to_on_hold'
				));
			} else {
				$this->emailService->addTemplate($post_id, array($email));
				$this->wp->updatePostMeta($post_id, 'general.email_actions', array($email));
			}
		}
	}
}
