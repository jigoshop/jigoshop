<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Service\EmailServiceInterface;
use WPAL\Wordpress;

class Emails implements Tool
{
	const ID = 'jigoshop_emails_migration';

	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
	}

	/**
	 * @return string Tool ID.
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * Shows migration tool in Migration tab.
	 */
	public function display()
	{
		Render::output('admin/migration/emails', array());
	}

	/**
	 * Migrates data from old format to new one.
	 */
	public function migrate()
	{
		$wpdb = $this->wp->getWPDB();

		$query = $wpdb->prepare("
			SELECT DISTINCT p.ID, pm.* FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE p.post_type IN (%s) AND p.post_status <> %s",
			array('shop_email', 'auto-draft'));
		$emails = $wpdb->get_results($query);

		for ($i = 0, $endI = count($emails); $i < $endI;) {
			$email = $emails[$i];

			// Update columns
			do {
				$key = $this->_transformKey($emails[$i]->meta_key);

				if ($key !== null) {
					$wpdb->query($wpdb->prepare(
						"UPDATE {$wpdb->postmeta} SET meta_value = %s, meta_key = %s WHERE meta_id = %d;",
						array(
							$this->_transform($emails[$i]->meta_key, $emails[$i]->meta_value),
							$key,
							$emails[$i]->meta_id,
						)
					));
				}
				$i++;
			} while ($i < $endI && $emails[$i]->ID == $email->ID);
		}
	}

	private function _transform($key, $value)
	{
		switch ($key) {
			case 'jigoshop_email_actions':
				$value = unserialize($value);
				return serialize(array_map(function($item){
					switch ($item) {
						case 'admin_order_status_pending_to_on-hold':
							return 'admin_order_status_pending_to_on_hold';
						case 'customer_order_status_pending_to_on-hold':
							return 'customer_order_status_pending_to_on_hold';
						case 'customer_order_status_on-hold_to_processing':
							return 'customer_order_status_on_hold_to_processing';
						case 'product_on_backorder_notification':
							return 'product_on_backorders_notification';
						default:
							return $item;
					}
				}, $value));
			default:
				return $value;
		}
	}

	private function _transformKey($key)
	{
		switch ($key) {
			case 'jigoshop_email_subject':
				return 'subject';
			case 'jigoshop_email_actions':
				return 'actions';
			default:
				return null;
		}
	}
}
