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
		$load_gateways = apply_filters('jigoshop_payment_gateways', array());

		foreach ($load_gateways as $gateway) {
			$object = new $gateway();
			self::$gateways[$object->id] = $object;
		}
	}

	/**
	 * @return array List of all registered payment gateways.
	 */
	public static function payment_gateways()
	{
		return apply_filters('jigoshop_payment_gateways_installed', self::$gateways);
	}

	public static function get_available_payment_gateways()
	{
		$gateways = array();

		foreach (self::$gateways as $gateway) {
			/** @var jigoshop_payment_gateway $gateway */
			if ($gateway->is_available()) {
				$gateways[$gateway->id] = $gateway;
			}
		}

		return apply_filters('jigoshop_available_payment_gateways', $gateways);
	}

	/**
	 * @param $id int ID (name) of the gateway to fetch.
	 * @return jigoshop_payment_gateway|null
	 */
	public static function get_gateway($id)
	{
		$gateways = self::get_available_payment_gateways();
		foreach($gateways as $gateway){
			/** @var jigoshop_payment_gateway $gateway */
			if($gateway->id === $id){
				return $gateway;
			}
		}

		return null;
	}
}