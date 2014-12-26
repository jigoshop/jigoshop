<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Entity\Coupon;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Service\CouponServiceInterface;
use WPAL\Wordpress;

class Coupons implements Tool
{
	const ID = 'jigoshop_coupons_migration';

	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var CouponServiceInterface */
	private $couponService;

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options, CouponServiceInterface $couponService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->couponService = $couponService;
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
		Render::output('admin/migration/coupons', array());
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
				WHERE p.post_type IN (%s, %s) AND p.post_status <> %s",
			array('shop_coupon', 'auto-draft'));
		$coupons = $wpdb->get_results($query);

		for ($i = 0, $endI = count($coupons); $i < $endI;) {
			$coupon = $coupons[$i];

			// Update columns
			do {
				$key = $this->_transformKey($coupons[$i]['meta_key']);

				if (!empty($key)) {
					$wpdb->query($wpdb->prepare(
						"UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND meta_id = %d",
						array(
							$this->_transform($coupons[$i]['meta_key'], $coupons[$i]['meta_value']),
							$key,
							$coupons[$i]['meta_id'],
						)
					));
				}
				$i++;
			} while ($i < $endI && $coupons[$i]['ID'] == $coupon['ID']);
		}
	}

	private function _transform($key, $value)
	{
		switch ($key) {
			case 'type':
				switch ($value) {
					case 'fixed_product':
						return Coupon::FIXED_PRODUCT;
					case 'percent_product':
						return Coupon::PERCENT_PRODUCT;
					case 'percent':
						return Coupon::PERCENT_CART;
					default:
						return Coupon::FIXED_CART;
				}
			default:
				return $value;
		}
	}

	private function _transformKey($key)
	{
		switch ($key) {
			case 'date_from':
				return 'from';
			case 'date_to':
				return 'to';
			case 'order_total_min':
				return 'order_total_minimum';
			case 'order_total_max':
				return 'order_total_maximum';
			case 'include_products':
				return 'products';
			case 'exclude_products':
				return 'excluded_products';
			case 'include_categories':
				return 'categories';
			case 'exclude_categories':
				return 'excluded_categories';
			case 'pay_methods':
				return 'payment_methods';
			default:
				return $key;
		}
	}
}
