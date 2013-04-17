<?php
/**
 * Jigoshop shortcodes
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
include_once('cart.php');
include_once('checkout.php');
include_once('my_account.php');
include_once('order_tracking.php');
include_once('pay.php');
include_once('thankyou.php');

function jigoshop_shortcode_wrapper( $function, $atts = array() ) {
	// WordPress caching of shortcodes stripped out in version 1.4.9 for compatibility with Cache plugins on Cart and Checkout
	ob_start();
	call_user_func( $function, $atts );
	return ob_get_clean();
}

//### Recent Products #########################################################

function jigoshop_recent_products( $atts ) {

	global $columns, $per_page, $paged;
    $jigoshop_options = Jigoshop_Base::get_options();

	extract( shortcode_atts( array(
		'per_page' 	=> $jigoshop_options->get_option('jigoshop_catalog_per_page'),
		'columns' 	=> $jigoshop_options->get_option('jigoshop_catalog_columns'),
		'orderby'	=> 'date',
		'order'		=> 'desc',
		'pagination'=> false
	), $atts));

	$args = array(
		'post_type'          => 'product',
		'post_status'        => 'publish',
		'ignore_sticky_posts'=> 1,
		'posts_per_page'     => $per_page,
		'orderby'            => $orderby,
		'order'              => $order,
		'paged'              => $paged,
		'meta_query'         => array(
			array(
				'key'    => 'visibility',
				'value'  => array( 'catalog', 'visible' ),
				'compare'=> 'IN'
			)
		)
	);

	query_posts( $args );
	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	if($pagination) do_action('jigoshop_pagination');
	wp_reset_query();

	return ob_get_clean();
}

//### Multiple Products #########################################################

function jigoshop_products( $atts ){
	global $columns, $paged;
	$jigoshop_options = Jigoshop_Base::get_options();

	if ( empty( $atts )) return;

	extract( shortcode_atts( array(
		'per_page' 	=> $jigoshop_options->get_option('jigoshop_catalog_per_page'),
		'columns' 	=> $jigoshop_options->get_option('jigoshop_catalog_columns'),
		'orderby'	=> $jigoshop_options->get_option('jigoshop_catalog_sort_orderby'),
		'order'		=> $jigoshop_options->get_option('jigoshop_catalog_sort_direction'),
		'pagination'=> false
	), $atts));

	$args = array(
		'post_type'          => 'product',
		'post_status'        => 'publish',
		'posts_per_page'     => $per_page,
		'ignore_sticky_posts'=> 1,
		'orderby'            => $orderby,
		'order'              => $order,
		'paged'              => $paged,
		'meta_query'         => array(
			array(
				'key'    => 'visibility',
				'value'  => array( 'catalog', 'visible' ),
				'compare'=> 'IN'
			)
		)
	);

	if ( isset( $atts['skus'] )){
		$skus = explode( ',', $atts['skus'] );
		array_walk( $skus, create_function('&$val', '$val = trim($val);') );
		$args['meta_query'][] = array(
			'key' => 'sku',
			'value' => $skus,
			'compare' => 'IN'
		);
	}

	if ( isset( $atts['ids'] )){
		$ids = explode( ',', $atts['ids'] );
		array_walk( $ids, create_function('&$val', '$val = trim($val);') );
		$args['post__in'] = $ids;
	}

	query_posts( $args );
	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	if($pagination) do_action('jigoshop_pagination');
	wp_reset_query();

	return ob_get_clean();
}

//### Single Product ############################################################

function jigoshop_product( $atts ){

	if ( empty( $atts )) return;

	$args = array(
		'post_type'     => 'product',
		'posts_per_page'=> 1,
		'post_status'   => 'publish',
		'meta_query'    => array(
			array(
				'key'    => 'visibility',
				'value'  => array( 'catalog', 'visible' ),
				'compare'=> 'IN'
			)
		)
	);

	if ( isset( $atts['sku'] )){
		$args['meta_query'][] = array(
			'key'    => 'sku',
			'value'  => $atts['sku'],
			'compare'=> '='
		);
	}

	if ( isset( $atts['id'] )){
		$args['p'] = $atts['id'];
	}

	query_posts( $args );
	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	wp_reset_query();

	return ob_get_clean();
}

//### Featured Products #########################################################

function jigoshop_featured_products( $atts ) {

	global $columns, $per_page, $paged;
	$jigoshop_options = Jigoshop_Base::get_options();

	extract( shortcode_atts( array(
		'per_page' 	=> $jigoshop_options->get_option('jigoshop_catalog_per_page'),
		'columns' 	=> $jigoshop_options->get_option('jigoshop_catalog_columns'),
		'orderby'	=> $jigoshop_options->get_option('jigoshop_catalog_sort_orderby'),
		'order'		=> $jigoshop_options->get_option('jigoshop_catalog_sort_direction'),
		'pagination'=> false
	), $atts));

	$args = array(
		'post_type'          => 'product',
		'post_status'        => 'publish',
		'ignore_sticky_posts'=> 1,
		'posts_per_page'     => $per_page,
		'orderby'            => $orderby,
		'order'              => $order,
		'paged'              => $paged,
		'meta_query'         => array(
			array(
				'key'    => 'visibility',
				'value'  => array( 'catalog', 'visible' ),
				'compare'=> 'IN'
			),
			array(
				'key'   => 'featured',
				'value' => true
			)
		)
	);

	query_posts( $args );
	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	if($pagination) do_action('jigoshop_pagination');
	wp_reset_query();

	return ob_get_clean();
}

//### Category #########################################################

function jigoshop_product_category( $atts ) {

	global $columns, $per_page, $paged;
    $jigoshop_options = Jigoshop_Base::get_options();

	if ( empty( $atts ) ) return;

	extract( shortcode_atts( array(
		'slug'            => '',
		'per_page'        => $jigoshop_options->get_option('jigoshop_catalog_per_page'),
		'columns' 	      => $jigoshop_options->get_option('jigoshop_catalog_columns'),
		'orderby'	      => $jigoshop_options->get_option('jigoshop_catalog_sort_orderby'),
		'order'		      => $jigoshop_options->get_option('jigoshop_catalog_sort_direction'),
		'pagination'      => false,
		'tax_operator'    => 'IN'
	), $atts));

	if ( ! $slug ) return;

	/** Operator validation. */
	if( !in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) )
		$tax_operator = 'IN';

	/** Multiple category values. */
	if ( !empty($slug) ) {
		$slug = explode( ',', esc_attr( $slug ) );
		$slug = array_map('trim', $slug);
	}

	$args = array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => 1,
		'posts_per_page'         => $per_page,
		'orderby'                => $orderby,
		'order'                  => $order,
		'paged'                  => $paged,
		'meta_query'             => array(
			array(
				'key'       => 'visibility',
				'value'     => array( 'catalog', 'visible' ),
				'compare'   => 'IN'
			)
		),
		'tax_query' => array(
			array(
				'taxonomy'    => 'product_cat',
				'field'       => 'slug',
				'terms'       => $slug,
				'operator'    => $tax_operator
			)
		)
	);

	query_posts( $args );
	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	if($pagination) do_action('jigoshop_pagination');
	wp_reset_query();
	return ob_get_clean();
}

//### Add to cart URL for single product #########################################################

function jigoshop_product_add_to_cart_url( $atts ) {

	if ( empty( $atts ) ) return;

	global $wpdb;

	if ($atts['id']) :
		$product_meta = get_post( $atts['id'] );
	elseif ($atts['sku']) :
		$product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='sku' AND meta_value=%s LIMIT 1", $atts['sku']));
		$product_meta = get_post( $product_id );
	else :
		return;
	endif;

	if ($product_meta->post_type!=='product') return;

	$_product = new jigoshop_product( $product_meta->ID );

	return esc_url( $_product->add_to_cart_url() );
}

//### Cart button + optional price for single product #########################################################

function jigoshop_product_add_to_cart( $atts ) {

	if (empty($atts)) return;

	global $wpdb;

	if (!$atts['class']) $atts['class'] = 'product';

	if ($atts['id']) :
		$product_meta = get_post( $atts['id'] );
	elseif ($atts['sku']) :
		$product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='sku' AND meta_value=%s LIMIT 1", $atts['sku']));
		$product_meta = get_post( $product_id );
	else :
		return;
	endif;

	if ($product_meta->post_type!=='product') return;

	$_product = new jigoshop_product( $product_meta->ID );

	if (!$_product->is_visible()) return;

	ob_start();
	?>
	<p class="<?php echo esc_attr( $atts['class'] ); ?>">

		<?php if ($atts['price'] != 'no') echo $_product->get_price_html(); ?>

		<?php jigoshop_template_loop_add_to_cart( $product_meta, $_product ); ?>

	</p><?php

	return ob_get_clean();
}

//### Search shortcode #########################################################

function jigoshop_search_shortcode( $args ) {

	// Extract the arguments
	extract( $args );

	// Construct the form
	$form = '<form role="search" method="get" id="searchform" action="' . home_url() . '">';
	$form .= '<div>';
		$form .= '<label class="assistive-text" for="s">' . __('Search for:', 'jigoshop') . '</label>';
		$form .= '<input type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="' . __('Search for products', 'jigoshop') . '" />';
		$form .= '<input type="submit" id="searchsubmit" value="' . __('Search', 'jigoshop') . '" />';
		$form .= '<input type="hidden" name="post_type" value="product" />';
	$form .= '</div>';
	$form .= '</form>';

	// Apply a filter to allow for additional fields
	echo apply_filters('jigoshop_product_search_shortcode', $form, $instance);

}

//### Sale products shortcode #########################################################

function jigoshop_sale_products( $atts ) {
	global $columns, $per_page, $paged, $wpdb;

	extract(shortcode_atts(array(
		'per_page'                  => Jigoshop_Base::get_options()->get_option('jigoshop_catalog_per_page'),
		'columns'                   => Jigoshop_Base::get_options()->get_option('jigoshop_catalog_columns'),
		'orderby'                   => Jigoshop_Base::get_options()->get_option('jigoshop_catalog_sort_orderby'),
		'order'                     => Jigoshop_Base::get_options()->get_option('jigoshop_catalog_sort_direction'),
		'pagination'                => false
	), $atts));

	$today = current_time('timestamp');
	$posts_table = $wpdb->prefix . 'posts';
	$meta_table = $wpdb->prefix . 'postmeta';
	
	// TODO: currently as of Jigoshop 1.6, this still won't handle variations
	$sql = "SELECT {$posts_table}.* FROM {$posts_table} INNER JOIN {$meta_table} ON ({$posts_table}.ID = {$meta_table}.post_id) INNER JOIN {$meta_table} AS mt1 ON ({$posts_table}.ID = mt1.post_id) INNER JOIN {$meta_table} AS mt2 ON ({$posts_table}.ID = mt2.post_id) INNER JOIN {$meta_table} AS mt3 ON ({$posts_table}.ID = mt3.post_id) INNER JOIN {$meta_table} AS mt4 ON ({$posts_table}.ID = mt4.post_id) INNER JOIN {$meta_table} AS mt5 ON ({$posts_table}.ID = mt5.post_id) WHERE 1=1 AND {$posts_table}.post_type = 'product' AND ({$posts_table}.post_status = 'publish') AND ( ({$meta_table}.meta_key = 'visibility' AND CAST({$meta_table}.meta_value AS CHAR) IN ('catalog','visible')) AND (mt1.meta_key = 'sale_price' AND CAST(mt1.meta_value AS CHAR) != '') AND ((mt2.meta_key = 'sale_price_dates_from' AND CAST(mt2.meta_value AS SIGNED) <= %d) OR (mt3.meta_key = 'sale_price_dates_from' AND CAST(mt3.meta_value AS CHAR) = '')) AND ((mt4.meta_key = 'sale_price_dates_to' AND CAST(mt4.meta_value AS SIGNED) >= %d) OR (mt5.meta_key = 'sale_price_dates_to' AND CAST(mt5.meta_value AS CHAR) = '') )) GROUP BY {$posts_table}.ID ORDER BY {$posts_table}.post_title ".$order;
	
	global $jigoshop_sale_products;
	$jigoshop_sale_products = $wpdb->get_results( $wpdb->prepare( $sql, $today, $today ), OBJECT );
	ob_start();
	load_template( jigoshop::plugin_path() . '/templates/loop-on_sale.php',false );
	if ( $pagination ) do_action( 'jigoshop_pagination' );
	return ob_get_clean();	
}
add_shortcode('sale_products', 'jigoshop_sale_products');

//### Shortcodes #########################################################

add_shortcode('product'                 , 'jigoshop_product');
add_shortcode('products'                , 'jigoshop_products');
add_shortcode('add_to_cart'             , 'jigoshop_product_add_to_cart');
add_shortcode('add_to_cart_url'         , 'jigoshop_product_add_to_cart_url');
add_shortcode('product_search'          , 'jigoshop_search_shortcode');

add_shortcode('recent_products'         , 'jigoshop_recent_products');
add_shortcode('featured_products'       , 'jigoshop_featured_products');
add_shortcode('jigoshop_category'       , 'jigoshop_product_category');

add_shortcode('jigoshop_cart'           , 'get_jigoshop_cart');
add_shortcode('jigoshop_checkout'       , 'get_jigoshop_checkout');
add_shortcode('jigoshop_order_tracking' , 'get_jigoshop_order_tracking');
add_shortcode('jigoshop_my_account'     , 'get_jigoshop_my_account');
add_shortcode('jigoshop_edit_address'   , 'get_jigoshop_edit_address');
add_shortcode('jigoshop_change_password', 'get_jigoshop_change_password');
add_shortcode('jigoshop_view_order'     , 'get_jigoshop_view_order');
add_shortcode('jigoshop_pay'            , 'get_jigoshop_pay');
add_shortcode('jigoshop_thankyou'       , 'get_jigoshop_thankyou');
