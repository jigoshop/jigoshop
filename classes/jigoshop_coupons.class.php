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
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

class JS_Coupons extends Jigoshop_Base {

	private static $coupons;

	function __construct() {

		if ( !empty( $_GET['unset_coupon'] ))
			$this->remove_coupon( $_GET['unset_coupon'] );

	}

	/**
	 * get an array of all coupon types
	 *
	 * @return  array - the coupon types that are supported
	 * @since   1.3
	 */
	public static function get_coupon_types() {
		$coupon_types = array(
			'fixed_cart'        => __('Cart Discount', 'jigoshop'),
			'percent'           => __('Cart % Discount', 'jigoshop'),
			'fixed_product'     => __('Product Discount', 'jigoshop'),
			'percent_product'   => __('Product % Discount', 'jigoshop')
		);
		return $coupon_types;
	}

	/**
	 * get an array of all coupon fields
	 *
	 * @return  array - the coupon fields with values that indicate custom meta data fields
	 * @since   1.3
	 */
	public static function get_coupon_fields() {
		$couponFields = array(
			'id'                    => false,
			'code'                  => false,
			'type'                  => true,
			'amount'                => true,
			'date_from'             => true,
			'date_to'               => true,
			'usage_limit'           => true,
			'usage'                 => true,
			'free_shipping'         => true,
			'individual_use'        => true,
			'order_total_min'       => true,
			'order_total_max'       => true,
			'include_products'      => true,
			'exclude_products'      => true,
			'include_categories'    => true,
			'exclude_categories'    => true,
			'pay_methods'           => true,
		);
		return $couponFields;
	}

	function get_coupon_post_id( $code ) {
		$args = array(
			'numberposts'	=> -1,
			'orderby'		=> 'post_date',
			'order'			=> 'DESC',
			'post_type'		=> 'shop_coupon',
			'post_status'	=> 'publish'
		);
		$our_coupons = (array) get_posts( $args );
		if ( ! empty( $our_coupons )) foreach ( $our_coupons as $id => $coupon ) {
			if ( $code == $coupon->post_name ) return $coupon->ID;
		}
		return false;
	}

	/**
	 * get all coupons
	 *
	 * @return array - the coupons
	 * @since 0.9.8
	 */
	function get_coupons() {
		if ( empty( self::$coupons ) ) {
			$args = array(
				'numberposts'	=> -1,
				'orderby'		=> 'post_date',
				'order'			=> 'DESC',
				'post_type'		=> 'shop_coupon',
				'post_status'	=> 'publish'
			);
			$our_coupons = (array) get_posts( $args );
			if ( ! empty( $our_coupons )) foreach ( $our_coupons as $id => $coupon ) {

				$values = array();
				$values['id']   = $coupon->ID;
				$values['code'] = $coupon->post_name;

				foreach ( self::get_coupon_fields() as $name => $meta )
					if ( $meta ) $values[$name] = get_post_meta( $coupon->ID, $name, true );
				self::$coupons[$coupon->post_name] = apply_filters( 'jigoshop_get_shop_coupon_data', $values, $coupon->post_name );
			}
		}
		return apply_filters( 'jigoshop_coupons', self::$coupons );
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
		if ( isset( $coupons[$code] )) {
			if ( self::in_date_range( $coupons[$code] ) && self::under_usage_limit( $coupons[$code] ) )
				return $coupons[$code];
		}
		return false;
	}

	/* Remove an applied coupon. */
	function remove_coupon( $code ) {

		if ( !is_array( jigoshop_session::instance()->coupons ) )
			return false;

		/* Loop to find the key of this coupon */
		foreach ( jigoshop_session::instance()->coupons as $key => $coupon ) :

			if ( $code == $coupon ) {
			//	unset(jigoshop_cart::$applied_coupons[$key]);
			//	unset(jigoshop_session::instance()->coupons[$key]);
				unset($_SESSION['jigoshop'][JIGOSHOP_VERSION]['coupons'][$key]);
				return true;
			}

		endforeach;

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
	function is_valid_coupon_for_product( $code, $product ) {

		if ( ! $coupon = self::get_coupon( $code )) return false;

		/* Exclude specific products first. */
		if ( !empty( $coupon['exclude_products'] ) ) :

			if ( in_array( $product['product_id'], $coupon['exclude_products'] ) )
				return false;

			if ( !empty( $product['variation_id'] ) && in_array( $product['variation_id'], $coupon['exclude_products'] ) )
				return false;

		endif;

		/* Exclude specific categories next. */
		if ( !empty( $coupon['exclude_categories'] ) ) :

			$categories  = get_the_terms( $product['product_id'], 'product_cat' );
			if ( ! empty( $categories )) foreach ( $categories as $index => $category ) {
				if ( in_array( $category->term_id, $coupon['exclude_categories'] ) )
					return false;
			}

		endif;

		/* Allow specific products only. */
		if ( !empty( $coupon['include_products'] ) ) :

			if ( in_array( $product['product_id'], $coupon['include_products'] ) )
				return true;

			if ( !empty( $product['variation_id'] ) && in_array( $product['variation_id'], $coupon['include_products'] ) )
				return true;

		endif;

		/* Allow all products in a specific category. */
		if ( !empty( $coupon['include_categories'] ) ) :

			$categories  = get_the_terms( $product['product_id'], 'product_cat' );
			if ( ! empty( $categories )) foreach ( $categories as $index => $category ) {
				if ( in_array( $category->term_id, $coupon['include_categories'] ) )
					return true;
			}

		endif;

		/* If no limits are set on the coupon, allow it to be used. */
		if (   empty( $coupon['include_products'] )
			&& empty( $coupon['exclude_products'] )
			&& empty( $coupon['include_categories'] )
			&& empty( $coupon['exclude_categories'] )
			) {

			return true;
		}

		// some products may have passed the earlier exclusion tests, allow them now if we got this far
		if ( !empty( $coupon['exclude_categories'] ) ) return true;
		if ( !empty( $coupon['exclude_products'] ) ) return true;

		// otherwise we've failed
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
	 * @since 1.3
	 */
	function under_usage_limit( $coupon ) {
		return (empty($coupon['usage_limit']) || (int) $coupon['usage'] < (int) $coupon['usage_limit']);

	}

}

if ( ! empty( $_GET['unset_coupon'] ) ) $coupons = new JS_Coupons();


/** Depreciated */
class jigoshop_coupons extends JS_Coupons {

	public function __construct() {
		_deprecated_function( 'jigoshop_coupons', '1.3', 'JS_Coupons' );
		parent::__construct();
	}

}
