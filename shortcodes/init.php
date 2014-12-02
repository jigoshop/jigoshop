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
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
include_once('cart.php');
include_once('checkout.php');
include_once('my_account.php');
include_once('order_tracking.php');
include_once('pay.php');
include_once('thankyou.php');
include_once('product_list.php');
include_once('product_tag.php');

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
		'per_page' 	=> $jigoshop_options->get('jigoshop_catalog_per_page'),
		'columns' 	=> $jigoshop_options->get('jigoshop_catalog_columns'),
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
		'per_page' 	=> $jigoshop_options->get('jigoshop_catalog_per_page'),
		'columns' 	=> $jigoshop_options->get('jigoshop_catalog_columns'),
		'orderby'	=> $jigoshop_options->get('jigoshop_catalog_sort_orderby'),
		'order'		=> $jigoshop_options->get('jigoshop_catalog_sort_direction'),
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
		'per_page' 	=> $jigoshop_options->get('jigoshop_catalog_per_page'),
		'columns' 	=> $jigoshop_options->get('jigoshop_catalog_columns'),
		'orderby'	=> $jigoshop_options->get('jigoshop_catalog_sort_orderby'),
		'order'		=> $jigoshop_options->get('jigoshop_catalog_sort_direction'),
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
		'per_page'        => $jigoshop_options->get('jigoshop_catalog_per_page'),
		'columns' 	      => $jigoshop_options->get('jigoshop_catalog_columns'),
		'orderby'	      => $jigoshop_options->get('jigoshop_catalog_sort_orderby'),
		'order'		      => $jigoshop_options->get('jigoshop_catalog_sort_direction'),
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

	$atts = shortcode_atts(array(
		'class' => 'product',
		'id' => false,
		'sku' => false,
		'price' => 'yes',
	), $atts);

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

function jigoshop_search_shortcode() {

	$unique = uniqid();

	// Construct the form
	$form = '<form role="search" method="get" class="jigoshop-shortcode-searchform" id="searchform'.$unique.'" action="' . home_url() . '">';
	$form .= '<div>';
		$form .= '<label class="assistive-text" for="s'.$unique.'">' . __('Search for:', 'jigoshop') . '</label>';
		$form .= '<input type="text" value="' . get_search_query() . '" name="s" id="s'.$unique.'" placeholder="' . __('Search for products', 'jigoshop') . '" />';
		$form .= '<input class="jigoshop-shortcode-submit button" type="submit" id="searchsubmit'.$unique.'" value="' . __('Search', 'jigoshop') . '" />';
		$form .= '<input type="hidden" name="post_type" value="product" />';
	$form .= '</div>';
	$form .= '</form>';

	// Apply a filter to allow for additional fields
	echo apply_filters('jigoshop_product_search_shortcode', $form);

}

//### Sale products shortcode #########################################################

function jigoshop_sale_products( $atts ) {

	global $columns, $per_page, $paged;

	extract( shortcode_atts( array(
		'per_page'          => Jigoshop_Base::get_options()->get('jigoshop_catalog_per_page'),
		'columns'           => Jigoshop_Base::get_options()->get('jigoshop_catalog_columns'),
		'orderby'           => Jigoshop_Base::get_options()->get('jigoshop_catalog_sort_orderby'),
		'order'             => Jigoshop_Base::get_options()->get('jigoshop_catalog_sort_direction'),
		'pagination'        => false
		), $atts ) );

	$ids = jigoshop_product::get_product_ids_on_sale();
	if ( empty( $ids ) ) $ids = array( '0' );

	$args = array(
		'post_status'       => 'publish',
		'post_type'         => 'product',
		'posts_per_page'    => $per_page,
		'orderby'           => $orderby,
		'order'             => $order,
		'paged'             => $paged,
		'post__in'          => $ids
	);

	query_posts( $args );

	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	if ( $pagination ) do_action( 'jigoshop_pagination' );
	wp_reset_postdata();

	return ob_get_clean();

}

//### Shortcodes #########################################################

add_shortcode('product'                 , 'jigoshop_product');
add_shortcode('products'                , 'jigoshop_products');
add_shortcode('add_to_cart'             , 'jigoshop_product_add_to_cart');
add_shortcode('add_to_cart_url'         , 'jigoshop_product_add_to_cart_url');
add_shortcode('product_search'          , 'jigoshop_search_shortcode');

add_shortcode('recent_products'         , 'jigoshop_recent_products');
add_shortcode('featured_products'       , 'jigoshop_featured_products');
add_shortcode('jigoshop_category'       , 'jigoshop_product_category');
add_shortcode('sale_products'           , 'jigoshop_sale_products');
add_shortcode('product_tag'		, 'jigoshop_product_tag');

add_shortcode('jigoshop_cart'           , 'get_jigoshop_cart');
add_shortcode('jigoshop_checkout'       , 'get_jigoshop_checkout');
add_shortcode('jigoshop_order_tracking' , 'get_jigoshop_order_tracking');
add_shortcode('jigoshop_my_account'     , 'get_jigoshop_my_account');
add_shortcode('jigoshop_edit_address'   , 'get_jigoshop_edit_address');
add_shortcode('jigoshop_change_password', 'get_jigoshop_change_password');
add_shortcode('jigoshop_view_order'     , 'get_jigoshop_view_order');
add_shortcode('jigoshop_pay'            , 'get_jigoshop_pay');
add_shortcode('jigoshop_thankyou'       , 'get_jigoshop_thankyou');
