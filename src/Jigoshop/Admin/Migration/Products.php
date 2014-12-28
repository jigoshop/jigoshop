<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute\Multiselect;
use Jigoshop\Entity\Product\Attribute\Option;
use Jigoshop\Entity\Product\Attribute\Select;
use Jigoshop\Entity\Product\Attribute\Text;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Helper\Render;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Products implements Tool
{
	const ID = 'jigoshop_products_migration';

	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var ProductServiceInterface */
	private $productService;
	private $taxClasses = array();

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
	}

	/**
	 * @return string Tool ID.
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * Shows migration tool in Migration tab.
	 */
	public function display()
	{
		Render::output('admin/migration/products', array());
	}

	/**
	 * Migrates data from old format to new one.
	 */
	public function migrate()
	{
		$wpdb = $this->wp->getWPDB();

		// Register product type taxonomy to fetch old product types
		$this->wp->registerTaxonomy('product_type',
			array('product'),
			array(
				'hierarchical' => false,
				'show_ui' => false,
				'query_var' => true,
				'show_in_nav_menus' => false,
			)
		);

		// Update product_cat into product_category
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s", array('product_category', 'product_cat')));

		$query = $wpdb->prepare("
			SELECT DISTINCT p.ID, pm.* FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE p.post_type IN (%s, %s) AND p.post_status <> %s",
			array('product', 'product_variation', 'auto-draft'));
		$products = $wpdb->get_results($query);
		$globalAttributes = array();

		for ($i = 0, $endI = count($products); $i < $endI;) {
			$product = $products[$i];

			// Add product types
			$types = $this->wp->getTheTerms($product->ID, 'product_type');
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} VALUES (NULL, %d, %s, %s)", array($product->ID, 'type', $types[0]->slug)));

			// Update columns
			do {
				// Sales support
				if ($products[$i]->meta_key == 'sale_price' && !empty($products[$i]->meta_value)) {
					$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($product->ID, 'sales_enabled', true)));
				}

				// Custom product attributes support
				if ($products[$i]->meta_key == 'product_attributes') {
					$attributes = unserialize($products[$i]->meta_value);
					$globalAttributes = array_replace_recursive($globalAttributes, array_filter($attributes, function($item){
						return $item['is_taxonomy'] == true;
					}));
					$attributes = array_filter($attributes, function($item){
						return $item['is_taxonomy'] == false;
					});

					foreach ($attributes as $slug => $source) {
						$attribute = $this->productService->createAttribute(Text::TYPE);
						$attribute->setSlug($slug);
						$attribute->setLabel($source['name']);
						$attribute->setVisible($source['visible']);
						$attribute->setLocal(true);

						$this->productService->saveAttribute($attribute);

						$wpdb->insert($wpdb->prefix.'jigoshop_product_attribute', array(
							'product_id' => $product->ID,
							'attribute_id' => $attribute->getId(),
							'value' => $source['value'],
						));
					}
				}

				$key = $this->_transformKey($products[$i]->meta_key);

				if (!empty($key)) {
					$wpdb->query($wpdb->prepare(
						"UPDATE {$wpdb->postmeta} SET meta_value = %s, meta_key = %s WHERE meta_id = %d",
						array(
							$this->_transform($products[$i]->meta_key, $products[$i]->meta_value),
							$key,
							$products[$i]->meta_id,
						)
					));
				}

				$i++;
			} while ($i < $endI && $products[$i]->ID == $product->ID);
		}

		// Migrate global product attributes
		$attributes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jigoshop_attribute_taxonomies");
		foreach ($attributes as $source) {
			$attribute = $this->productService->createAttribute($this->_getAttributeType($source));
			$label = !empty($source->attribute_label) ? $source->attribute_label : $source->attribute_name;
			$attribute->setLabel($label);
			$attribute->setSlug($this->wp->getHelpers()->sanitizeTitle($source->attribute_name));
			$attribute->setVisible(true);
			$attribute->setLocal(false);

			$options = $wpdb->get_results("
				SELECT t.name, t.slug, tr.object_id FROM {$wpdb->terms} t
					LEFT JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
					LEFT JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
				 	WHERE tt.taxonomy = 'pa_{$source->attribute_name}'
		  ");

			$productAttribute = array();
			$createdOptions = array();
			foreach ($options as $k => $sourceOption) {
				if (!in_array($sourceOption->slug, $createdOptions)) {
					$option = new Option();
					$option->setLabel($sourceOption->name);
					$option->setValue($sourceOption->slug);
					$attribute->addOption($option);
					$createdOptions[] = $sourceOption->slug;
				}

				if (isset($sourceOption->object_id)) {
					$productAttribute[$sourceOption->object_id][] = $sourceOption->slug;
				}
			}

			$this->productService->saveAttribute($attribute);

			foreach ($productAttribute as $productId => $values) {
				$value = array();
				foreach ($attribute->getOptions() as $option) {
					/** @var $option Option */
					if (in_array($option->getValue(), $values)) {
						$value[] = $option->getId();
					}
				}

				$wpdb->insert($wpdb->prefix.'jigoshop_product_attribute', array(
					'product_id' => $productId,
					'attribute_id' => $attribute->getId(),
					'value' => join('|', $value),
				));

				if (isset($globalAttributes[$attribute->getSlug()])) {
					$data = array(
						'product_id' => $productId,
						'attribute_id' => $attribute->getId(),
						'meta_key' => 'is_visible',
						'meta_value' => $globalAttributes[$attribute->getSlug()]['visible'] ? true : false,
					);
					$wpdb->insert($wpdb->prefix.'jigoshop_product_attribute_meta', $data);

					if ($globalAttributes[$attribute->getSlug()]['variation']) {
						$data = array(
							'product_id' => $productId,
							'attribute_id' => $attribute->getId(),
							'meta_key' => 'is_variable',
							'meta_value' => true,
						);
						$wpdb->insert($wpdb->prefix.'jigoshop_product_attribute_meta', $data);
					}
				}
			}
		}

		// Add found tax classes
		$currentTaxClasses = $this->options->get('tax.classes');
		$currentTaxClassesKeys = array_map(function($item){
			return $item['class'];
		}, $currentTaxClasses);
		$this->taxClasses = array_filter(array_unique($this->taxClasses), function($item) use ($currentTaxClassesKeys){
			return !in_array($item, $currentTaxClassesKeys);
		});

		foreach ($this->taxClasses as $class) {
			$currentTaxClasses[] = array(
				'label' => ucfirst($class),
				'class' => $class,
			);
		}

		$this->options->update('tax.classes', $currentTaxClasses);
	}

	private function _transform($key, $value)
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

					$this->taxClasses[] = $taxClass;
					$result[] = $taxClass;
				}

				return serialize($result);
			case 'stock_status':
				switch ($value) {
					case 'outofstock':
						return StockStatus::OUT_STOCK;
					default:
						return StockStatus::IN_STOCK;
				}
			case 'backorders':
				switch ($value) {
					case 'notify':
						return StockStatus::BACKORDERS_NOTIFY;
					case 'yes':
						return StockStatus::BACKORDERS_ALLOW;
					default:
						return StockStatus::BACKORDERS_FORBID;
				}
			default:
				return $value;
		}
	}

	private function _transformKey($key)
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
			case 'product_attributes':
				return false;
			default:
				return $key;
		}
	}

	private function _getAttributeType($source)
	{
		switch ($source->attribute_type) {
			case 'multiselect':
				return Multiselect::TYPE;
			case 'select':
				return Select::TYPE;
			default:
				return Text::TYPE;
		}
	}
}
