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

/**
 * used by the layered_nav widget and the price filter widget as they access the global ($all_post_ids)
 * ($all_post_ids also referenced but not used in jigoshop_product.class.php)
 * is run on the 'request' filter with highest priority to ensure it runs before main filter_catalog_query
 * gathers all product ID's into a global variable for use elsewhere ($all_post_ids)
 * calls jigoshop_get_product_ids() with a formed query to gather the id's
 *
 * @param array $request - the array representing the current WordPress request eg. post_type => 'product'
 * @return array - unaltered array of the intial request
 * @since 0.9.9
 * @TODO: implement better mechanism for gathering ID's for the widgets -JAP-
 *        This whole file should be wrapped in a class with protected variables and 
 *        proper set and get methods.
 **/
function jigoshop_get_product_ids_in_view( $request ) {

	global $all_post_ids;

	$all_post_ids = array();

	$this_query = new WP_Query();
    $this_query->parse_query( $request );

    if ( $this_query->is_post_type_archive( 'product' )
    	OR $this_query->is_tax( 'product_cat' )
    	OR $this_query->is_tax( 'product_tag' ) ) :

		$all_post_ids = jigoshop_get_product_ids( $request );
	endif;

	$all_post_ids[] = 0;

	return $request;
}
add_filter( 'request', 'jigoshop_get_product_ids_in_view', 0 );

/**
 * Prior to WordPress executing the main Catalog query, add in any further modifications to the query.
 * The post_type is already set to 'product'.  We'll look for published products, retrieving the amount
 * and sort based on Jigoshop Admin Settings.  We'll also use specific product ID's determined by widgets
 * We tap into the WP 'request' filter with a normal priority so this is done once only prior to query_posts().
 *
 * @param array $request - the array representing the current WordPress request eg. post_type => 'product'
 * @return array - the finished query object array is returned for WordPress to use
 * @since 0.9.9
 **/
function jigoshop_filter_catalog_query( $request ) {

	global $all_post_ids;
	
	$this_query = new WP_Query();
    $this_query->parse_query( $request );

	// we only work on Jigoshop product lists [ is_shop() and is_product_list() ]
    if ( $this_query->is_post_type_archive( 'product' )
    	OR $this_query->is_tax( 'product_cat' )
    	OR $this_query->is_tax( 'product_tag' ) ) :

        if ( ! $this_query->is_admin ) :	/* only apply these to the front end */
        	$request['post_status'] = 'publish';
        	$request['posts_per_page'] = apply_filters( 'loop_shop_per_page', get_option( 'jigoshop_catalog_per_page' ));
			
			// establish any filters for orderby, order and anything else added to the filter
			$filters = array();
			$filters = apply_filters( 'loop-shop-query', $filters );
			foreach( $filters as $key => $value ) :
				$request[$key] = $value;
			endforeach;
			
			// modify the query for specific product ID's for layered nav and price filter widgets
			$request['post__in'] = apply_filters( 'loop-shop-posts-in', $all_post_ids );
		endif;
		
		$request['meta_query'] = jigoshop_filter_meta_query( $this_query );
		
	endif;
	
    return $request;		/* give it back to WordPress for query_posts() */
}
add_filter( 'request', 'jigoshop_filter_catalog_query', 10 );


/**
 * Forms the meta query for visibility of products in both the Admin and Front end.
 *
 * @param array $this_query - WP_Query object array
 * @return array - the finished meta query for visibility array
 * @since 0.9.9
 **/
function jigoshop_filter_meta_query( $this_query ) {

	$in = array( 'visible' );
	if ( $this_query->is_admin ) :
		$in[] = 'hidden';
		$in[] = 'search';
		$in[] = 'catalog';
	else :
		if ( $this_query->is_search || isset( $request['s'] )) $in[] = 'search';
		if ( ! $this_query->is_search && ! isset( $request['s'] ) ) $in[] = 'catalog';
	endif;
	$meta = $this_query->get( 'meta_query' );
	$meta[] = array(
		'key' => 'visibility',
		'value' => $in,
		'compare' => 'IN'
	);
	
	return $meta;
}


//### Layered Nav Init
function jigoshop_layered_nav_init() {

	global $_chosen_attributes;

	$attribute_taxonomies = jigoshop_product::getAttributeTaxonomies();
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


/**
 * used by the layered_nav widget and the price filter widget as they access the global ($all_post_ids)
 * this is only called from jigoshop_get_product_ids_in_view() on the 'request' filter
 * only implemented this way for now to make things functional as per prior releases
 *
 * @param array $request - the array representing the current WordPress request eg. post_type => 'product'
 * @return array - an array of product ID's
 * @since 0.9.9
 * @TODO: implement better mechanism for gathering ID's for the widgets -JAP-
 **/
function jigoshop_get_product_ids( $request ) {

	$this_query = new WP_Query();
    $this_query->parse_query( $request );
	
    if ( $this_query->is_post_type_archive( 'product' )
    	OR $this_query->is_tax( 'product_cat' )
    	OR $this_query->is_tax( 'product_tag' ) ) :
		
		$args = array_merge(
			$this_query->query,
			array(
				'page_id' => '',
				'posts_per_page' => -1,
				'post_type' => 'product',
				'post_status' => 'publish',
				'meta_query' => jigoshop_filter_meta_query( $this_query )
			)
		);
		$custom_query  = new WP_Query( $args );
		
		$queried_post_ids = array();
		
		foreach ($custom_query->posts as $p) $queried_post_ids[] = $p->ID;
		
	endif;
	
	return $queried_post_ids;
}

//### Layered Nav
function jigoshop_layered_nav_query( $filtered_posts ) {

	global $_chosen_attributes;

	if (sizeof($_chosen_attributes)>0) :

		$matched_products = array();
		$filtered = false;

		foreach ($_chosen_attributes as $attribute => $values) :
			if (sizeof($values)>0) :
				foreach ($values as $value) :

					$posts = get_objects_in_term( $value, $attribute );
					if (!is_wp_error($posts) && (sizeof($matched_products)>0 || $filtered)) :
						$matched_products = array_intersect($posts, $matched_products);
					elseif (!is_wp_error($posts)) :
						$matched_products = $posts;
					endif;

					$filtered = true;

				endforeach;
			endif;
		endforeach;

		if ($filtered) :
			$matched_products[] = 0;
			$filtered_posts = array_intersect($filtered_posts, $matched_products);
		endif;

	endif;

	return $filtered_posts;
}
add_filter('loop-shop-posts-in', 'jigoshop_layered_nav_query');


//### Price Filtering
function jigoshop_price_filter( $filtered_posts ) {

	if (isset($_GET['max_price']) && isset($_GET['min_price'])) :

		$matched_products = array( 0 );

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

		$filtered_posts = array_intersect($matched_products, $filtered_posts);

	endif;

	return $filtered_posts;
}
add_filter( 'loop-shop-posts-in', 'jigoshop_price_filter' );
