<?php

use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
use Monolog\Registry;

/**
 * Integration helper - stores useful services and classes for static access.
 *
 * WARNING: Do NOT use this class, it is useful only as transition for Jigoshop 1.x modules and will be removed in future!
 */
class Integration
{
	/** @var PaymentServiceInterface */
	private static $paymentService;
	/** @var CartServiceInterface */
	private static $cartService;

	public function __construct(PaymentServiceInterface $paymentService, CartServiceInterface $cartService)
	{
		self::$paymentService = $paymentService;
		self::$cartService = $cartService;
	}

	public static function initializeGateways()
	{
		Registry::getInstance('jigoshop')->addDebug('Initializing Jigoshop 1.x gateways');
		$service = self::getPaymentService();
		$gateways = apply_filters('jigoshop_payment_gateways', array());

		foreach ($gateways as $gateway) {
			$object = new $gateway();
			$service->addMethod(new Integration\Gateway($object));
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

	public static function getPaymentService()
	{
		return self::$paymentService;
	}

	public static function getCart()
	{
		return self::$cartService->getCurrent();
	}
}
