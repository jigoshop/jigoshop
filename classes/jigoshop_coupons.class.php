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
 * @package    Jigoshop
 * @category   Orders
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
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
			if ( self::in_date_range( $coupons[$code] ) ) return $coupons[$code];
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
		$valid = false;
		$coupon = self::get_coupon($code);
		if ( $coupon && sizeof($coupon['products'])>0) :
			if ( in_array( $product['product_id'], $coupon['products'] )) :
				$valid = true;
			endif;
			if ( $product['variation_id'] <> '' ) :
				if ( in_array( $product['variation_id'], $coupon['products'] )) :
					$valid = true;
				endif;
			endif;
		endif;
		return $valid;
	}

	/**
	 * determines whether a coupon code is valid by being within allowed dates if dates are entered
	 *
	 * @param array $coupon - the coupon record to check valid dates for
	 * @return boolean - whether coupon is valid based on dates
	 * @since 0.9.9.1
	 */
	function in_date_range( $coupon ) {
		$in_range = false;
		$date_from = (int)$coupon['date_from'];
		$date_to = (int)$coupon['date_to'];
		$current_time = strtotime( 'NOW' );
		if ( $date_to == 0 && $date_from == 0 ) $in_range = true;
		else if ( $date_from == 0 || ( $date_from > 0 && $date_from < $current_time )) :
			if ( $date_to == 0 || $date_to > $current_time ) $in_range = true;
		endif;
		return $in_range;
	}
	
}