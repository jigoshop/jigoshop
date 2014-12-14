<?php
use Jigoshop\Integration;

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
class jigoshop_payment_gateways
{
	protected static $gateways = array();
	private static $instance;

	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	public static function reset()
	{
		self::$instance = null;
	}

	public function __clone()
	{
		trigger_error("Cloning Singleton's is not allowed.", E_USER_ERROR);
	}


	public function __wakeup()
	{
		trigger_error("Unserializing Singleton's is not allowed.", E_USER_ERROR);
	}

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
			/** @var $gateway Jigoshop\Integration\Gateway */
			return $gateway->getGateway();
		}, Integration::getPaymentService()->getAvailable()));
	}

	public static function get_available_payment_gateways()
	{
		return apply_filters('jigoshop_available_payment_gateways', array_map(function($gateway){
			/** @var $gateway Jigoshop\Integration\Gateway */
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
			/** @var Jigoshop\Integration\Gateway $gateway */
			$gateway = Integration::getPaymentService()->get($id);

			return $gateway->getGateway();
		} catch (\Jigoshop\Exception $e) {
			return null;
		}
	}
}
