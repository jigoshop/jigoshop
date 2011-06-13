<?php
/**
 * Jigoshop coupons
 * @class 		jigoshop_coupons
 * 
 * The JigoShop coupons class gets coupon data from storage
 *
 * @author 		Jigowatt
 * @category 	Classes
 * @package 	JigoShop
 */
class jigoshop_coupons {
	
	/** get coupons from the options database */
	function get_coupons() {
		$coupons = get_option('jigoshop_coupons') ? $coupons = (array) get_option('jigoshop_coupons') : $coupons = array();
		return $coupons;
	}
	
	/** get coupon with $code */
	function get_coupon($code) {
		$coupons = get_option('jigoshop_coupons') ? $coupons = (array) get_option('jigoshop_coupons') : $coupons = array();
		if (isset($coupons[$code])) return $coupons[$code];
		return false;
	}
	
}