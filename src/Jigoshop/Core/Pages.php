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

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->options = $options;
		$this->wp = $wp;
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
				return $this->isAdminPage() == $page;
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
		return $this->wp->isPage($this->options->getPageId(self::CART));
	}

	/**
	 * Evaluates to true only on the Checkout or Pay pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isCheckout()
	{
		return $this->wp->isPage($this->options->getPageId(self::CHECKOUT)); // || is_page(jigoshop_get_page_id('pay'));
	}

	/**
	 * Evaluates to true only on the Single Product Page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isProduct()
	{
		return $this->wp->isSingular(array(Types::PRODUCT));
	}

	/**
	 * Evaluates to true only on the Category Pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isProductCategory()
	{
		return $this->wp->isTax(Types::PRODUCT_CATEGORY);
	}

	/**
	 * Evaluates to true only on Shop, Product Category, and Product Tag pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isProductList()
	{
		return $this->wp->isPostTypeArchive(Types::PRODUCT) || $this->wp->isPage($this->options->getPageId(self::SHOP))
		|| $this->isProductCategory() || $this->isProductTag();
	}

	/**
	 * Evaluates to true only on the Tag Pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isProductTag()
	{
		return $this->wp->isTax(Types::PRODUCT_TAG);
	}

	/**
	 * @return mixed Admin page or false.
	 * @since 2.0
	 */
	public function isAdminPage()
	{
		$currentScreen = $this->wp->getCurrentScreen();

		if ($currentScreen === null) {
			return false;
		}

//		if (in_array($currentScreen->post_type, array(Types::PRODUCT, Types::ORDER, Types::COUPON), true)) {
		if (in_array($currentScreen->post_type, array(Types::PRODUCT), true)) {
			return $currentScreen->post_type;
		}

		if (strpos($currentScreen->id, 'jigoshop') !== false) {
			return $currentScreen->id;
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
		return $this->isProductList() || $this->isProduct();
	}

	/**
	 * Evaluates to true only on the main Account or any sub-account pages
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isAccount()
	{
		return $this->wp->isPage($this->options->getPageId(self::ACCOUNT)); // || is_page(jigoshop_get_page_id('edit_address')) || is_page(jigoshop_get_page_id('change_password')) || is_page(jigoshop_get_page_id('view_order'));
	}

	/**
	 * Evaluates to true only on the Order Tracking page
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function isOrderTracker()
	{
		return $this->wp->isPage($this->options->getPageId(self::ORDER_TRACKING));
	}

	public function isAjax()
	{
		if (defined('DOING_AJAX')) {
			return true;
		}

		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}
}