<?php

namespace Jigoshop;

use Jigoshop\Entity\Order;
use Jigoshop\Entity\Product;
use Jigoshop\Frontend\Pages;
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
	/** @var Order */
	private static $currentOrder;

	public function __construct(\JigoshopContainer $di)
	{
		self::$di = $di;
		add_action('jigoshop\run', function($di){
			/** @var $di \JigoshopContainer */
			\jigoshop_product::__setProductService($di->get('jigoshop.service.product'));
			\jigoshop_product::__setTaxService($di->get('jigoshop.service.tax'));
		});

		// Email product title support
		add_filter('jigoshop\emails\product_title', function ($value, $product, $item){
			return apply_filters('jigoshop_order_product_title', $value, $product, $item);
		}, 10, 3);

		add_action('jigoshop\migration\before', function(){
			Integration::initializeGateways();
			Integration::initializeShipping();
		});
		add_action('jigoshop\service\cart', function(){
			Integration::initializeShipping();
		});
		add_action('jigoshop\page_resolver\before', function(){
			if (Pages::isCart() || Pages::isCheckout() || Pages::isCheckoutThankYou()) {
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
		add_filter('jigoshop\pay\render', function($render, $order){
			/** @var $order Order */
			if (isset($_GET['receipt'])) {
				ob_start();
				do_action('receipt_'.$_GET['receipt'], $order->getId());
				return ob_get_clean();
			}

			return $render;
		}, 10, 2);
		add_filter('jigoshop\checkout\order', function($order){
			Integration::setCurrentOrder($order);
			return $order;
		}, 10, 1);

		// Admin product page integration
		add_filter('jigoshop\admin\product\menu', function($menu){
			ob_start();
			do_action('jigoshop_product_write_panel_tabs');
			do_action('product_write_panel_tabs');
			$tabs = ob_get_clean();

			$data = array();
			preg_match_all('@<a href=(\'|")#(.+?)(\'|")>(.*?)</a>@', $tabs, $data);

			foreach ($data[2] as $key => $id) {
				$menu[$id] = array('label' => $data[4][$key], 'visible' => true);
			}

			return $menu;
		});
		add_filter('jigoshop\admin\product\tabs', function($tabs){
			ob_start();
			do_action('jigoshop_product_write_panels');
			do_action('product_write_panels');
			$panels = ob_get_clean();

			$data = array();
			preg_match_all('@<div id=(\'|")(.+?)(\'|").*?class=(\'|").*?jigoshop_options_panel.*?(\'|").*?>(.*?)</div>@', str_replace("\n", '', $panels), $data);

			foreach ($data[2] as $key => $id) {
				$tabs[$id] = $data[6][$key];
			}

			return $tabs;
		});
		add_action('jigoshop\service\product\save', function($product){
			/** @var $product Product */
			do_action('jigoshop_process_product_meta', $product->getId());
			do_action('jigoshop_process_product_meta_'.$product->getType(), $product->getId());
		});

		// Product support
		add_filter('jigoshop\product\restore_state', function($state){
			$id = isset($state['id']) ? $state['id'] : 0;

			if (isset($state['regular_price'])) {
				$state['regular_price'] = apply_filters('jigoshop_product_get_regular_price', $state['regular_price'], $id);
			}

			return $state;
		});
		add_action('jigoshop\template\product\tabs', function($currentTab){
			do_action('jigoshop_product_tabs', $currentTab);
		});
		add_action('jigoshop\template\product\tab_panels', function(){
			do_action('jigoshop_product_tab_panels');
		}, 10, 0);
		add_action('jigoshop\product\summary', function($product){
			global $post;
			do_action('jigoshop_template_single_summary', $post, new \jigoshop_product($product));
		});
		add_filter('jigoshop\helper\product\get_price_html', function($html, $price, $product){
			return apply_filters('jigoshop_product_get_price_html', $html, new \jigoshop_product($product), $price);
		}, 10, 3);
		add_filter('jigoshop\cart\validate_new_item', function($value, $id, $quantity){
			return apply_filters('jigoshop_add_to_cart_validation', $value, $id, $quantity);
		}, 10, 3);
		add_filter('jigoshop\product\get_price', function($price, $product){
			return apply_filters('jigoshop_product_get_price', $price, $product->getId());
		}, 10, 2);
		add_action('jigoshop\product\tabs\general', function(){
			return do_action('jigoshop_product_pricing_options');
		}, 10, 0);
		add_action('jigoshop\template\product\before_cart', function(){
			return do_action('jigoshop_before_add_to_cart_form_button');
		}, 10, 0);
		add_action('jigoshop\template\shop\list\before', function(){
			do_action('jigoshop_before_shop_loop');
		});
		add_action('jigoshop\template\shop\list\after', function(){
			do_action('jigoshop_after_shop_loop');
		});

		// Orders support
		add_action('jigoshop\service\order\new', function($id){
			do_action('jigoshop_new_order', $id);
		});

		// Cart support
		add_filter('jigoshop\template\shop\cart\product_title', function($value, $product){
			/** @var $product Product */
			return apply_filters('jigoshop_cart_product_title', $value, new \jigoshop_product($product));
		}, 10, 2);
		add_filter('jigoshop\template\shop\cart\product_price', function($value, $price, $product, $item){
			/** @var $product Product */
			/** @var $item Order\Item */
			$cart = \jigoshop_cart::get_cart();
			$values = $cart[$item->getKey()];
			return apply_filters('jigoshop_product_price_display_in_cart', $value, $product->getId(), $values);
		}, 10, 4);
		add_filter('jigoshop\template\shop\cart\product_subtotal', function($value, $subtotal, $product, $item){
			/** @var $product Product */
			/** @var $item Order\Item */
			$cart = \jigoshop_cart::get_cart();
			$values = $cart[$item->getKey()];
			if (Integration::getOptions()->get('tax.included')) {
				return apply_filters('jigoshop_product_total_display_in_cart', $value, $product->getId(), $values);
			}
			return apply_filters('jigoshop_product_subtotal_display_in_cart', $value, $product->getId(), $values);
		}, 10, 4);

		// Checkout support
		add_action('jigoshop\template\shop\checkout\before_total', function(){
			do_action('jigoshop_after_review_order_items');
		});
	}

	public static function initializeGateways()
	{
		Registry::getInstance(JIGOSHOP_LOGGER)->addDebug('Initializing Jigoshop 1.x gateways');
		$service = self::getPaymentService();
		$gateways = apply_filters('jigoshop_payment_gateways', array());

		foreach ($gateways as $gateway) {
			$service->addMethod(new Integration\Gateway($gateway));
		}

		add_action('jigoshop\checkout\set_payment\before', '\Jigoshop\Integration::processGateway');
	}

	/**
	 * @param $method \Jigoshop\Payment\Method
	 */
	public static function processGateway($method)
	{
		if ($method instanceof Integration\Gateway) {
			$gateway = $method->getGateway();
			Registry::getInstance(JIGOSHOP_LOGGER)->addDebug(sprintf('Processing Jigoshop 1.x gateway "%s".', $method->getId()));
			$cart = self::getCart();

			if ($gateway->process_gateway($cart->getSubtotal(), $cart->getShippingPrice(), $cart->getDiscount())) {
				$gateway->validate_fields();
			}

			// TODO: Check if we have errors (jigoshop::has_errors()) and throw properly exception to stop execution
		}
	}

	public static function initializeShipping()
	{
		Registry::getInstance(JIGOSHOP_LOGGER)->addDebug('Initializing Jigoshop 1.x shipping methods');
		$service = self::getShippingService();
		$methods = apply_filters('jigoshop_shipping_methods', array());

		foreach ($methods as $method) {
			$service->addMethod(new Integration\Shipping($method));
		}

//		add_action('jigoshop\checkout\set_shipping\before', '\Jigoshop\Integration::processGateway');
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
	 * @return \Jigoshop\Service\CartServiceInterface
	 */
	public static function getCartService()
	{
		return self::$di->get('jigoshop.service.cart');
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
	 * @return \Jigoshop\Entity\Cart
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
	 * @return \Jigoshop\Helper\Styles
	 */
	public static function getStyles()
	{
		return self::$di->get('jigoshop.helper.styles');
	}

	/**
	 * @return \Jigoshop\Helper\Scripts
	 */
	public static function getScripts()
	{
		return self::$di->get('jigoshop.helper.scripts');
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

	public static function setCurrentOrder($order)
	{
		self::$currentOrder = $order;
	}

	/**
	 * @return Order
	 */
	public static function getCurrentOrder()
	{
		return self::$currentOrder;
	}
}
