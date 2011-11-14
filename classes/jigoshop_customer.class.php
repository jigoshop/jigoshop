<?php
/**
 * Customer Class
 * 
 * The JigoShop custoemr class handles storage of the current customer's data, such as location.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Customer
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

class jigoshop_customer extends jigoshop_singleton {
	
	/** constructor */
	protected function __construct() {
		
		if ( !isset($_SESSION['customer']) ) :
			
			$default = get_option('jigoshop_default_country');
        	if (strstr($default, ':')) :
        		$country = current(explode(':', $default));
        		$state = end(explode(':', $default));
        	else :
        		$country = $default;
        		$state = '';
        	endif;
			$data = array(
				'country' => $country,
				'state' => $state,
				'postcode' => '',
				'shipping_country' => $country,
				'shipping_state' => $state,
				'shipping_postcode' => ''
			);			
			$_SESSION['customer'] = $data;
			
		endif;
		
	}
	
    /** Is customer outside base country? */
	public static function is_customer_outside_base() {
		$outside = false;
		if ( self::get_country() ) :
			
			$shopcountry = jigoshop_countries::get_base_country();
			$shopstate = jigoshop_countries::get_base_state();
			
			if ( $shopcountry !== self::get_country() ) $outside = true;
			if ( $shopstate !== self::get_state() ) $outside = true;
		endif;
		return $outside;
	}
	
	/** Gets the state from the current session */
	public static function get_state() {
		if (isset($_SESSION['customer']['state'])) return $_SESSION['customer']['state'];
	}
	
	/** Gets the country from the current session */
	public static function get_country() {
		if (isset($_SESSION['customer']['country'])) return $_SESSION['customer']['country'];
	}
	
	/** Gets the postcode from the current session */
	public static function get_postcode() {
		if (isset($_SESSION['customer']['postcode'])) return strtolower(str_replace(' ', '', $_SESSION['customer']['postcode']));
	}
	
	/** Gets the state from the current session */
	public static function get_shipping_state() {
		if (isset($_SESSION['customer']['shipping_state'])) return $_SESSION['customer']['shipping_state'];
	}
	
	/** Gets the country from the current session */
	public static function get_shipping_country() {
		if (isset($_SESSION['customer']['shipping_country'])) return $_SESSION['customer']['shipping_country'];
	}
	
	/** Gets the postcode from the current session */
	public static function get_shipping_postcode() {
		if (isset($_SESSION['customer']['shipping_postcode'])) return strtolower(str_replace(' ', '', $_SESSION['customer']['shipping_postcode']));
	}
	
	/** Sets session data for the location */
	public static function set_location( $country, $state, $postcode = '' ) {
		$data = (array) $_SESSION['customer'];
		
		$data['country'] = $country;
		$data['state'] = $state;
		$data['postcode'] = $postcode;
		
		$_SESSION['customer'] = $data;
	}
	
	/** Sets session data for the country */
	public static function set_country( $country ) {
		$_SESSION['customer']['country'] = $country;
	}
	
	/** Sets session data for the state */
	public static function set_state( $state ) {
		$_SESSION['customer']['state'] = $state;
	}
	
	/** Sets session data for the postcode */
	public static function set_postcode( $postcode ) {
		$_SESSION['customer']['postcode'] = $postcode;
	}
	
	/** Sets session data for the location */
	public static function set_shipping_location( $country, $state = '', $postcode = '' ) {
		$data = (array) $_SESSION['customer'];
		
		$data['shipping_country'] = $country;
		$data['shipping_state'] = $state;
		$data['shipping_postcode'] = $postcode;
		
		$_SESSION['customer'] = $data;
	}
	
	/** Sets session data for the country */
	public static function set_shipping_country( $country ) {
		$_SESSION['customer']['shipping_country'] = $country;
	}
	
	/** Sets session data for the state */
	public static function set_shipping_state( $state ) {
		$_SESSION['customer']['shipping_state'] = $state;
	}
	
	/** Sets session data for the postcode */
	public static function set_shipping_postcode( $postcode ) {
		$_SESSION['customer']['shipping_postcode'] = $postcode;
	}
	
	/**
	 * Gets a user's downloadable products if they are logged in
	 *
	 * @return   array	downloads	Array of downloadable products
	 */
	public static function get_downloadable_products() {
		
		global $wpdb;
		
		$downloads = array();
		
		if (is_user_logged_in()) :
		
			$jigoshop_orders = &new jigoshop_orders();
			$jigoshop_orders->get_customer_orders( get_current_user_id() );
			if ($jigoshop_orders->orders) foreach ($jigoshop_orders->orders as $order) :
				if ( $order->status == 'completed' ) {
					$results = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."jigoshop_downloadable_product_permissions WHERE order_key = \"".$order->order_key."\" AND user_id = ".get_current_user_id().";" );
					$user_info = get_userdata(get_current_user_id());
					if ($results) foreach ($results as $result) :
							$_product = &new jigoshop_product( $result->product_id );
							if ($_product->exists) :
								$download_name = $_product->get_title();
							else :
								$download_name = '#' . $result->product_id;
							endif;
							$downloads[] = array(
								'download_url' => add_query_arg('download_file', $result->product_id, add_query_arg('order', $result->order_key, add_query_arg('email', $user_info->user_email, home_url()))),
								'product_id' => $result->product_id,
								'download_name' => $download_name,
								'order_key' => $result->order_key,
								'downloads_remaining' => $result->downloads_remaining
							);
					endforeach;
				}
			endforeach;
		
		endif;
		
		return $downloads;
		
	}
	
}