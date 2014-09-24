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
				return sprintf('%1$01.2f %2$s', $product->getPrice(), self::currencySymbol()); // TODO: Properly implement fetching price position and format
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
		switch($product->getType()){
			case Simple::TYPE:
				/** @var $product Simple */
				$status = $product->getStock()->getStatus() == StockStatus::IN_STOCK ?
					_x('In stock', 'product', 'jigoshop') :
					'<strong class="attention">'._x('Out of stock', 'product', 'jigoshop').'</strong>';
				return sprintf(_x('%s <strong>(%d)</strong>', 'product', 'jigoshop'), $status, $product->getStock()->getStock());
			default:
				return apply_filters('jigoshop\helper\product\get_stock', '', $product);
		}
	}

	/**
	 * Gets thumbnail <img> tag for the product.
	 *
	 * @param ProductEntity $product
	 * @param string $size
	 * @return string
	 */
	public static function getThumbnail(ProductEntity $product, $size = 'admin_product_list')
	{
		if (has_post_thumbnail($product->getId())) {
			return get_the_post_thumbnail($product->getId(), $size);
		} else {
			$width = 70;
			$height = 70;

			global $_wp_additional_image_sizes;
			if (isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$size])) {
				$width = intval($_wp_additional_image_sizes[$size]['width']);
				$height = intval($_wp_additional_image_sizes[$size]['height']);
			}

			return '<img src="'.JIGOSHOP_URL.'/assets/images/placeholder.png" alt="Placeholder" width="'.$width.'" height="'.$height.'" />';
		}
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
}
