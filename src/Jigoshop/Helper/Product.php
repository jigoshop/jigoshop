<?php

namespace Jigoshop\Helper;

use Jigoshop\Entity\Product\StockStatus;
use Jigoshop\Entity\Product\Type\Simple;

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
	 * @param \Jigoshop\Entity\Product $product
	 * @return string
	 */
	public static function getPrice(\Jigoshop\Entity\Product $product)
	{
		switch($product->getType()){
			case Simple::TYPE:
				/** @var $product Simple */
				return sprintf('%1$01.2f %2$s', $product->getPrice(), self::currencySymbol()); // TODO: Properly implement fetching price position and format
			default:
				return apply_filters('jigoshop\\helper\\product\\get_price', '', $product);
		}
	}

	/**
	 * Formats stock status appropriately to the product type and returns a string.
	 *
	 * @param \Jigoshop\Entity\Product $product
	 * @return string
	 */
	public static function getStock(\Jigoshop\Entity\Product $product)
	{
		switch($product->getType()){
			case Simple::TYPE:
				/** @var $product Simple */
				$status = $product->getStock()->getStatus() == StockStatus::IN_STOCK ?
					_x('In stock', 'product', 'jigoshop') :
					'<strong class="attention">'._x('Out of stock', 'product', 'jigoshop').'</strong>';
				return sprintf(_x('%s <strong>(%d)</strong>', 'product', 'jigoshop'), $status, $product->getStock()->getStock());
			default:
				return apply_filters('jigoshop\\helper\\product\\get_stock', '', $product);
		}
	}

	/**
	 * Gets thumbnail <img> tag for the product.
	 *
	 * @param \Jigoshop\Entity\Product $product
	 * @return string
	 */
	public static function getThumbnail(\Jigoshop\Entity\Product $product)
	{
		if (has_post_thumbnail($product->getId())) {
			return get_the_post_thumbnail($product->getId(), 'admin_product_list');
		} else {
			return '<img src="'.JIGOSHOP_URL.'/assets/images/placeholder.png" alt="Placeholder" width="70" height="70" />';
		}
	}

	/**
	 * Formats stock status appropriately to the product type and returns a string.
	 *
	 * @param \Jigoshop\Entity\Product $product
	 * @return string
	 */
	public static function isFeatured(\Jigoshop\Entity\Product $product)
	{
//				$url = wp_nonce_url( admin_url('admin-ajax.php?action=jigoshop-feature-product&product_id=' . $post->ID) );
//				echo '<a href="'.esc_url($url).'" title="'.__('Change','jigoshop') .'">';
//				if ($product->is_featured()) echo '<a href="'.esc_url($url).'"><img src="'.jigoshop::assets_url().'/assets/images/head_featured_desc.png" alt="yes" />';
//				else echo '<img src="'.jigoshop::assets_url().'/assets/images/head_featured.png" alt="no" />';
//				echo '</a>';
	}
}
