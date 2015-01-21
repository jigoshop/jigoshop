<?php

namespace Jigoshop\Frontend;

use Jigoshop\Core;
use Jigoshop\Core\Types;
use WPAL\Wordpress;

/**
 * Class containing available pages in Jigoshop.
 *
 * @package Jigoshop
 * @author Amadeusz Starzykiewicz
 */
class Pages
{
	const SHOP = 'shop';
	const CART = 'cart';
	const CHECKOUT = 'checkout';
	const THANK_YOU = 'checkout_thank_you';
	const PRODUCT = 'product';
	const PRODUCT_CATEGORY = 'product_category';
	const PRODUCT_LIST = 'product_list';
	const PRODUCT_TAG = 'product_tag';
	const ACCOUNT = 'account';
	const ORDER_TRACKING = 'order_tracking';
	const ALL = 'all';

	/** @var \Jigoshop\Core\Options */
	private static $options;
	private static $cache = array();

	public static function setOptions($options)
	{
		self::$options = $options;
	}

	/**
	 * Returns list of pages supported by is() and isOneOf() methods.
	 *
	 * @return array List of supported pages.
	 */
	public static function getAvailable()
	{
		return array(
			self::CART,
			self::CHECKOUT,
			self::PRODUCT,
			self::PRODUCT_CATEGORY,
			self::PRODUCT_LIST,
			self::PRODUCT_TAG,
			self::ACCOUNT,
			self::ORDER_TRACKING,
			self::ALL,
		);
	}

	/**
	 * Checks if current page is one of given page types.
	 *
	 * @param string|array $pages List of page types to check.
	 * @return bool Is current page one of provided?
	 * @since 2.0
	 */
	public static function isOneOf($pages)
	{
		$result = false;
		$pages = is_array($pages) ? $pages : array($pages);

		foreach ($pages as $page) {
			$result = $result || self::is($page);
		}

		return $result;
	}

	/**
	 * Checks if current page is of given page type.
	 *
	 * @param string $page Page type.
	 * @return bool Is current page the one from name?
	 * @since 2.0
	 */
	public static function is($page)
	{
		switch ($page) {
			case self::CART:
				return self::isCart();
			case self::CHECKOUT:
				return self::isCheckout();
			case self::PRODUCT:
				return self::isProduct();
			case self::PRODUCT_CATEGORY:
				return self::isProductCategory();
			case self::PRODUCT_LIST:
				return self::isProductList();
			case self::PRODUCT_TAG:
				return self::isProductTag();
			case self::ACCOUNT:
				return self::isAccount();
			case self::ALL:
				return true;
			default:
				return self::isAdminPage($page);
		}
	}

	/**
	 * Evaluates to true only on the Cart page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isCart()
	{
		if (!isset(self::$cache[self::CART])) {
			$page = self::$options->getPageId(self::CART);
			self::$cache[self::CART] = $page !== false && is_page($page);
			self::$cache[self::CART] |= self::isAjax() && isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'cart') !== false;
		}

		return self::$cache[self::CART];
	}

	public static function isAjax()
	{
		if (defined('DOING_AJAX') && isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'jigoshop') !== false) {
			return true;
		}

		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	/**
	 * Evaluates to true only on the Checkout or Pay pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isCheckout()
	{
		if (!isset(self::$cache[self::CHECKOUT])) {
			$page = self::$options->getPageId(self::CHECKOUT);
			self::$cache[self::CHECKOUT] = $page !== false && is_page($page);
			self::$cache[self::CHECKOUT] |= self::isAjax() && isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'checkout') !== false;
		}

		return self::$cache[self::CHECKOUT];
	}

	/**
	 * Evaluates to true only on the Single Product Page
	 *
*@return bool
	 * @since 2.0
	 */
	public static function isProduct()
	{
		if (!isset(self::$cache[self::PRODUCT])) {
			self::$cache[self::PRODUCT] = is_singular(array(Types::PRODUCT));
		}

		return self::$cache[self::PRODUCT];
	}

	/**
	 * Evaluates to true only on the Category Pages

	 *
*@return bool
	 * @since 2.0
	 */
	public static function isProductCategory()
	{
		if (!isset(self::$cache[self::PRODUCT_CATEGORY])) {
			self::$cache[self::PRODUCT_CATEGORY] = is_tax(Types::PRODUCT_CATEGORY);
		}

		return self::$cache[self::PRODUCT_CATEGORY];
	}

	/**
	 * Evaluates to true only on Shop, Product Category, and Product Tag pages

	 *
*@return bool
	 * @since 2.0
	 */
	public static function isProductList()
	{
		if (!isset(self::$cache[self::PRODUCT_LIST])) {
			$page = self::$options->getPageId(self::SHOP);
			self::$cache[self::PRODUCT_LIST] = is_post_type_archive(Types::PRODUCT) ||
				($page !== false && is_page($page)) ||
				self::isProductCategory() ||
				self::isProductTag();
		}

		return self::$cache[self::PRODUCT_LIST];
	}

	/**
	 * Evaluates to true only on the Tag Pages

	 *
*@return bool
	 * @since 2.0
	 */
	public static function isProductTag()
	{
		if (!isset(self::$cache[self::PRODUCT_TAG])) {
			self::$cache[self::PRODUCT_TAG] = is_tax(Types::PRODUCT_TAG);
		}

		return self::$cache[self::PRODUCT_TAG];
	}

	/**
	 * Evaluates to true only on the main Account or any sub-account pages

	 *
*@return bool
	 * @since 2.0
	 */
	public static function isAccount()
	{
		if (!isset(self::$cache[self::ACCOUNT])) {
			$page = self::$options->getPageId(self::ACCOUNT);
			self::$cache[self::ACCOUNT] = $page !== false && is_page($page);
		}

		return self::$cache[self::ACCOUNT];
	}

	/**
	 * @param $page string Page to check.
	 * @return boolean Is current page selected one?
	 * @since 2.0
	 */
	public static function isAdminPage($page)
	{
		if (!isset(self::$cache[$page])) {
			self::$cache[$page] = self::getAdminPage() == $page;
		}

		return self::$cache[$page];
	}

	/**
	 * @return string|bool Currently displayed admin page slug or false.
	 */
	public static function getAdminPage()
	{
		global $current_screen;

		if ($current_screen === null) {
			return false;
		}

		//		if (in_array($currentScreen->post_type, array(Types::PRODUCT, Types::ORDER, Types::COUPON), true)) {
		if (in_array($current_screen->post_type, array(Types::PRODUCT, Types::ORDER), true)) {
			return $current_screen->post_type;
		}
		if (strpos($current_screen->id, 'jigoshop') !== false) {
			return $current_screen->id;
		}
		if (strpos($current_screen->base, 'jigoshop_page') !== false) {
			return $current_screen->base;
		}

		return false;
	}

	/**
	 * Evaluates to true only on the Thank You page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isCheckoutThankYou()
	{
		if (!isset(self::$cache[self::THANK_YOU])) {
			$page = self::$options->getPageId(self::THANK_YOU);
			self::$cache[self::THANK_YOU] = $page !== false && is_page($page);
			self::$cache[self::THANK_YOU] |= self::isAjax() && isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'thank_you') !== false;
		}

		return self::$cache[self::THANK_YOU];
	}

	/**
	 * Evaluates to true only on the Thank You page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isCheckoutPay()
	{
		if (!isset(self::$cache['checkout-pay'])) {
			global $wp;
			self::$cache['checkout-pay'] = self::isCheckout() && isset($wp->query_vars['pay']);
		}

		return self::$cache['checkout-pay'];
	}

	/**
	 * Evaluates to true only on the Edit address page of My account.
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isAccountEditAddress()
	{
		if (!isset(self::$cache['account-edit-address'])) {
			global $wp;
			self::$cache['account-edit-address'] = self::isAccount() && isset($wp->query_vars['edit-address']);
		}

		return self::$cache['account-edit-address'];
	}

	/**
	 * Evaluates to true only on the Change password page of My account.
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isAccountChangePassword()
	{
		if (!isset(self::$cache['account-change-password'])) {
			global $wp;
			self::$cache['account-change-password'] = self::isAccount() && isset($wp->query_vars['change-password']);
		}

		return self::$cache['account-change-password'];
	}

	/**
	 * Evaluates to true only on the My orders page of My account.
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isAccountOrders()
	{
		if (!isset(self::$cache['account-orders'])) {
			global $wp;
			self::$cache['account-orders'] = self::isAccount() && isset($wp->query_vars['orders']);
		}

		return self::$cache['account-orders'];
	}

	/**
	 * Evaluates to true for all Jigoshop pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isJigoshop()
	{
		return self::isShop() || self::isAccount() || self::isCart() || self::isCheckout() || self::isOrderTracker();
	}

	/**
	 * Evaluates to true only on the Shop, Category, Tag and Single Product Pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isShop()
	{
		return self::isProductList() || self::isProduct() || self::isProductCategory() || self::isProductTag();
	}

	/**
	 * Evaluates to true only on the Order Tracking page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isOrderTracker()
	{
		if (!isset(self::$cache[self::ORDER_TRACKING])) {
			$page = self::$options->getPageId(self::ORDER_TRACKING);
			self::$cache[self::ORDER_TRACKING] = $page !== false && is_page($page);
		}

		return self::$cache[self::ORDER_TRACKING];
	}
}
