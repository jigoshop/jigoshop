<?php

namespace Jigoshop;

/**
 * Class containing available pages in Jigoshop.
 *
 * @package Jigoshop
 * @author Jigoshop
 */
class Pages
{
	const CART = 'cart';
	const CHECKOUT = 'checkout';
	const PRODUCT = 'product';
	const PRODUCT_CATEGORY = 'product_category';
	const PRODUCT_LIST = 'product_list';
	const PRODUCT_TAG = 'product_tag';
	const ALL = 'all';

	/**
	 * Returns list of pages supported by Pages::isPage() and Pages::isOneOfPages().
	 *
	 * @return array List of supported pages.
	 */
	public function getAvailablePages() {
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
	public static function isOneOfPages($pages) {
		$result = false;
		$pages = is_array($pages) ? $pages : array($pages);

		foreach($pages as $page){
			$result = $result || self::isPage($page);
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
	public static function isPage($page) {
		switch($page){
			case self::CART:
				return Helper\Pages::isCart();
			case self::CHECKOUT:
				return Helper\Pages::isCheckout();
			case self::PRODUCT:
				return Helper\Pages::isProduct();
			case self::PRODUCT_CATEGORY:
				return Helper\Pages::isProductCategory();
			case self::PRODUCT_LIST:
				return Helper\Pages::isProductList();
			case self::PRODUCT_TAG:
				return Helper\Pages::isProductTag();
			case self::ALL:
				return true;
			default:
				return Helper\Pages::isAdminPage() == $page;
		}
	}
}