<?php

namespace Jigoshop\Helper;

use Jigoshop\Core\Options;
use Jigoshop\Entity;

class Product
{
	/** @var Options */
	private static $options;

	/**
	 * @param Options $options Options object.
	 */
	public static function setOptions($options)
	{
		self::$options = $options;
	}

	public static function dimensionsUnit()
	{
		return self::$options->get('products.dimensions_unit');
	}

	public static function weightUnit()
	{
		return self::$options->get('products.weight_unit');
	}

	/**
	 * Returns options array for select form element based on provided options list.
	 *
	 * It also allows to add empty item (placeholder for Select2) at the beginning.
	 *
	 * @param array $options Options to use.
	 * @param bool|string $emptyItem Empty item name or false to disable.
	 * @return array List of options.
	 */
	public static function getSelectOption(array $options, $emptyItem = false)
	{
		$result = array();

		if ($emptyItem !== false) {
			$result = array('' => $emptyItem);
		}

		foreach ($options as $item) {
			/** @var $item Entity\Product\Attribute\Option */
			$result[$item->getId()] = $item->getLabel();
		}

		return $result;
	}

	/**
	 * Formats price appropriately to the product type and returns a string.
	 *
	 * @param Entity\Product $product
	 * @return string
	 */
	public static function getPriceHtml(Entity\Product $product)
	{
		switch($product->getType()){
			case Entity\Product\Simple::TYPE:
			case Entity\Product\External::TYPE:
			case Entity\Product\Downloadable::TYPE:
				/** @var $product Entity\Product\Simple */
				if ( self::isOnSale($product)) {
					if (strpos($product->getSales()->getPrice(), '%') !== false) {
						return '<del>'.self::formatPrice($product->getRegularPrice()).'</del>'.self::formatPrice($product->getPrice()).'
						<ins>'.sprintf(__('%s off!', 'jigoshop'), $product->getSales()->getPrice()).'</ins>';
					} else {
						return '<del>'.self::formatPrice($product->getRegularPrice()).'</del>
						<ins>'.self::formatPrice($product->getPrice()).'</ins>';
					}
				}

				return self::formatPrice($product->getPrice());
			case Entity\Product\Variable::TYPE:
				/** @var $product Entity\Product\Variable */
				$price = $product->getLowestPrice();
				$formatted = self::formatPrice($price);

				if ($price !== '') {
					return sprintf(__('From: %s', 'jigoshop'), $formatted);
				}

				return $formatted;
			default:
				return apply_filters('jigoshop\helper\product\get_price', '', $product);
		}
	}

	/**
	 * Formats stock status appropriately to the product type and returns a string.
	 *
	 * @param Entity\Product $product
	 * @return string
	 */
	public static function getStock(Entity\Product $product)
	{
		if (!($product instanceof Entity\Product\Purchasable) || !$product->getStock()->getManage()) {
			return '';
		}

		switch($product->getType()){
			case Entity\Product\Simple::TYPE:
			case Entity\Product\Downloadable::TYPE:
				/** @var $product Entity\Product\Simple */
				$status = $product->getStock()->getStatus() == Entity\Product\Attributes\StockStatus::IN_STOCK ?
					_x('In stock', 'product', 'jigoshop') :
					'<strong class="attention">'._x('Out of stock', 'product', 'jigoshop').'</strong>';

				if (!self::$options->get('products.show_stock')) {
					return $status;
				}

				return sprintf(_x('%s <strong>(%d available)</strong>', 'product', 'jigoshop'), $status, $product->getStock()->getStock());
			default:
				return apply_filters('jigoshop\helper\product\get_stock', '', $product);
		}
	}

	/**
	 * Checks if product has a thumbnail.
	 *
	 * @param Entity\Product $product
	 * @return boolean
	 */
	public static function hasFeaturedImage(Entity\Product $product)
	{
		return has_post_thumbnail($product->getId());
	}

	/**
	 * Gets thumbnail <img> tag for the product.
	 *
	 * @param Entity\Product $product
	 * @param string $size
	 * @return string
	 */
	public static function getFeaturedImage(Entity\Product $product, $size = 'admin_product_list')
	{
		if (self::hasFeaturedImage($product)) {
			return get_the_post_thumbnail($product->getId(), $size);
		}

		return self::getImagePlaceholder($size);
	}

	/**
	 * Returns width and height for images of given size.
	 *
	 * @param $size string Size name to fetch.
	 * @return array Width and height values.
	 */
	public static function getImageSize($size) {
		$width = 70;
		$height = 70;

		global $_wp_additional_image_sizes;
		if (isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$size])) {
			$width = intval($_wp_additional_image_sizes[$size]['width']);
			$height = intval($_wp_additional_image_sizes[$size]['height']);
		}

		return array('width' => $width, 'height' => $height);
	}

	/**
	 * Gets placeholder <img> tag for products.
	 *
	 * @param string $size
	 * @return string
	 */
	public static function getImagePlaceholder($size = 'admin_product_list')
	{
		$size = self::getImageSize($size);

		return '<img src="'.JIGOSHOP_URL.'/assets/images/placeholder.png" alt="" width="'.$size['width'].'" height="'.$size['height'].'" />';
	}

	/**
	 * Formats stock status appropriately to the product type and returns a string.
	 *
	 * @param Entity\Product $product
	 * @return string
	 */
	public static function isFeatured(Entity\Product $product)
	{
		return sprintf(
			'<a href="#" data-id="%d" class="product-featured"><span class="glyphicon %s" aria-hidden="true"></span> <span class="sr-only">%s</span></a>',
			$product->getId(),
			$product->isFeatured() ? 'glyphicon-star' : 'glyphicon-star-empty',
			$product->isFeatured() ? __('Yes', 'jigoshop') : __('No', 'jigoshop')
		);
	}

	/**
	 * Check whether selected product is on sale.
	 *
	 * @param Entity\Product $product
	 * @return boolean
	 */
	public static function isOnSale(Entity\Product $product)
	{
		$status = false;
		switch($product->getType()){
			case Entity\Product\Simple::TYPE:
			case Entity\Product\External::TYPE:
			case Entity\Product\Downloadable::TYPE:
				/** @var $product Entity\Product\Simple */
				$status = $product->getSales()->isEnabled();
		}

		return apply_filters('jigoshop\helper\product\is_on_sales', $status, $product);
	}

	/**
	 * @param $price float Price to format.
	 * @return string Formatted price with currency symbol.
	 */
	public static function formatPrice($price)
	{
		if ($price !== '') {
			return sprintf(Currency::format(), Currency::symbol(), Currency::code(), self::formatNumericPrice($price));
		}

		return __('Price not announced.', 'jigoshop');
	}

	/**
	 * @param $price float Price to format.
	 * @return string Formatted price as numeric value.
	 */
	public static function formatNumericPrice($price)
	{
		return number_format($price, Currency::decimals(), Currency::decimalSeparator(), Currency::thousandsSeparator());
	}

	/**
	 * Prints add to cart form for product list.
	 *
	 * @param $product \Jigoshop\Entity\Product Product to display.
	 * @param $template string Template base to use.
	 */
	public static function printAddToCartForm($product, $template)
	{
		switch($product->getType()){
			case Entity\Product\Simple::TYPE:
				Render::output("shop/{$template}/cart/simple", array('product' => $product));
				break;
			case Entity\Product\Downloadable::TYPE:
				Render::output("shop/{$template}/cart/downloadable", array('product' => $product));
				break;
			case Entity\Product\External::TYPE:
				Render::output("shop/{$template}/cart/external", array('product' => $product));
				break;
			case Entity\Product\Variable::TYPE:
				Render::output("shop/{$template}/cart/variable", array('product' => $product));
				break;
			default:
				do_action('jigoshop\helper\product\print_cart_form', '', $product, $template);
		}
	}

	/**
	 * @param Entity\Product\Variable\Variation $variation Variation to format.
	 * @return string Formatted variation data in HTML.
	 */
	public static function getVariation(Entity\Product\Variable\Variation $variation)
	{
		return Render::get('helper/product/variation', array(
			'variation' => $variation,
		));
	}
}
