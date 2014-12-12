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
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
class jigoshop_payment_gateways extends Jigoshop_Singleton
{
	protected static $gateways = array();

	protected function __construct()
	{
		self::gateways_init();
	}

	/**
	 * @deprecated Use jigoshop_payment_gateways::gateways_init() instead.
	 */
	public static function gateway_inits()
	{
		self::gateways_init();
	}

	/**
	 * Initializes gateways.
	 */
	public static function gateways_init()
	{
		Integration::initializeGateways();
	}

	/**
	 * @return array List of all registered payment gateways.
	 */
	public static function payment_gateways()
	{
		return apply_filters('jigoshop_payment_gateways_installed', array_map(function($gateway){
			/** @var $gateway Integration\Gateway */
			return $gateway->getGateway();
		}, Integration::getPaymentService()->getAvailable()));
	}

	public static function get_available_payment_gateways()
	{
		return apply_filters('jigoshop_available_payment_gateways', array_map(function($gateway){
			/** @var $gateway Integration\Gateway */
			return $gateway->getGateway();
		}, Integration::getPaymentService()->getEnabled()));
	}

	/**
	 * @param $id int ID (name) of the gateway to fetch.
	 * @return jigoshop_payment_gateway|null
	 */
	public static function get_gateway($id)
	{
		try {
			/** @var Integration\Gateway $gateway */
			$gateway = Integration::getPaymentService()->get($id);

			return $gateway->getGateway();
		} catch (\Jigoshop\Exception $e) {
			return null;
		}
	}
}
