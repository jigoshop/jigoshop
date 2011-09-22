<?php

/**
 * Jigoshop Queries
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Core
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

//### Layered Nav Init
function jigoshop_layered_nav_init() {

	global $_chosen_attributes;

	$attribute_taxonomies = jigoshop::getAttributeTaxonomies();
	if ( $attribute_taxonomies ) :
		foreach ($attribute_taxonomies as $tax) :

	    	$attribute = strtolower(sanitize_title($tax->attribute_name));
	    	$taxonomy = 'pa_' . $attribute;
	    	$name = 'filter_' . $attribute;

	    	if (isset($_GET[$name]) && taxonomy_exists($taxonomy)) $_chosen_attributes[$taxonomy] = explode(',', $_GET[$name] );

	    endforeach;
    endif;

}
add_action('init', 'jigoshop_layered_nav_init', 1);

function jigoshop_layered_nav_tax_query ( $tax_query ) {

	global $_chosen_attributes;

	if ( !sizeof($_chosen_attributes)>0 ) return $tax_query;
	
	foreach ($_chosen_attributes as $attribute => $values) {
		if (sizeof($values)<1) continue;
		
		$tax_query[] = array (	'taxonomy' => $attribute,
								'field' => 'id',
								'terms' => $values,
								'operator' => 'IN',
							 );
	}
	
	return $tax_query;
	
}
add_filter('loop_shop_tax-query', 'jigoshop_layered_nav_tax_query');


//### Price Filtering
function jigoshop_price_request ( $request ) {

	if ( !isset($_GET['max_price']) && !isset($_GET['min_price'])) return $request;

	$matched_products = array();

	$matched_products_query = get_posts(array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'price',
					'value' => array( $_GET['min_price'], $_GET['max_price'] ),
					'type' => 'NUMERIC',
					'compare' => 'BETWEEN'
				)
			),
			'tax_query' => array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => 'grouped',
					'operator' => 'NOT IN'
				)
			)
		));

		if ($matched_products_query) :

			foreach ($matched_products_query as $product) :
				$matched_products[] = $product->ID;
			endforeach;

		endif;

		// Get grouped product ids
		$grouped_products = get_objects_in_term( get_term_by('slug', 'grouped', 'product_type')->term_id, 'product_type' );

		if ($grouped_products) foreach ($grouped_products as $grouped_product) :

			$children = get_children( 'post_parent='.$grouped_product.'&post_type=product' );

			if ($children) foreach ($children as $product) :
				$price = get_post_meta( $product->ID, 'price', true);

				if ($price<=$_GET['max_price'] && $price>=$_GET['min_price']) :

					$matched_products[] = $grouped_product;

					break;

				endif;
			endforeach;

		endforeach;

	if( sizeof($matched_products) ) {
		if(!isset($request['post__in'])) $request['post__in'] = array();
		
		$request['post__in'] = array_merge($request['post__in'], $matched_products);
	}

	
	
	return $request;
}
add_filter( 'jigoshop-request', 'jigoshop_price_request' );

/**
 * Find and get a specific variation
 *
 * @todo unused, needed?  (seconded -JAP-)
 *
 * @since 		1.0
 */
function jigoshop_find_variation( $variation_data = array() ) {

	foreach ($variation_data as $key => $value) :

		if (!strstr($key, 'tax_')) continue;

		$variation_query[] = array(
			'key' 		=> $key,
			'value' 	=> array( $value ),
			'compare'	=> 'IN'
		);

	endforeach;

	// do the query
	$args = array(
		'post_type' 		=> 'product_variation',
		'orderby'			=> 'id',
		'order'				=> 'desc',
		'posts_per_page'	=> 1,
		'meta_query' 		=> $variation_query
	);
	$posts = get_posts( $args );

	if (!$posts) :

		// Wildcard search
		$variation_query = array();
		foreach ($variation_data as $key => $value) :

			if (!strstr($key, 'tax_')) continue;

			$variation_query[] = array(
				'key' 		=> $key,
				'value' 	=> array( $value, '' ),
				'compare'	=> 'IN'
			);

		endforeach;
		$args['meta_query'] = $variation_query;

		$posts = get_posts( $args );

	endif;

	if (!$posts) return false;

	return $posts[0]->ID;

}
