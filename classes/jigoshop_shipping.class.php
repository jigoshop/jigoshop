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
		return self::$shipping_methods;
	}
	
	
	public static function get_available_shipping_methods() {
	
		$_available_methods = array();
		
		if ( self::$enabled == 'yes' ) :
		
			foreach ( self::$shipping_methods as $method ) :
				
				if ( $method->is_available() ) $_available_methods[$method->id] = $method;
				
			endforeach;
			
		endif;
		
		return $_available_methods;
		
	}
	
	
	public static function reset_shipping_methods() {
		foreach ( self::$shipping_methods as $method ) :
			$method->chosen = false;
			$method->shipping_total = 0;
			$method->shipping_tax = 0;
		endforeach;
	}
	
	
	public static function calculate_shipping() {
		
		if ( self::$enabled == 'yes' ) :
		
			self::reset_shipping_methods();
			
			self::$shipping_total = 0;
			self::$shipping_tax = 0;
			self::$shipping_label = null;
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
	}
	
}