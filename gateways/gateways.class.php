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
 * @package		Jigoshop
 * @category	Checkout
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */

class jigoshop_payment_gateways extends Jigoshop_Singleton {

	protected static $payment_gateways;


	/** Constructor */
    protected function __construct() {

		self::gateway_inits();

	}

	private static function gateway_inits() {

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