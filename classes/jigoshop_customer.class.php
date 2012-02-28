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
		
		if ( !isset( jigoshop_session::instance()->customer ) ) :
			
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
			jigoshop_session::instance()->customer  = $data;
			
		endif;
		
	}
	
    /** 
     * Is customer shipping outside base, but within the same country? This is
     * used to determine how to apply taxes. Also, it no country is set, assume
     * shipping is going to base country.
     */
	public static function is_customer_shipping_outside_base() {
		$outside = false;
        $shipping_country = self::get_shipping_country();
        
        // if no shipping country is set, then assume customer will ship to the shop base
        // country until customer sets the shipping country.
		if ( $shipping_country ) :
           
            // only check if it's a country with states. Otherwise always return false, as
            // we don't care about calculating taxes for a customer outside of the base 
            // country.
           if (jigoshop_countries::country_has_states($shipping_country)) :
                
                $shopcountry = jigoshop_countries::get_base_country();
                $shopstate = jigoshop_countries::get_base_state();
            
                // taxes only apply if the customer is shipping in the same country. If the customer is 
                // shipping outside of the shop country, then taxes do not apply.
                if ( $shopcountry === self::get_shipping_country() && $shopstate !== self::get_shipping_state() ) :
                    $outside = true;
                endif;
            endif;
		endif;
		return $outside;
	}
	
	/** Gets the state from the current session */
	public static function get_state() {
		if (self::get_customer_session('state')) return self::get_customer_session('state');
	}
	
	/** Gets the country from the current session */
	public static function get_country() {
		if (self::get_customer_session('country')) return self::get_customer_session('country');
	}
	
	/** Gets the postcode from the current session */
	public static function get_postcode() {
		if ( self::get_customer_session('postcode')) return strtolower(str_replace(' ', '', self::get_customer_session('postcode')));
	}
	
	/** Gets the state from the current session */
	public static function get_shipping_state() {
		if (self::get_customer_session('shipping_state')) return self::get_customer_session('shipping_state');
	}
	
	/** Gets the country from the current session */
	public static function get_shipping_country() {
		if (self::get_customer_session('shipping_country'))	return self::get_customer_session('shipping_country');
	}
	
	/** Gets the postcode from the current session */
	public static function get_shipping_postcode() {
        if (self::get_customer_session('shipping_postcode')) return strtolower(str_replace(' ', '', self::get_customer_session('shipping_postcode')));
	}
	
	/** Sets session data for the location */
	public static function set_location( $country, $state, $postcode = '' ) {
		$data = (array) jigoshop_session::instance()->customer;
		$data['country'] = $country;
		$data['state'] = $state;
		$data['postcode'] = $postcode;
		jigoshop_session::instance()->customer = $data;
	}
	
	/** Sets session data for the country */
	public static function set_country( $country ) {
        self::set_customer_session('country', $country);
	}
	
	/** Sets session data for the state */
	public static function set_state( $state ) {
        self::set_customer_session('state', $state);
	}
	
	/** Sets session data for the postcode */
	public static function set_postcode( $postcode ) {
        self::set_customer_session('postcode', $postcode);
	}
	
	/** Sets session data for the location */
	public static function set_shipping_location( $country, $state = '', $postcode = '' ) {
		$data = (array) jigoshop_session::instance()->customer;
		$data['shipping_country'] = $country;
		$data['shipping_state'] = $state;
		$data['shipping_postcode'] = $postcode;
		jigoshop_session::instance()->customer = $data;
	}
	
	/** Sets session data for the country */
	public static function set_shipping_country( $country ) {
        self::set_customer_session('shipping_country', $country);
	}
	
	/** Sets session data for the state */
	public static function set_shipping_state( $state ) {
        self::set_customer_session('shipping_state', $state);
	}
	
	/** Sets session data for the postcode */
	public static function set_shipping_postcode( $postcode ) {
        self::set_customer_session('shipping_postcode', $postcode);
	}
    
    /**
     * Setting the customer session for country, postcode, and state
     * @param string $array_index the index to set on the session array
     * @param string $value postcode, country, or state
     */
    private static function set_customer_session($array_index, $value) {
        $customer = (array) jigoshop_session::instance()->customer;
        $customer[$array_index] = $value;
        jigoshop_session::instance()->customer = $customer;
    }
    
    private static function get_customer_session($array_index) {
        $customer = (array) jigoshop_session::instance()->customer;
        return $customer[$array_index];
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
		
			$jigoshop_orders = new jigoshop_orders();
			$jigoshop_orders->get_customer_orders( get_current_user_id() );
			if ($jigoshop_orders->orders) foreach ($jigoshop_orders->orders as $order) :
				if ( $order->status == 'completed' ) {
					$results = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."jigoshop_downloadable_product_permissions WHERE order_key = \"".$order->order_key."\" AND user_id = ".get_current_user_id().";" );
					$user_info = get_userdata(get_current_user_id());
					if ($results) foreach ($results as $result) :
							$_product = new jigoshop_product( $result->product_id );
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