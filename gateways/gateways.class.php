<?php
/**
 * Jigoshop Payment Gateways class
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */

class jigoshop_payment_gateways extends Jigoshop_Singleton {

	protected static $payment_gateways = array();


	/** Constructor */
    protected function __construct() {

		// this constructor is called on the 'init' hook with a priority of 0 (highest)
		// this doesn't give gateways time to install themselves and load text domains
		// allow translations to function by re-adding initializations to the 'init' hook with a default priority
		self::add_action( 'init', 'gateway_inits' );
		
	}

	public static function gateway_inits() {
		
		// Gateways need to add themselves to this filter -prior- to the 'init' action hook
    	$load_gateways = apply_filters('jigoshop_payment_gateways', array());

		foreach ($load_gateways as $gateway) :

			self::$payment_gateways[] = new $gateway();

		endforeach;

    }

    function payment_gateways() {

		$_available_gateways = array();

		if (sizeof(self::$payment_gateways) > 0) :
			foreach ( self::$payment_gateways as $gateway ) :

				$_available_gateways[$gateway->id] = $gateway;

			endforeach;
		endif;

		return $_available_gateways;
	}

	function get_available_payment_gateways() {

		$_available_gateways = array();

		foreach ( self::$payment_gateways as $gateway ) :

			if ($gateway->is_available()) $_available_gateways[$gateway->id] = $gateway;

		endforeach;

		return $_available_gateways;
	}
    
}