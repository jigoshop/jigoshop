<?php

namespace Jigoshop\Helper;

use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Entity\Product\Simple;
use Jigoshop\Entity\Product as ProductEntity;

class Product
{
	public static function currencySymbol()
	{
		return '$'; // TODO: Properly implement after setting up the settings page.
	}

	public static function dimensionsUnit()
	{
		return 'cm'; // TODO: Properly implement after setting up the settings page.
	}

	public static function weightUnit()
	{
		return 'kg'; // TODO: Properly implement after setting up the settings page.
	}

	/**
	 * Formats price appropriately to the product type and returns a string.
	 *
	 * @param ProductEntity $product
	 * @return string
	 */
	public static function getPrice(ProductEntity $product)
	{
		switch($product->getType()){
			case Simple::TYPE:
				/** @var $product Simple */
				if ( self::isOnSale($product)) {
					if (strpos($product->getSales()->getPrice(), '%') !== false) {
						return '<del>'.self::formatPrice($product->getRegularPrice()).'</del>'.self::formatPrice($product->getPrice()).'
						<ins>'.sprintf(__('%s off!', 'jigoshop'), $product->getSales()->getPrice()).'</ins>';
					} else {
						return '<del>'.self::formatPrice($product->getRegularPrice()).'</del>
						<ins>'.self::getPrice($product).'</ins>';
					}
				}

				return self::formatPrice($product->getPrice());
			default:
				return apply_filters('jigoshop\helper\product\get_price', '', $product);
		}
	}

	/**
	 * Formats stock status appropriately to the product type and returns a string.
	 *
	 * @param ProductEntity $product
	 * @return string
	 */
	public static function getStock(ProductEntity $product)
	{
		if (!$product->getStock()->getManage()) {
			return '';
		}

		// TODO: Respect shopping options for displaying stock values
		switch($product->getType()){
			case Simple::TYPE:
				/** @var $product Simple */
				$status = $product->getStock()->getStatus() == StockStatus::IN_STOCK ?
					_x('In stock', 'product', 'jigoshop') :
					'<strong class="attention">'._x('Out of stock', 'product', 'jigoshop').'</strong>';
				return sprintf(_x('%s <strong>(%d available)</strong>', 'product', 'jigoshop'), $status, $product->getStock()->getStock());
			default:
				return apply_filters('jigoshop\helper\product\get_stock', '', $product);
		}
	}

	/**
	 * Checks if product has a thumbnail.
	 *
	 * @param ProductEntity $product
	 * @return boolean
	 */
	public static function hasFeaturedImage(ProductEntity $product)
	{
		return has_post_thumbnail($product->getId());
	}

	/**
	 * Gets thumbnail <img> tag for the product.
	 *
	 * @param ProductEntity $product
	 * @param string $size
	 * @return string
	 */
	public static function getFeaturedImage(ProductEntity $product, $size = 'admin_product_list')
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
	 * @param ProductEntity $product
	 * @return string
	 */
	public static function isFeatured(ProductEntity $product)
	{
//				$url = wp_nonce_url( admin_url('admin-ajax.php?action=jigoshop-feature-product&product_id=' . $post->ID) );
//				echo '<a href="'.esc_url($url).'" title="'.__('Change','jigoshop') .'">';
//				if ($product->is_featured()) echo '<a href="'.esc_url($url).'"><img src="'.jigoshop::assets_url().'/assets/images/head_featured_desc.png" alt="yes" />';
//				else echo '<img src="'.jigoshop::assets_url().'/assets/images/head_featured.png" alt="no" />';
//				echo '</a>';
	}

	/**
	 * Check whether selected product is on sale.
	 *
	 * @param ProductEntity $product
	 * @return boolean
	 */
	public static function isOnSale(ProductEntity $product)
	{
		$status = false;
		switch($product->getType()){
			case Simple::TYPE:
				/** @var $product Simple */
				$time = time();
				$status = $product->getSales()->isEnabled() && $product->getSales()->getFrom()->getTimestamp() <= $time && $product->getSales()->getTo()->getTimestamp() >= $time;
		}

		return apply_filters('jigoshop\helper\product\is_on_sales', $status, $product);
	}

	/**
	 * @param $price float Price to format.
	 * @return string Formatted price with currency symbol.
	 */
	public static function formatPrice($price)
	{
		return sprintf('%1$01.2f %2$s', $price, self::currencySymbol()); // TODO: Properly implement fetching price position and format
	}
}
