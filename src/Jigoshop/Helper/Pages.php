<?php

namespace Jigoshop\Helper;

/**
 * Helper class - useful to determine what page we are currently on.
 *
 * @package Jigoshop\Helper
 * @author Jigoshop
 */
class Pages
{
	const SHOP = 'shop';
	const CART = 'cart';
	const CHECKOUT = 'checkout';
	const ACCOUNT = 'account';
	const ORDER_TRACKING = 'order_tracking';

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
		return self::isProductList() || self::isProduct();
	}

	/**
	 * Evaluates to true only on Shop, Product Category, and Product Tag pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isProductList()
	{
		return is_post_type_archive('product') || is_page(Core::getPageId(self::SHOP)) || self::isProductTag() || self::isProductCategory();
	}

	/**
	 * Evaluates to true only on the Tag Pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isProductTag()
	{
		return is_tax('product_tag');
	}

	/**
	 * Evaluates to true only on the Category Pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isProductCategory()
	{
		return is_tax('product_category');
	}

	/**
	 * Evaluates to true only on the Single Product Page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isProduct()
	{
		return is_singular(array('product'));
	}

	/**
	 * Evaluates to true only on the main Account or any sub-account pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isAccount()
	{
		return is_page(Core::getPageId(self::ACCOUNT));// || is_page(jigoshop_get_page_id('edit_address')) || is_page(jigoshop_get_page_id('change_password')) || is_page(jigoshop_get_page_id('view_order'));
	}

	/**
	 * Evaluates to true only on the Cart page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isCart()
	{
		return is_page(Core::getPageId(self::CART));
	}

	/**
	 * Evaluates to true only on the Checkout or Pay pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isCheckout()
	{
		return is_page(Core::getPageId(self::CHECKOUT));// || is_page(jigoshop_get_page_id('pay'));
	}

	/**
	 * Evaluates to true only on the Order Tracking page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public static function isOrderTracker()
	{
		return is_page(Core::getPageId(self::ORDER_TRACKING));
	}

	public static function isAjax()
	{
		if(defined('DOING_AJAX'))
		{
			return true;
		}

		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	/**
	 * @return mixed Admin page or false.
	 * @since 2.0
	 */
	public static function isAdminPage()
	{
		global $current_screen;

		if($current_screen->post_type == 'product' || $current_screen->post_type == 'shop_order' || $current_screen->post_type == 'shop_coupon')
		{
			return $current_screen->post_type;
		}

		if(strstr($current_screen->id, 'jigoshop'))
		{
			return $current_screen->id;
		}

		return false;
	}
}