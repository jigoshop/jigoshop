<?php

class JS_Coupons extends Jigoshop_Base
{
	private static $coupons;

	public function __construct()
	{
		add_action('init', array($this, 'check_remove_coupon'));
	}

	public static function check_remove_coupon()
	{
		if (!empty($_GET['unset_coupon'])) {
			self::remove_coupon($_GET['unset_coupon']);
			\Jigoshop\Integration::setShippingRate(null);
		}
	}

	public static function remove_coupon($code)
	{
		$cart = \Jigoshop\Integration::getCart();
		foreach ($cart->getCoupons() as $id => $coupon) {
			/** @var $coupon \Jigoshop\Entity\Coupon */
			if ($coupon->getCode() == $code) {
				$cart->removeCoupon($id);
				break;
			}
		}
	}

	public static function get_coupon_types()
	{
		$service = \Jigoshop\Integration::getCouponService();
		$types = $service->getTypes();

		return apply_filters('jigoshop_coupon_discount_types', $types);
	}

	public static function get_coupon_post_id($code)
	{
		$service = \Jigoshop\Integration::getCouponService();
		$coupon = $service->findByCode($code);

		if ($coupon !== null) {
			return $coupon->getId();
		}

		return false;
	}

	/**
	 * get a coupon containing a specific code and verify the product applies to this coupon
	 * this will usually be called for Coupon type = 'Product Discount' to match the product ID
	 *
	 * @param string $code - the coupon code to retrieve
	 * @param array $product - the Cart $values entry for this product
	 * @return boolean - whether this product is applicable to this coupon based on product ID, variation ID, and dates
	 * @since 1.3
	 */
	public static function is_valid_coupon_for_product($code, $product)
	{
		$coupon = \Jigoshop\Integration::getCouponService()->findByCode($code);
		if ($coupon === null) {
			return false;
		}

		// TODO: Properly fetch product from array
		return $coupon->productMatchesCoupon($product);
	}

	/**
	 * get a coupon containing a specific code
	 * also used to determine if a valid coupon code as false is returned if not
	 * will check coupon dates if entered for a found coupon and if out of date range, coupon is considered invalid
	 *
	 * @param string $code - the coupon code to retrieve
	 * @return array - the stored coupon entry from the coupons array or false if no coupon code exists, or is invalid
	 * @since 0.9.8
	 */
	public static function get_coupon($code)
	{
		$coupon = \Jigoshop\Integration::getCouponService()->findByCode($code);
		if ($coupon === null) {
			return false;
		}

		return self::_formatCoupon($coupon);
	}

	/* Remove an applied coupon. */

	/**
	 * get all coupons
	 *
	 * @return array - the coupons
	 * @since 0.9.8
	 */
	public static function get_coupons()
	{
		if (empty(self::$coupons)) {
			$query = new \WP_Query(array(
				'numberposts' => -1,
				'orderby' => 'post_date',
				'order' => 'DESC',
				'post_type' => \Jigoshop\Core\Types::COUPON,
				'post_status' => 'publish'
			));
			$coupons = \Jigoshop\Integration::getCouponService()->findByQuery($query);

			foreach ($coupons as $coupon) {
				/** @var $coupon \Jigoshop\Entity\Coupon */
				self::$coupons[$coupon->getCode()] = apply_filters('jigoshop_get_shop_coupon_data', self::_formatCoupon($coupon), $coupon->getCode());
			}
		}

		return apply_filters('jigoshop_coupons', self::$coupons);
	}

	/**
	 * get an array of all coupon fields
	 *
	 * @return  array - the coupon fields with values that indicate custom meta data fields
	 * @since   1.3
	 */
	public static function get_coupon_fields()
	{
		$couponFields = array(
			'id' => false,
			'code' => false,
			'type' => true,
			'amount' => true,
			'date_from' => true,
			'date_to' => true,
			'usage_limit' => true,
			'usage' => true,
			'free_shipping' => true,
			'individual_use' => true,
			'order_total_min' => true,
			'order_total_max' => true,
			'include_products' => true,
			'exclude_products' => true,
			'include_categories' => true,
			'exclude_categories' => true,
			'pay_methods' => true,
		);

		return $couponFields;
	}

	/**
	 * determines whether a coupon code is valid by being within allowed dates if dates are entered
	 *
	 * @param array $coupon - the coupon record to check valid dates for
	 * @return boolean - whether coupon is valid based on dates
	 * @since 0.9.9.1
	 */
	public static function in_date_range($coupon)
	{

		$date_from = (int)$coupon['date_from'];
		$date_to = (int)$coupon['date_to'];
		$current_time = time();

		if ($date_to == 0 && $date_from == 0) {
			return true;
		}

		if ($date_from == 0 || ($date_from > 0 && $date_from < $current_time)) {
			if ($date_to == 0 || $date_to > $current_time) {
				return true;
			}
		}

		return false;
	}

	/**
	 * determines whether a coupon code is valid by checking if it has a usage limit, and if that limit has been passed
	 *
	 * @param array $coupon - the coupon record to check limit for
	 * @return boolean - whether coupon is valid based on usage limit
	 * @since 1.3
	 */
	public static function under_usage_limit($coupon)
	{
		return (empty($coupon['usage_limit']) || (int)$coupon['usage'] < (int)$coupon['usage_limit']);

	}

	public static function has_coupons()
	{
		$coupons = self::get_coupons();

		return !empty($coupons);
	}

	/**
	 * @param $coupon \Jigoshop\Entity\Coupon Coupon to format.
	 * @return array Jigoshop 1.x coupon format.
	 */
	private static function _formatCoupon($coupon)
	{
		return array(
			'id' => $coupon->getId(),
			'code' => $coupon->getCode(),
			'type' => $coupon->getType(),
			'amount' => $coupon->getAmount(),
			'date_from' => $coupon->getFrom() ? $coupon->getFrom()->getTimestamp() : 0,
			'date_to' => $coupon->getTo() ? $coupon->getTo()->getTimestamp() : 0,
			'usage_limit' => $coupon->getUsageLimit(),
			'usage' => $coupon->getUsage(),
			'free_shipping' => $coupon->isFreeShipping(),
			'individual_use' => $coupon->isIndividualUse(),
			'order_total_min' => $coupon->getOrderTotalMinimum(),
			'order_total_max' => $coupon->getOrderTotalMaximum(),
			'include_products' => $coupon->getProducts(),
			'exclude_products' => $coupon->getExcludedProducts(),
			'include_categories' => $coupon->getCategories(),
			'exclude_categories' => $coupon->getExcludedCategories(),
			'pay_methods' => $coupon->getPaymentMethods(),
		);
	}
}
