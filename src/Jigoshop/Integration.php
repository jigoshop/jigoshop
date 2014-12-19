<?php

namespace Jigoshop;

use JigoshopContainer;
use Monolog\Registry;

/**
 * Integration helper - stores useful services and classes for static access.
 * WARNING: Do NOT use this class, it is useful only as transition for Jigoshop 1.x modules and will be removed in future!
 */
class Integration
{
	/** @var JigoshopContainer */
	private static $di;
	private static $shippingRate;

	public function __construct(\JigoshopContainer $di)
	{
		self::$di = $di;

		// Email product title support
		add_filter('jigoshop\emails\product_title', function ($value, $product, $item){
			return apply_filters('jigoshop_order_product_title', $value, $product, $item);
		}, 10, 3);


		add_action('jigoshop\service\cart', function(){
			Integration::initializeShipping();
		});
		add_action('jigoshop\page_resolver\before', function(){
			if (Integration::getPages()->isCheckout()) {
				Integration::initializeGateways();
			}
		});
		add_action('jigoshop\admin\page_resolver\before', function(){
			$pages = Integration::getAdminPages();
			if ($pages->isOrdersList() || $pages->isSettings()) {
				Integration::initializeGateways();
			}
			if ($pages->isDashboard() || $pages->isOrder()) {
				Integration::initializeGateways();
				Integration::initializeShipping();
			}
		});
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

	public static function initializeShipping()
	{
		Registry::getInstance('jigoshop')->addDebug('Initializing Jigoshop 1.x shipping methods');
		$service = self::getShippingService();
		$methods = apply_filters('jigoshop_shipping_methods', array());

		foreach ($methods as $method) {
			$service->addMethod(new Integration\Shipping($method));
		}

//		add_action('jigoshop\checkout\set_shipping\before', '\Integration::processGateway');
	}

	/**
	 * @return int
	 */
	public static function getShippingRate()
	{
		return self::$shippingRate;
	}

	/**
	 * @param int $shippingRate
	 */
	public static function setShippingRate($shippingRate)
	{
		self::$shippingRate = $shippingRate;
	}

	/**
	 * @return \Jigoshop\Service\PaymentServiceInterface
	 */
	public static function getPaymentService()
	{
		return self::$di->get('jigoshop.service.payment');
	}

	/**
	 * @return \Jigoshop\Service\ShippingServiceInterface
	 */
	public static function getShippingService()
	{
		return self::$di->get('jigoshop.service.shipping');
	}

	/**
	 * @return \Jigoshop\Service\OrderServiceInterface
	 */
	public static function getOrderService()
	{
		return self::$di->get('jigoshop.service.order');
	}

	/**
	 * @return \Jigoshop\Service\TaxServiceInterface
	 */
	public static function getTaxService()
	{
		return self::$di->get('jigoshop.service.tax');
	}

	/**
	 * @return \Jigoshop\Service\ProductServiceInterface
	 */
	public static function getProductService()
	{
		return self::$di->get('jigoshop.service.product');
	}

	/**
	 * @return \Jigoshop\Service\CouponServiceInterface
	 */
	public static function getCouponService()
	{
		return self::$di->get('jigoshop.service.coupon');
	}

	/**
	 * @return \Jigoshop\Service\CustomerServiceInterface
	 */
	public static function getCustomerService()
	{
		return self::$di->get('jigoshop.service.customer');
	}

	/**
	 * @return \Jigoshop\Core
	 */
	public static function getCore()
	{
		return self::$di->get('jigoshop');
	}

	/**
	 * @return \Jigoshop\Core\Emails
	 */
	public static function getEmails()
	{
		return self::$di->get('jigoshop.emails');
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
	 * @return \Jigoshop\Frontend\Pages
	 */
	public static function getPages()
	{
		return self::$di->get('jigoshop.pages');
	}

	/**
	 * @return \Jigoshop\Admin\Pages
	 */
	public static function getAdminPages()
	{
		return self::$di->get('jigoshop.admin.pages');
	}

	/**
	 * @return \Jigoshop\Admin\Settings
	 */
	public static function getAdminSettings()
	{
		return self::$di->get('jigoshop.admin.settings');
	}
}
