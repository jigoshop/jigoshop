<?php
foreach(glob( dirname(__FILE__)."/shortcodes/*.php" ) as $filename) include_once($filename);

### Recent Products #########################################################

function jigoshop_recent_products( $atts ) {
	
	global $columns, $per_page;
	
	extract(shortcode_atts(array(
		'per_page' 	=> '12',
		'columns' 	=> '4'
	), $atts));
	
	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'posts_per_page' => $per_page,
		'orderby' => 'date',
		'order' => 'desc',
		'meta_query' => array(
			array(
				'key' => 'visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN'
			)
		)
	);
	query_posts($args);
	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	wp_reset_query();
	
	return ob_get_clean();
}

### Featured Products #########################################################

function jigoshop_featured_products( $atts ) {
	
	global $columns, $per_page;
	
	extract(shortcode_atts(array(
		'per_page' 	=> '12',
		'columns' 	=> '4'
	), $atts));
	
	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'posts_per_page' => $per_page,
		'orderby' => 'date',
		'order' => 'desc',
		'meta_query' => array(
			array(
				'key' => 'visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN'
			),
			array(
				'key' => 'featured',
				'value' => 'yes'
			)
		)
	);
	query_posts($args);
	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	wp_reset_query();
	
	return ob_get_clean();
}

### Shortcodes #########################################################

add_shortcode('recent_products', 'jigoshop_recent_products');
add_shortcode('featured_products', 'jigoshop_featured_products');
add_shortcode('jigoshop_cart', 'jigoshop_cart');
add_shortcode('jigoshop_checkout', 'jigoshop_checkout');
add_shortcode('jigoshop_order_tracking', 'jigoshop_order_tracking');
add_shortcode('jigoshop_my_account', 'jigoshop_my_account');
add_shortcode('jigoshop_edit_address', 'jigoshop_edit_address');
add_shortcode('jigoshop_change_password', 'jigoshop_change_password');
add_shortcode('jigoshop_view_order', 'jigoshop_view_order');
add_shortcode('jigoshop_pay', 'jigoshop_pay');
add_shortcode('jigoshop_thankyou', 'jigoshop_thankyou');