<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Products
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $productService;
	/** @var Types\Product */
	private $type;

	public function __construct(Wordpress $wp, Options $options, Types\Product $type, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->type = $type;

		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::PRODUCT), array($this, 'columns'));
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::PRODUCT), array($this, 'displayColumn'), 2);
		// TODO: Introduce proper category filter
//		$wp->addAction('restrict_manage_posts', array($this, 'categoryFilter'));
		$wp->addAction('restrict_manage_posts', array($this, 'typeFilter'));
	}

	public function columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'thumbnail' => null,
			'title' => _x('Name', 'product', 'jigoshop'),
			'sku' => _x('SKU', 'product', 'jigoshop'),
			'featured' => sprintf(
				'<img src="'.JIGOSHOP_URL.'/assets/images/head_featured.png" alt="%s" title="%s" />',
				_x('Is featured?', 'product', 'jigoshop'),
				_x('Is featured?', 'product', 'jigoshop')
			),
			'type' => _x('Type', 'product', 'jigoshop'),
			'stock' => _x('Stock', 'product', 'jigoshop'),
			'price' => _x('Price', 'product', 'jigoshop'),
			'creation' => _x('Created at', 'product', 'jigoshop'),
		);

		if($this->options->get('enable_sku', 'yes') !== 'yes'){
			unset($columns['sku']);
		}
		if($this->options->get('manage_stock', 'yes') !== 'yes'){
			unset($columns['stock']);
		}

		return $columns;
	}

	public function displayColumn($column)
	{
		$post = $this->wp->getGlobalPost();
		if($post === null){
			return;
		}

		$product = $this->productService->find($post->ID);
		switch ($column) {
			case 'thumbnail':
				echo ProductHelper::getFeaturedImage($product);
				break;
			case 'price':
				echo ProductHelper::getPriceHtml($product);
				break;
			case 'featured':
				echo ProductHelper::isFeatured($product);
				break;
			case 'stock':
				echo ProductHelper::getStock($product);
				break;
			case 'type':
				echo $this->type->getType($product->getType())->getName();
				break;
			case 'sku':
				echo $product->getSku();
				break;
			case 'creation':
				$fullFormat = _x('Y/m/d g:i:s A', 'time', 'jigoshop');
				$format = _x('Y/m/d', 'time', 'jigoshop');
				echo '<abbr title="'.mysql2date($fullFormat, $post->post_date).'">'.apply_filters('post_date_column_time', mysql2date($format, $post->post_date), $post ).'</abbr>';

				if($product->isVisible()){
					echo '<br /><strong>'.__('Visible in', 'jigoshop').'</strong>: ';
					switch($product->getVisibility()){
						case Product::VISIBILITY_SEARCH:
							echo __('Search only', 'jigoshop');
							break;
						case Product::VISIBILITY_CATALOG:
							echo __('Catalog only', 'jigoshop');
							break;
						case Product::VISIBILITY_PUBLIC:
							echo __('Catalog and search', 'jigoshop');
							break;
					}
				}
				break;
		}
	}
	/**
	 * Filter products by category, uses slugs for option values.
	 * Props to: Andrew Benbow - chromeorange.co.uk
	 */
//	public function categoryFilter()
//	{
//		global $typenow, $wp_query;
//
//		if ($typenow == self::NAME) {
//			$r = array();
//			$r['pad_counts'] = 1;
//			$r['hierarchical'] = true;
//			$r['hide_empty'] = true;
//			$r['show_count'] = true;
//			$r['selected']   = isset( $wp_query->query['product_cat'] ) ? $wp_query->query['product_cat'] : '';
//
//			$terms = get_terms( 'product_cat', $r );
//			if ( ! $terms ) return;
//
//			$output  = "<select name='product_cat' id='dropdown_product_cat'>";
//
//			$output .= '<option value="" ' .  selected( isset( $_GET['product_cat'] ) ? esc_attr( $_GET['product_cat'] ) : '', '', false ) . '>'.__('View all categories', 'jigoshop').'</option>';
//			$output .= jigoshop_walk_category_dropdown_tree( $terms, 0, $r );
//			$output .="</select>";
//		}
//	}

	/**
	 * Filter products by type
	 */
	public function typeFilter()
	{
		$type = $this->wp->getTypeNow();
		if ($type != Types::PRODUCT) {
			return;
		}

		// Get all active terms
		$types = array();
		foreach ($this->type->getEnabledTypes() as $type) {
			/** @var $type Types\Product\Type */
			$types[$type->getId()] = array(
				'label' => $type->getName(),
				'count' => $this->getTypeCount($type),
			);
		}
		$currentType = $this->wp->getQueryParameter('product_type');

		Render::output('admin/products/typeFilter', array(
			'types' => $types,
			'current' => $currentType
		));
	}

	/**
	 * Finds and returns number of products of specified type.
	 *
	 * @param $type Types\Product\Type Type class.
	 * @return int Count of the products.
	 */
	private function getTypeCount($type)
	{
		// TODO: Implement fetching count of selected type
		return 0;
	}
}
