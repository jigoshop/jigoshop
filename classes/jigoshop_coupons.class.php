<?php
/**
 * Coupons Class
 *
 * The JigoShop coupons class gets coupon data from storage
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Orders
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class jigoshop_coupons {

	/**
	 * get coupons from the options database
	 *
	 * @return array - the stored coupons array if any or an empty array otherwise
	 * @since 0.9.8
	 */
	function get_coupons() {
		return get_option('jigoshop_coupons') ? $coupons = (array) get_option('jigoshop_coupons') : $coupons = array();
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
	function get_coupon( $code ) {
		$coupons = self::get_coupons();
		if ( isset( $coupons[$code] )) :
			if ( self::in_date_range( $coupons[$code] ) && self::under_usage_limit( $coupons[$code] ) ) return $coupons[$code];
		endif;

		return false;
	}

	/**
	 * get a coupon containing a specific code and verify the product applies to this coupon
	 * this will usually be called for Coupon type = 'Product Discount' to match the product ID
	 *
	 * @param string $code - the coupon code to retrieve
	 * @param array $product - the Cart $values entry for this product
	 * @return boolean - whether this product is applicable to this coupon based on product ID, variation ID, and dates
	 * @since 0.9.9.1
	 */
	function is_valid_product( $code, $product ) {

		$product_id = !empty($product['variation_id']) ? 'variation_id' : 'product_id';
		$category   = reset(wp_get_post_terms($product['product_id'], 'product_cat'));
		$coupon     = self::get_coupon($code);

		if ( $coupon && sizeof($coupon['products']) > 0 || !empty($coupon['category']) )
			return ( in_array( $product[$product_id], $coupon['products'] ) || $coupon['category'] == $category->term_id );

		return false;
	}

	/**
	 * determines whether a coupon code is valid by being within allowed dates if dates are entered
	 *
	 * @param array $coupon - the coupon record to check valid dates for
	 * @return boolean - whether coupon is valid based on dates
	 * @since 0.9.9.1
	 */
	function in_date_range( $coupon ) {

		$date_from    = (int)$coupon['date_from'];
		$date_to      = (int)$coupon['date_to'];
		$current_time = strtotime( 'NOW' );

		if ( $date_to == 0 && $date_from == 0 )
			return true;

		if ( $date_from == 0 || ( $date_from > 0 && $date_from < $current_time ) )
			if ( $date_to == 0 || $date_to > $current_time )
				return true;

		return false;
	}

	/**
	 * determines whether a coupon code is valid by checking if it has a usage limit, and if that limit has been passed
	 *
	 * @param array $coupon - the coupon record to check limit for
	 * @return boolean - whether coupon is valid based on usage limit
	 * @since 1.0
	 */
	function under_usage_limit( $coupon ) {

		return (empty($coupon['usage_limit']) || (int) $coupon['usage'] < (int) $coupon['usage_limit']) ? true : false;

	}

}