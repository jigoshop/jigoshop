<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product\Type\Simple;
use Jigoshop\Helper\Render;
use Jigoshop\Service\ProductServiceInterface;
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

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;

		$enabledTypes = $options->getEnabledProductTypes();
		foreach ($enabledTypes as $type) {
			$productService->addType($type, $this->getTypeClass($type));
		}

		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::PRODUCT), array($this, 'columns'));
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::PRODUCT), array($this, 'displayColumn'), 2);
//		$wp->addAction('restrict_manage_posts', array($this, 'categoryFilter'));
//		$wp->addAction('restrict_manage_posts', array($this, 'typeFilter'));
		$that = $this;
		$wp->addAction('add_meta_boxes', function() use ($wp, $that){
			$wp->addMetaBox('jigoshop-product-data', __('Product Data', 'jigoshop'), array($that, 'dataBox'), $that::NAME, 'normal', 'high');
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
			'description' => \__('This is where you can add new products to your store.', 'jigoshop'),
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
			'has_archive' => false,
			'show_in_nav_menus' => false,
			'menu_position' => 56
		);
	}

	public function columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'thumbnail' => null,
			'title' => _x('Name', 'product', 'jigoshop'),
			'sku' => _x('SKU', 'product', 'jigoshop'),
			'featured' => _x('Is featured?', 'product', 'jigoshop'),
			'type' => _x('Type', 'product', 'jigoshop'),
			'stock' => _x('Stock', 'product', 'jigoshop'),
			'price' => _x('Price', 'product', 'jigoshop'),
			'date' => _x('Created at', 'product', 'jigoshop'),
		);

//		$columns["thumb"] = null;
//		$columns["featured"] = '<img src="' . jigoshop::assets_url() . '/assets/images/head_featured.png" alt="' . __('Featured', 'jigoshop') . '" />';
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
			case 'thumb':
//				if( 'trash' != $post->post_status ) {
//					echo '<a class="row-title" href="'.get_edit_post_link( $post->ID ).'">';
//					echo jigoshop_get_product_thumbnail( 'admin_product_list' );
//					echo '</a>';
//				}
//				else {
//					echo jigoshop_get_product_thumbnail( 'admin_product_list' );
//				}
				break;
			case 'price':
				echo $product->getPrice();
				break;
			case 'featured':
//				$url = wp_nonce_url( admin_url('admin-ajax.php?action=jigoshop-feature-product&product_id=' . $post->ID) );
//				echo '<a href="'.esc_url($url).'" title="'.__('Change','jigoshop') .'">';
//				if ($product->is_featured()) echo '<a href="'.esc_url($url).'"><img src="'.jigoshop::assets_url().'/assets/images/head_featured_desc.png" alt="yes" />';
//				else echo '<img src="'.jigoshop::assets_url().'/assets/images/head_featured.png" alt="no" />';
//				echo '</a>';
				break;
			case 'stock':
				echo $product->getStock()->getStatus();
//				if ( ! $product->is_type( 'grouped' ) && $product->is_in_stock() ) {
//					if ( $product->managing_stock() ) {
//						if ( $product->is_type( 'variable' ) && $product->stock > 0 ) {
//							echo $product->stock.' '.__('In Stock', 'jigoshop');
//						} else if ( $product->is_type( 'variable' ) ) {
//							$stock_total = 0;
//							foreach ( $product->get_children() as $child_ID ) {
//								$child = $product->get_child( $child_ID );
//								$stock_total += (int)$child->stock;
//							}
//							echo $stock_total.' '.__('In Stock', 'jigoshop');
//						} else {
//							echo $product->stock.' '.__('In Stock', 'jigoshop');
//						}
//					} else {
//						echo __('In Stock', 'jigoshop');
//					}
//				} elseif ( $product->is_type( 'grouped' ) ) {
//					echo __('Parent (no stock)', 'jigoshop');
//				} else {
//					echo '<strong class="attention">' . __('Out of Stock', 'jigoshop') . '</strong>';
//				}
				break;
			case 'type':
				echo $product->getType();
				break;
			case 'sku':
				echo $product->getSku();
				break;
			case 'date':
//				if ( '0000-00-00 00:00:00' == $post->post_date ) :
//					$t_time = $h_time = __( 'Unpublished', 'jigoshop' );
//					$time_diff = 0;
//				else :
//					$t_time = get_the_time( __( 'Y/m/d g:i:s A', 'jigoshop' ) );
//					$m_time = $post->post_date;
//					$time = get_post_time( 'G', true, $post );
//
//					$time_diff = time() - $time;
//
//					if ( $time_diff > 0 && $time_diff < 24*60*60 )
//						$h_time = sprintf( __( '%s ago', 'jigoshop' ), human_time_diff( $time ) );
//					else
//						$h_time = mysql2date( __( 'Y/m/d', 'jigoshop' ), $m_time );
//				endif;
//
//				echo '<abbr title="' . esc_attr( $t_time ) . '">' . apply_filters( 'post_date_column_time', $h_time, $post ) . '</abbr><br />';
//
//				if ( 'publish' == $post->post_status ) :
//					_e( 'Published', 'jigoshop' );
//				elseif ( 'future' == $post->post_status ) :
//					if ( $time_diff > 0 ) :
//						echo '<strong class="attention">' . __( 'Missed schedule', 'jigoshop' ) . '</strong>';
//					else :
//						_e( 'Scheduled', 'jigoshop' );
//					endif;
//				else :
//					_e( 'Draft', 'jigoshop' );
//				endif;
//				if ( $product->visibility ) :
//					echo ($product->visibility != 'visible')
//						? '<br /><strong class="attention">'.ucfirst($product->visibility).'</strong>'
//						: '';
//				endif;
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
		// TODO: Properly fetch available types
		$terms = $this->wp->getTerms('product_type');
		$currentType = $this->wp->getQueryParameter('product_type');

		Render::output('admin/products/typeFilter', array(
			'types' => $terms,
			'current' => $currentType
		));
	}


	/**
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since 		1.0
	 */
	public function dataBox()
	{
		$post = $this->wp->getGlobalPost();
		$product = $this->productService->findForPost($post);
		// TODO: Properly fetch available product types
		$types = $this->wp->applyFilters('jigoshop\\admin\\product\\types', array(
			'simple' => __('Simple', 'jigoshop'),
		));
//		$types = apply_filters( 'jigoshop_product_type_selector', array(
//			'simple'		=> __('Simple', 'jigoshop'),
//			'downloadable'	=> __('Downloadable', 'jigoshop'),
//			'grouped'		=> __('Grouped', 'jigoshop'),
//			'virtual'		=> __('Virtual', 'jigoshop'),
//			'variable'		=> __('Variable', 'jigoshop'),
//			'external'		=> __('External / Affiliate', 'jigoshop')
//		));
		$menu = $this->wp->applyFilters('jigoshop\\admin\\product\\menu', array(
			'general' => __('General', 'jigoshop'),
			'advanced' => __('Advanced', 'jigoshop'),
			'inventory' => __('Inventory', 'jigoshop'),
			'attributes' => __('Attributes', 'jigoshop'),
//			'grouping' => __('Grouping', 'jigoshop'), // TODO: Remove this, unnecessary tab...
//			'file' => __('Download', 'jigoshop'), // TODO: Remove this, move to Downloadable product
		));
		$tabs = $this->wp->applyFilters('jigoshop\\admin\\product\\tabs', array(
			'general' => array(),
			'advanced' => array(),
			'inventory' => array(),
			'attributes' => array(),
		));

//		add_action('admin_footer', 'jigoshop_meta_scripts');
//		wp_nonce_field('jigoshop_save_data', 'jigoshop_meta_nonce');

		Render::output('admin/products/box', array(
			'product' => $product,
			'types' => $types,
			'menu' => $menu,
			'tabs' => $tabs,
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
				return 'Jigoshop\\Product\\Type\\Simple';
			default:
				return $this->wp->applyFilters('jigoshop\\product\\type\\class', $type);
		}
	}
}