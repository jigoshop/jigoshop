<?php

function jigoshop_product_list($attributes)
{
	$options = Jigoshop_Base::get_options();

	$attributes = shortcode_atts(array(
		'number' => $options->get('jigoshop_catalog_per_page'),
		'order_by' => 'date',
		'order' => 'desc',
		'orientation' => 'rows',
		'taxonomy' => 'product_cat',
		'terms' => '',
		'thumbnails' => 'show',
		'sku' => 'hide',
	), $attributes);

	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts' => 1,
		'posts_per_page' => $attributes['number'],
		'orderby' => $attributes['order_by'],
		'order' => $attributes['order'],
		'meta_query' => array(
			array(
				'key' => 'visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN',
			),
		),
	);

	if(!empty($attributes['taxonomy']) && !empty($attributes['terms'])){
		$args['tax_query'] = array(
			array(
				'taxonomy' => $attributes['taxonomy'],
				'terms' => $attributes['terms'],
				'field' => 'slug',
			),
		);
	}

	$query = new WP_Query($args);

	remove_action('jigoshop_before_shop_loop_item_title', 'jigoshop_template_loop_product_thumbnail', 10);
	if ($attributes['thumbnails'] === 'show') {
		add_action('jigoshop_before_shop_loop_item', 'jigoshop_product_thumbnail', 10, 2);
	}
	if ($attributes['sku'] === 'show') {
		add_action('jigoshop_after_shop_loop_item_title', 'jigoshop_product_sku', 9, 2);
	}

	$result = jigoshop_render_result('shortcode/product_list', array(
		'orientation' => $attributes['orientation'],
		'products' => $query->get_posts(),
		'has_thumbnails' => $attributes['thumbnails'] === 'show'
	));

	if ($attributes['sku'] === 'show') {
		remove_action('jigoshop_after_shop_loop_item_title', 'jigoshop_product_sku', 9);
	}
	if ($attributes['thumbnails'] === 'show') {
		remove_action('jigoshop_before_shop_loop_item', 'jigoshop_product_thumbnail', 10);
	}
	add_action('jigoshop_before_shop_loop_item_title', 'jigoshop_template_loop_product_thumbnail', 10, 2);

	return $result;
}

add_shortcode('jigoshop_product_list', 'jigoshop_product_list');

function jigoshop_product_sku($post, jigoshop_product $product)
{
	echo '<span class="sku">'.__('SKU', 'jigoshop').': '.$product->get_sku().'</span>';
}
function jigoshop_product_thumbnail($post, jigoshop_product $product)
{
	echo $product->get_image('shop_thumbnail');
}
