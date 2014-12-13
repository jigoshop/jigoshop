<?php

use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
use Monolog\Registry;

require_once(JIGOSHOP_DIR.'/integration/classes/abstract/jigoshop_base.class.php');
require_once(JIGOSHOP_DIR.'/integration/classes/jigoshop.class.php');
require_once(JIGOSHOP_DIR.'/integration/classes/jigoshop_options_interface.php');
require_once(JIGOSHOP_DIR.'/integration/classes/jigoshop_countries.class.php');
require_once(JIGOSHOP_DIR.'/integration/classes/jigoshop_request_api.class.php');
require_once(JIGOSHOP_DIR.'/integration/gateways/gateway.class.php');
require_once(JIGOSHOP_DIR.'/integration/gateways/gateways.class.php');

/**
 * Integration helper - stores useful services and classes for static access.
 *
 * WARNING: Do NOT use this class, it is useful only as transition for Jigoshop 1.x modules and will be removed in future!
 */
class Integration
{
	private static $di;
	/** @var CartServiceInterface */
	private static $cartService;
	/** @var \Jigoshop\Core\Messages */
	private static $messages;
	/** @var \Jigoshop\Core\Options */
	private static $options;
	/** @var \Jigoshop\Core\Options */
	private static $settings;

	public function __construct(\JigoshopContainer $di)
	{
		self::$di = $di;
		self::$cartService = $di->get('jigoshop.service.cart');
		self::$messages = $di->get('jigoshop.messages');
		self::$options = $di->get('jigoshop.options');
		self::$settings;
	}

	public static function initializeGateways()
	{
		Registry::getInstance('jigoshop')->addDebug('Initializing Jigoshop 1.x gateways');
		$service = self::getPaymentService();
		$gateways = apply_filters('jigoshop_payment_gateways', array());

		foreach ($gateways as $gateway) {
			$service->addMethod(new Integration\Gateway($gateway));
		}

		add_action('jigoshop\checkout\set_payment\before', '\Integration::processGateway');
	}

	/**
	 * @param $method \Jigoshop\Payment\Method
	 */
	public static function processGateway($method)
	{
		if ($method instanceof Integration\Gateway) {
			$gateway = $method->getGateway();
			Registry::getInstance('jigoshop')->addDebug(sprintf('Processing Jigoshop 1.x gateway "%s".', $method->getId()));
			$cart = self::getCart();

			if ($gateway->process_gateway($cart->getSubtotal(), $cart->getShippingPrice(), $cart->getDiscount())) {
				$gateway->validate_fields();
			}

			// TODO: Check if we have errors (jigoshop::has_errors()) and throw properly exception to stop execution
		}
	}

	/**
	 * @return PaymentServiceInterface|object
	 */
	public static function getPaymentService()
	{
		return self::$di->get('jigoshop.service.payment');
	}

	/**
	 * @return \Jigoshop\Frontend\Cart
	 */
	public static function getCart()
	{
		return self::$di->get('jigoshop.service.cart')->getCurrent();
	}

	/**
	 * @return \Jigoshop\Core\Messages
	 */
	public static function getMessages()
	{
		return self::$di->get('jigoshop.messages');
	}

	/**
	 * @return \Jigoshop\Core\Options
	 */
	public static function getOptions()
	{
		return self::$di->get('jigoshop.options');
	}

	/**
	 * @return \Jigoshop\Admin\Settings
	 */
	public static function getAdminSettings()
	{
		return self::$di->get('jigoshop.admin.settings');
	}
}
