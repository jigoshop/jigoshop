<?php

namespace Jigoshop;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Product;
use WPAL\Wordpress;

/**
 * Migration helper - transforms Jigoshop 1.x entities into Jigoshop 2.x ones.
 *
 * WARNING: Do NOT use this class, it is useful only as transition for Jigoshop 1.x and will be removed in future!
 */
class Migration
{
	/** @var Wordpress */
	private static $wp;
	/** @var Options */
	private static $options;
	private static $taxClasses = array();

	public function __construct(Wordpress $wp, Options $options)
	{
		self::$wp = $wp;
		self::$options = $options;
	}

	public static function migrateOptions()
	{
		$options = self::$wp->getOption('jigoshop_options');
		$transformations = \Jigoshop_Base::get_options()->__getTransformations();

		foreach ($transformations as $old => $new) {
			self::$options->update($new, $options[$old]);
		}

		// TODO: How to migrate plugin options?
	}

	public static function migrateProducts()
	{
		$wpdb = self::$wp->getWPDB();

		// Update product_cat into product_category
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s", array('product_category', 'product_cat')));

		$query = $wpdb->prepare("
			SELECT DISTINCT p.ID, pm.* FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE p.post_type IN (%s, %s) p.post_status <> %s pm.meta_key",
			array('product', 'product_variation', 'auto-draft'));
		$products = $wpdb->get_results($query);

		for ($i = 0, $endI = count($products); $i < $endI;) {
			$product = $products[$i];

			// Add product types
			$types = wp_get_object_terms($product['ID'], 'product_type');
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} VALUES (NULL, %d, %s, %s)", array($product['ID'], 'type', $types[0]->slug)));
			$i++;

			// Update columns
			while ($i < $endI && $products[$i]['ID'] == $product['ID']) {
				// Sales support
				if ($products[$i]['meta_key'] == 'sale_price' && !empty($products[$i]['meta_value'])) {
					$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($products[$i]['ID'], 'sales_enabled', true)));
				}

				$wpdb->query($wpdb->prepare(
					"UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND meta_id = %d",
					array(
						self::_transformProductMeta($products[$i]['meta_key'], $products[$i]['meta_value']),
						self::_transformProductMetaKey($products[$i]['meta_key']),
						$products[$i]['meta_id'],
					)
				));
				$i++;
			}
		}

		// Add found tax classes
		$currentTaxClasses = self::$options->get('tax.classes');
		$currentTaxClassesKeys = array_map(function($item){
			return $item['class'];
		}, $currentTaxClasses);
		self::$taxClasses = array_filter(array_unique(self::$taxClasses), function($item) use ($currentTaxClassesKeys){
			return !in_array($item, $currentTaxClassesKeys);
		});

		foreach (self::$taxClasses as $class) {
			$currentTaxClasses[] = array(
				'label' => ucfirst($class),
				'class' => $class,
			);
		}

		self::$options->update('tax.classes', $currentTaxClasses);
	}

	private static function _transformProductMeta($key, $value)
	{
		switch ($key) {
			case 'visibility':
				switch ($value) {
					case 'visible':
						return Product::VISIBILITY_PUBLIC;
					case 'catalog':
						return Product::VISIBILITY_CATALOG;
					case 'search':
						return Product::VISIBILITY_SEARCH;
					default:
						return Product::VISIBILITY_NONE;
				}
			case 'tax_status':
				if ($value == 'taxable') {
					return true;
				}
				return false;
			case 'tax_classes':
				$value = unserialize($value);
				$result = array();

				if (!is_array($value)) {
					$value = array();
				}

				foreach ($value as $taxClass) {
					if ($taxClass == '*') {
						$taxClass = 'standard';
					}

					self::$taxClasses[] = $taxClass;
					$result[] = $taxClass;
				}

				return serialize($result);
			case 'stock_status':
				switch ($value) {
					case 'outofstock':
						return Product\Attributes\StockStatus::OUT_STOCK;
					default:
						return Product\Attributes\StockStatus::IN_STOCK;
				}
			case 'backorders':
				switch ($value) {
					case 'notify':
						return Product\Attributes\StockStatus::BACKORDERS_NOTIFY;
					case 'yes':
						return Product\Attributes\StockStatus::BACKORDERS_ALLOW;
					default:
						return Product\Attributes\StockStatus::BACKORDERS_FORBID;
				}
			default:
				return $value;
		}
	}

	private static function _transformProductMetaKey($key)
	{
		switch ($key) {
			case 'tax_status':
				return 'is_taxable';
			case 'weight':
				return 'size_weight';
			case 'width':
				return 'size_width';
			case 'height':
				return 'size_height';
			case 'length':
				return 'size_length';
			case 'sale_price':
				return 'sales_price';
			case 'sale_price_dates_from':
				return 'sales_from';
			case 'sale_price_dates_to':
				return 'sales_to';
			case 'manage_stock':
				return 'stock_manage';
			case 'stock':
				return 'stock_stock';
			case 'backorders':
				return 'stock_allow_backorders';
			case 'quantity_sold':
				return 'stock_sold';
			case 'file_path':
				return 'url';
			case 'download_limit':
				return 'limit';
			default:
				return $key;
		}
	}
}
