<?php
/**
 * Shipping class
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Checkout
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */     

class jigoshop_shipping extends jigoshop_singleton {
	
	protected static $enabled			= false;
	protected static $shipping_methods 	= array();
	protected static $chosen_method		= null;
	protected static $shipping_total	= 0;
	protected static $shipping_tax 		= 0;
	protected static $shipping_label	= null;
	protected static $has_calculable_shipping	= false;
	
	
	/** Constructor */
    protected function __construct() {
    
		if ( get_option( 'jigoshop_calc_shipping' ) != 'no' ) self::$enabled = true;
		
		// ensure low priority to force recalc after all shipping plugins are loaded
		self::add_action( 'plugins_loaded', 'calculate_shipping', 999 );
	}
	
	
	/**
	 * This is called from the 'plugins_loaded' action hook so shipping plugins can get hooked in.
	 * After all plugins are loaded, our constructor will force recalculation of shipping to ensure
	 *     that the Cart and Checkout properly show shipping on the first and subsequent passes.
	 */
	public static function method_inits() {
	
		do_action( 'jigoshop_shipping_init' ); /* loaded plugins for shipping inits */
		
		self::init_shipping_methods();
		
	}

	/**
	* pulled out of the method_inits function so that it can be called within this class if the
	* shipping method array hasn't been yet initialized 
	*/
	private static function init_shipping_methods() {
		$load_methods = apply_filters( 'jigoshop_shipping_methods', array() );
		
		foreach ( $load_methods as $method ) :
			self::$shipping_methods[] = &new $method();
		endforeach;
	
	}
    
	public static function is_enabled() {
		return self::$enabled;
	}
	
	
	public static function get_total() {
		return self::$shipping_total;
	}
	
	
	public static function get_tax() {
		return self::$shipping_tax;
	}
	
	
	public static function get_label() {
		return self::$shipping_label;
	}
	
	
	public static function get_all_methods() {
                if (self::$shipping_methods == NULL) {
                    self::init_shipping_methods();
                }
		return self::$shipping_methods;
	}
	
	public static function has_calculable_shipping() {
		return self::$has_calculable_shipping;
	}
	
	public static function get_available_shipping_methods() {
	
		$_available_methods = array();
		
		if ( self::$enabled == 'yes' ) :
		
			foreach ( self::get_all_methods() as $method ) :
				
				if ( $method->is_available() ) :
					$_available_methods[$method->id] = $method;
					if ($method instanceof jigoshop_calculable_shipping) self::$has_calculable_shipping = true;
				endif;
				
			endforeach;
			
		endif;
		
		return $_available_methods;
		
	}
	
	
	public static function reset_shipping_methods() {
		foreach ( self::$shipping_methods as $method ) :
			$method->chosen = false;
			$method->shipping_total = 0;
			$method->shipping_tax = 0;
                           if ($method instanceof jigoshop_calculable_shipping) $method->reset_rates();
		endforeach;
	}
	

         /**
          * This function is to update the shipping totals that the user has selected.
          * @selected_method_id the shipping method id chosen by the customer
          * @service_id the service id of the shipping method that was chosen by the customer
          * TODO: This function needs a little more work...especially with tax           
          */ 
	public static function update_shipping_user_selected($selected_method_id, $service_id) {
	
		if ( self::$enabled ) :
			$selected_method = self::get_selected_method($selected_method_id);
			if (isset($selected_method)) :
				if (!$seleted_method->chosen) :
					self::reset_chosen_shipping_methods();
					$selected_method->choose();
					self::$shipping_label = $selected_method->title;
					self::$shipping_tax = $selected_method->shipping_tax; //TODO: fix this. shipping_tax needs to change because the shipping total has changed. Should go below, and recalculate tax
				endif;
				
				// set shipping total here, because it needs to be set regardless if the method was chosen previously 
				// or not
				if (isset($service_id)) :
					self::$shipping_total = $selected_method->get_chosen_price($service_id);
				else :
					self::$shipping_total = $selected_method->shipping_total;
				endif;
			endif;
		endif;
	}

	/**
	 * used during user selectable. If a user chooses a different shipping method than
	 * was automatically selected, then the auto selected method must change to false
	 */
	private static function reset_chosen_shipping_methods() {
		foreach ( self::$shipping_methods as $method ) :
			$method->chosen = false;
		endforeach;
	}
	
         /**
          * This returns the method from the available methods for shipping based on the 
          * method id selected
          * @selected_method_id the shipping method chosen by the customer
          */
	private static function get_selected_method($selected_method_id) {
		if ( self::$enabled ) :
		
			foreach ( self::$shipping_methods as $method ) :
				
				if ( $method->id == $selected_method_id ) :
					return $method;
				endif;
				
			endforeach;
			
		endif;
		return NULL;
	}	

         public static function calculate_shipping() {
		
		if ( self::$enabled == 'yes' ) :
		
			self::reset_shipping_methods();
			
			self::reset_shipping();
			$_cheapest_fee = '';
			$_cheapest_method = '';
			
			if ( isset( $_SESSION['chosen_shipping_method_id'] )) $chosen_method = $_SESSION['chosen_shipping_method_id'];
			else $chosen_method = '';
			
			$calc_cheapest = false; /* bug for free shipping is here -JAP- */
			
			if ( ! $chosen_method || empty( $chosen_method )) $calc_cheapest = true;
			
			$_available_methods = self::get_available_shipping_methods();
			
			foreach ( $_available_methods as $method ) :
				$method->calculate_shipping();
				$fee = $method->shipping_total;
				if ( $fee < $_cheapest_fee || ! is_numeric( $_cheapest_fee )) :
					$_cheapest_fee = $fee;
					$_cheapest_method = $method->id;
				endif;
			endforeach;
			
//			if ( $calc_cheapest || ! isset( $_available_methods[$chosen_method] )) :
//			if ( ! isset( $_available_methods[$chosen_method] )) :
				$chosen_method = $_cheapest_method;
//			endif;
			
			if ( $chosen_method ) :
				
				$_available_methods[$chosen_method]->choose();
				self::$shipping_total 	= $_available_methods[$chosen_method]->shipping_total;
				self::$shipping_tax 	= $_available_methods[$chosen_method]->shipping_tax;
				self::$shipping_label 	= $_available_methods[$chosen_method]->title;
				
			endif;

		endif;
		
	}
	
	
	public static function reset_shipping() {
		self::$shipping_total = 0;
		self::$shipping_tax = 0;
		self::$shipping_label = null;
		self::$has_calculable_shipping = false;
	}
	
}