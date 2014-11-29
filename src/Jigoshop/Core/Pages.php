<?php

namespace Jigoshop\Core;

use Jigoshop\Core;
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
	private $options;
	/** @var \WPAL\Wordpress */
	private $wp;
	private $cache = array();

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
	}

	/**
	 * Returns list of pages supported by is() and isOneOf() methods.
	 *
	 * @return array List of supported pages.
	 */
	public function getAvailable()
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
	public function isOneOf($pages)
	{
		$result = false;
		$pages = is_array($pages) ? $pages : array($pages);

		foreach ($pages as $page) {
			$result = $result || $this->is($page);
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
	public function is($page)
	{
		switch ($page) {
			case self::CART:
				return $this->isCart();
			case self::CHECKOUT:
				return $this->isCheckout();
			case self::PRODUCT:
				return $this->isProduct();
			case self::PRODUCT_CATEGORY:
				return $this->isProductCategory();
			case self::PRODUCT_LIST:
				return $this->isProductList();
			case self::PRODUCT_TAG:
				return $this->isProductTag();
			case self::ALL:
				return true;
			default:
				return $this->isAdminPage($page);
		}
	}

	/**
	 * Evaluates to true only on the Cart page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isCart()
	{
		if (!isset($this->cache[self::CART])) {
			$page = $this->options->getPageId(self::CART);
			$this->cache[self::CART] = $page !== false && $this->wp->isPage($page);
			$this->cache[self::CART] |= $this->isAjax() && strpos($_REQUEST['action'], 'cart') !== false;
		}

		return $this->cache[self::CART];
	}

	/**
	 * Evaluates to true only on the Checkout or Pay pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isCheckout()
	{
		if (!isset($this->cache[self::CHECKOUT])) {
			$page = $this->options->getPageId(self::CHECKOUT);
			$this->cache[self::CHECKOUT] = $page !== false && $this->wp->isPage($page);
			$this->cache[self::CHECKOUT] |= $this->isAjax() && strpos($_REQUEST['action'], 'checkout') !== false;
		}

		return $this->cache[self::CHECKOUT];
	}

	/**
	 * Evaluates to true only on the Single Product Page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isProduct()
	{
		if (!isset($this->cache[self::PRODUCT])) {
			$this->cache[self::PRODUCT] =  $this->wp->isSingular(array(Types::PRODUCT));
		}

		return $this->cache[self::PRODUCT];
	}

	/**
	 * Evaluates to true only on the Category Pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isProductCategory()
	{
		if (!isset($this->cache[self::PRODUCT_CATEGORY])) {
			$this->cache[self::PRODUCT_CATEGORY] =  $this->wp->isTax(Types::PRODUCT_CATEGORY);
		}

		return $this->cache[self::PRODUCT_CATEGORY];
	}

	/**
	 * Evaluates to true only on Shop, Product Category, and Product Tag pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isProductList()
	{
		if (!isset($this->cache[self::PRODUCT_LIST])) {
			$page = $this->options->getPageId(self::SHOP);
			$this->cache[self::PRODUCT_LIST] = $this->wp->isPostTypeArchive(Types::PRODUCT) ||
				($page !== false && $this->wp->isPage($page)) ||
				$this->isProductCategory() ||
				$this->isProductTag();
		}

		return $this->cache[self::PRODUCT_LIST];
	}

	/**
	 * Evaluates to true only on the Tag Pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isProductTag()
	{
		if (!isset($this->cache[self::PRODUCT_TAG])) {
			$this->cache[self::PRODUCT_TAG] =  $this->wp->isTax(Types::PRODUCT_TAG);
		}

		return $this->cache[self::PRODUCT_TAG];
	}

	/**
	 * Evaluates to true only on the main Account or any sub-account pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isAccount()
	{
		if (!isset($this->cache[self::ACCOUNT])) {
			$page = $this->options->getPageId(self::ACCOUNT);
			$this->cache[self::ACCOUNT] = $page !== false && $this->wp->isPage($page);
		}

		return $this->cache[self::ACCOUNT];
	}

	/**
	 * Evaluates to true only on the Edit address page of My account.
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isAccountEditAddress()
	{
		if (!isset($this->cache['account-edit-address'])) {
			$wp = $this->wp->getWp();
			$this->cache['account-edit-address'] = $this->isAccount() && isset($wp->query_vars['edit-address']);
		}

		return $this->cache['account-edit-address'];
	}

	/**
	 * Evaluates to true only on the Change password page of My account.
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isAccountChangePassword()
	{
		if (!isset($this->cache['account-change-password'])) {
			$wp = $this->wp->getWp();
			$this->cache['account-change-password'] = $this->isAccount() && isset($wp->query_vars['change-password']);
		}

		return $this->cache['account-change-password'];
	}

	/**
	 * Evaluates to true only on the Order Tracking page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isOrderTracker()
	{
		if (!isset($this->cache[self::ORDER_TRACKING])) {
			$page = $this->options->getPageId(self::ORDER_TRACKING);
			$this->cache[self::ORDER_TRACKING] = $page !== false && $this->wp->isPage($page);
		}

		return $this->cache[self::ORDER_TRACKING];
	}

	/**
	 * @param $page string Page to check.
	 * @return boolean Is current page selected one?
	 * @since 2.0
	 */
	public function isAdminPage($page)
	{
		if (!isset($this->cache[$page])) {
			$this->cache[$page] = $this->getAdminPage() == $page;
		}

		return $this->cache[$page];
	}

	/**
	 * @return string|bool Currently displayed admin page slug or false.
	 */
	public function getAdminPage()
	{
		$currentScreen = $this->wp->getCurrentScreen();

		if ($currentScreen === null) {
			return false;
		}

		//		if (in_array($currentScreen->post_type, array(Types::PRODUCT, Types::ORDER, Types::COUPON), true)) {
		if (in_array($currentScreen->post_type, array(Types::PRODUCT, Types::ORDER), true)) {
			return $currentScreen->post_type;
		}
		if (strpos($currentScreen->id, 'jigoshop') !== false) {
			return $currentScreen->id;
		}
		if (strpos($currentScreen->base, 'jigoshop_page') !== false) {
			return $currentScreen->base;
		}

		return false;
	}

	/**
	 * Evaluates to true for all Jigoshop pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isJigoshop()
	{
		return $this->isShop() || $this->isAccount() || $this->isCart() || $this->isCheckout() || $this->isOrderTracker();
	}

	/**
	 * Evaluates to true only on the Shop, Category, Tag and Single Product Pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isShop()
	{
		return $this->isProductList() || $this->isProduct() || $this->isProductCategory() || $this->isProductTag();
	}

	public function isAjax()
	{
		if (defined('DOING_AJAX') && strpos($_REQUEST['action'], 'jigoshop') !== false) {
			return true;
		}

		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}
}
