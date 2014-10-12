<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product\Simple;
use Jigoshop\Helper\Render;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
use WPAL\Wordpress;

class Product implements Post
{
	const NAME = 'product';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $productService;
	/** @var TaxServiceInterface */
	private $taxService;
	/** @var array */
	private $enabledTypes = array();

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, TaxServiceInterface $taxService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->taxService = $taxService;

		$this->enabledTypes = $options->getEnabledProductTypes();
		foreach ($this->enabledTypes as $type) {
			$productService->addType($type, $this->getTypeClass($type));
		}

		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::PRODUCT), array($this, 'columns'));
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::PRODUCT), array($this, 'displayColumn'), 2);
		// TODO: Introduce proper category filter
//		$wp->addAction('restrict_manage_posts', array($this, 'categoryFilter'));
		$wp->addAction('restrict_manage_posts', array($this, 'typeFilter'));
		$that = $this;
		$wp->addAction('add_meta_boxes_'.self::NAME, function() use ($wp, $that){
			$wp->addMetaBox('jigoshop-product-data', __('Product Data', 'jigoshop'), array($that, 'box'), $that::NAME, 'normal', 'high');
			$wp->removeMetaBox('commentstatusdiv', null, 'normal');
		});
	}

	public function getName()
	{
		return self::NAME;
	}

	public function getDefinition()
	{
		return array(
			'labels' => array(
				'name' => __('Products', 'jigoshop'),
				'singular_name' => __('Product', 'jigoshop'),
				'all_items' => __('All Products', 'jigoshop'),
				'add_new' => __('Add New', 'jigoshop'),
				'add_new_item' => __('Add New Product', 'jigoshop'),
				'edit' => __('Edit', 'jigoshop'),
				'edit_item' => __('Edit Product', 'jigoshop'),
				'new_item' => __('New Product', 'jigoshop'),
				'view' => __('View Product', 'jigoshop'),
				'view_item' => __('View Product', 'jigoshop'),
				'search_items' => __('Search Products', 'jigoshop'),
				'not_found' => __('No Products found', 'jigoshop'),
				'not_found_in_trash' => __('No Products found in trash', 'jigoshop'),
				'parent' => __('Parent Product', 'jigoshop'),
			),
			'description' => __('This is where you can add new products to your store.', 'jigoshop'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'product',
			'map_meta_cap' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'hierarchical' => false, // Hierarchical causes a memory leak http://core.trac.wordpress.org/ticket/15459
			'rewrite' => array(
				'slug' => 'product',
				'with_front' => true,
				'feeds' => true,
				'pages' => true,
			),
			'query_var' => true,
			'supports' => array('title', 'editor', 'thumbnail', 'comments', 'excerpt'),
			'has_archive' => true,
			'show_in_nav_menus' => false,
			'menu_position' => 56,
			'menu_icon' => 'dashicons-book',
		);
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
				echo \Jigoshop\Helper\Product::getFeaturedImage($product);
				break;
			case 'price':
				echo \Jigoshop\Helper\Product::getPriceHtml($product);
				break;
			case 'featured':
				echo \Jigoshop\Helper\Product::isFeatured($product);
				break;
			case 'stock':
				echo \Jigoshop\Helper\Product::getStock($product);
				break;
			case 'type':
				echo $this->getTypeName($product->getType());
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
						case \Jigoshop\Entity\Product::VISIBILITY_SEARCH:
							echo __('Search only', 'jigoshop');
							break;
						case \Jigoshop\Entity\Product::VISIBILITY_CATALOG:
							echo __('Catalog only', 'jigoshop');
							break;
						case \Jigoshop\Entity\Product::VISIBILITY_PUBLIC:
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
		if ($type != self::NAME) {
			return;
		}

		// Get all active terms
		$types = array();
		foreach ($this->enabledTypes as $type) {
			$types[$type] = array(
				'label' => $this->getTypeName($type),
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
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since 		1.0
	 */
	public function box()
	{
		$post = $this->wp->getGlobalPost();
		$product = $this->productService->findForPost($post);
		$types = array();

		foreach ($this->enabledTypes as $type) {
			$types[$type] = $this->getTypeName($type);
		}

		$menu = $this->wp->applyFilters('jigoshop\admin\product\menu', array(
			'general' => array('label' => __('General', 'jigoshop'), 'visible' => true),
			'advanced' => array('label' => __('Advanced', 'jigoshop'), 'visible' => true),
			'stock' => array('label' => __('Stock', 'jigoshop'), 'visible' => true),
			'sales' => array('label' => __('Sales', 'jigoshop'), 'visible' => array('simple')),
//			'inventory' => __('Inventory', 'jigoshop'),
//			'attributes' => __('Attributes', 'jigoshop'),
		));
		$taxClasses = array();
		foreach ($this->options->get('tax.classes') as $class) {
			$taxClasses[$class['class']] = $class['label'];
		}

		$tabs = $this->wp->applyFilters('jigoshop\admin\product\tabs', array(
			'general' => array(
				'product' => $product,
			),
			'stock' => array(
				'product' => $product,
			),
			'sales' => array(
				'product' => $product,
			),
			'advanced' => array(
				'product' => $product,
				'taxClasses' => $taxClasses,
			),
//			'inventory' => array(),
//			'attributes' => array(),
		));

//		add_action('admin_footer', 'jigoshop_meta_scripts');
//		wp_nonce_field('jigoshop_save_data', 'jigoshop_meta_nonce');

		Render::output('admin/products/box', array(
			'product' => $product,
			'types' => $types,
			'menu' => $menu,
			'tabs' => $tabs,
			'current_tab' => 'general',
		));
	}

	/**
	 * Finds and returns class name of specified product type.
	 *
	 * @param $type string Name of the type.
	 * @return string Class name.
	 */
	private function getTypeClass($type)
	{
		switch($type){
			case Simple::TYPE:
				return 'Jigoshop\Entity\Product\Simple';
			default:
				return $this->wp->applyFilters('jigoshop\product\type\class', $type);
		}
	}

	/**
	 * Finds and returns human-readable name of specified product type.
	 *
	 * @param $type string Name of the type.
	 * @return string Human-readable name.
	 */
	private function getTypeName($type)
	{
		switch($type){
			case Simple::TYPE:
				return __('Simple', 'jigoshop');
			default:
				return $this->wp->applyFilters('jigoshop\product\type\name', $type);
		}
	}

	/**
	 * Finds and returns number of products of specified type.
	 *
	 * @param $type string Name of the type.
	 * @return int Count of the products.
	 */
	private function getTypeCount($type)
	{
		// TODO: Implement fetching count of selected type
		return 0;
	}
}
