<?php

function jigoshop_product_list($attributes) {
	$options = Jigoshop_Base::get_options();

	$attributes = shortcode_atts(array(
		'number' => $options->get_option('jigoshop_catalog_per_page'),
		'columns' => $options->get_option('jigoshop_catalog_columns'),
		'order_by' => 'date',
		'order' => 'desc',
		'orientation' => 'rows',
		'taxonomy' => 'product_cat',
		'terms' => '',
	), $attributes);

	$query = new WP_Query(array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts' => 1,
		'posts_per_page' => $attributes['number'],
		'orderby' => $attributes['order_by'],
		'order' => $attributes['order'],
		'tax_query' => array(
			array(
				'taxonomy' => $attributes['taxonomy'],
				'terms' => $attributes['terms'],
				'field' => 'slug',
			),
		),
		'meta_query' => array(
			array(
				'key' => 'visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN',
			),
		),
	));

	return jigoshop_render_result('shortcode/product_list', array(
		'orientation' => $attributes['orientation'],
		'columns' => $attributes['columns'],
		'products' => $query->get_posts(),
	));
}
add_shortcode('jigoshop_product_list', 'jigoshop_product_list');