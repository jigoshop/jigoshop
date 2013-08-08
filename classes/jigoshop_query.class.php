<?php
/**
 * Jigoshop Catalog Query Class
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


class jigoshop_catalog_query extends Jigoshop_Singleton {

	private static $original_query;

	/**
	 * Singleton constructor
	 */
	protected function __construct() {

		// parses the orginal request into a WP_Query instance to access useful boolean methods
		// use the highest priority to get it done first
		self::add_filter( 'request', 'parse_original_request', 0 );

		// alters the original request with a default priority
		self::add_filter( 'request', 'catalog_query_filter' );

		self::add_filter( 'request', 'jigoshop_get_product_ids_in_view', 1 );

	}


	/*
	 * Is this query for Shop, product_tag or product_cat listings
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public static function is_product_list() {

		if ( self::$original_query ) return self::$original_query->is_post_type_archive( 'product' )
			 	|| self::$original_query->is_tax( 'product_cat' )
		 	 	|| self::$original_query->is_tax( 'product_tag' );
		return false;
	}


	/**
	 * Return true if we are on the Search page
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public static function is_search() {

		if ( self::$original_query ) return self::$original_query->is_search;
		return false;

	}


	/**
	 * Action and filter hooks
	 */

	/**
	 * Parses a request to the 'original_query' var to access its boolean methods
	 * This is called with highest priority to get first look at the request
	 *
	 * @param array $request
	 *
	 * @since 1.0
	 */
	public function parse_original_request( $request ) {

		if ( self::$original_query ) return $request;

		self::$original_query = new WP_Query();

		self::$original_query->parse_query( $request );

		return $request;
	}


	/**
	 * Alters the main wordpress query when on a Jigoshop product listing.
	 *
	 * The meta-query and tax_query can be filtered using the 'loop_shop_tax_query' and 'loop_shop_tax_meta_query' filters.
	 *
	 * Use the 'loop_shop_per_page' filter for adjusting the # of products to show per page on front end Product lists.
	 *
	 * Use the 'loop-shop-query' filter to adjust sort order and direction or other front end only arguments.
	 *
	 * The whole resulting request can be filtered using the 'jigoshop-request' filter
	 *
	 * @param array $request - the parsed request from 'parse_original_request' that sets up $this->original_query
	 * @return array - the altered request array is returned to be submitted to 'query_posts' with all filtering done.
	 *
	 * @since 1.0
	 */
	public function catalog_query_filter( $request ) {

		global $jigoshop_all_post_ids_in_view;

		// we only work on Jigoshop product lists
		if ( ! self::is_product_list() ) return $request;

		$request['post_status'] = 'publish';
		$request['posts_per_page'] = apply_filters( 'loop_shop_per_page', Jigoshop_Base::get_options()->get_option( 'jigoshop_catalog_per_page' ));

		// establish any filters for orderby, order and anything else added to the filter
		$filters = array();
		$filters = apply_filters( 'loop-shop-query', $filters );
		foreach( $filters as $key => $value ) :
			$request[$key] = $value;
		endforeach;

		$request['tax_query'] = apply_filters( 'loop_shop_tax_query', $this->tax_query( $request ));

		$request['meta_query'] = apply_filters( 'loop_shop_tax_meta_query', $this->meta_query( $request ));

		// modify the query for specific product ID's for layered nav and price filter widgets
		$request['post__in'] = apply_filters( 'loop-shop-posts-in', $jigoshop_all_post_ids_in_view );

		return apply_filters( 'jigoshop-request', $request );	/* give it back to WordPress for query_posts() */
	}


	/**
	 * This function builds the taxonomy query for Product Categories and Tags.
	 *
	 * @param array $request
	 *
	 * @since 1.0
	 */
	private function tax_query( $request ) {

		$tax_query = array( 'relation' => 'AND' );

		// we add 'product_cat' and 'product_tag' to the tax query so that is_tax() replies correctly
		if ( ! empty( $request['product_cat'] ) ) {
			$tax_query[] = array (	'taxonomy'	=> 'product_cat',
									'field'		=> 'slug',
									'terms'		=> $request['product_cat'],
									'operator'	=> 'IN',
							 	 );
		}

		if ( ! empty( $request['product_tag'] ) ) {
			$tax_query[] = array (	'taxonomy'	=> 'product_tag',
									'field'		=> 'slug',
									'terms'		=> $request['product_tag'],
									'operator'	=> 'IN',
							 	 );
		}

		return $tax_query;
	}


	/**
	 * This function builds the meta query for products.
	 *
	 * @param array $request
	 *
	 * @since 1.0
	 */
	private function meta_query( $request ) {

		$in = array( 'visible' );

		if ( self::is_search() ) $in[] = 'search';
		else $in[] = 'catalog';

		$meta = self::$original_query->get( 'meta_query' );

		$meta[] = array(
			'key' => 'visibility',
			'value' => $in,
			'compare' => 'IN'
		);

		return $meta;
	}


	/**
	 * used by the layered_nav widget and the price filter widget as they access the global ($all_post_ids)
	 * is run on the 'request' filter with highest priority to ensure it runs before main filter_catalog_query
	 * gathers all product ID's into a global variable for use elsewhere ($all_post_ids)
	 *
	 * @param array $request - the array representing the current WordPress request eg. post_type => 'product'
	 * @return array - unaltered array of the intial request
	 * @since 0.9.9
	 **/
	function jigoshop_get_product_ids_in_view( $request ) {

		global $jigoshop_all_post_ids_in_view;

		$jigoshop_all_post_ids_in_view = array();

		$this_query = new WP_Query();
		$this_query->parse_query( $request );

		if ( $this_query->is_post_type_archive( 'product' )
			|| $this_query->is_tax( 'product_cat' )
			|| $this_query->is_tax( 'product_tag' ) ) :

			$args = array_merge(
				$this_query->query,
				array(
					'page_id'       => '',
					'fields'        => 'ids',
					'posts_per_page'=> -1,
					'post_type'     => 'product',
					'post_status'   => 'publish',
					'meta_query'    => self::meta_query( $this_query )
				)
			);

			$custom_query = get_posts($args);

			$jigoshop_all_post_ids_in_view = array_merge($jigoshop_all_post_ids_in_view, $custom_query);

		endif;

		$jigoshop_all_post_ids_in_view[] = 0;

		return $request;
	}

}