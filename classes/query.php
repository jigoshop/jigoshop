<?php

require_once 'abstract/singleton.php';

/**
 * Main jigoshop catalog query class
 */
class jigoshop_query extends jigoshop_singleton {
	
	private $original_query;
	
	private $all_posts_in_view;
	
	/**
	 * Singleton constructor
	 */
	protected function __construct () {
		
		/* first parses the orginal request in a WP_Query instance 
		   to access the usefull boleans methods */
		$this->add_action('request', 'parse_original_request', 0);
		
		// alters the original request
		$this->add_action('request', 'request');
				
	}
		
	/*
	 * Is it a jigoshop product, product_tag or product_cat archive
	 * @return bool
	 */
	public function is_archive () {
		return     $this->original_query->is_post_type_archive( 'product' ) 
			 	|| $this->original_query->is_tax( 'product_cat' )
    	 	 	|| $this->original_query->is_tax( 'product_tag' );
	}
	
	/**
	 * Return true if we are on the admin side
	 * @return bool
	 */
	public function is_admin () {
		return $this->original_query->is_admin;
	}	
	
	/**
	 * Return true if we are on the search page
	 * @return bool
	 */
	public function is_search () {
		return $this->original_query->is_search;
	}
	
	/**
	 * Action and filter hooks
	 */
	
	/**
	 * Parses a request to the original_query var to access its boleans methods
	 * @param array $request
	 */
	public function parse_original_request ( $request ) {
		if($this->original_query) return $request;
		
		$this->original_query = new WP_Query();
    	$this->original_query->parse_query( $request ); 
    	
    	return $request;
	}
	
	/**
	 * Alters the main wordpress query when in a jigoshop page
	 * 
	 * The meta-query and the tax_query can be filtered using the loop_shop_tax-query and the
	 * loop_shop_tax_meta-query filters
	 * 
	 * The whole resulting request can be filtered using the jigoshop-request filter
	 * 
	 * @param array $request
	 */
	public function request ( $request ) {
		
		if( ! $this->is_archive() ) return $request; 
		
		if( ! $this->is_admin() ) {
			$request['post_status'] = 'publish';
       		$request['posts_per_page'] = apply_filters( 'loop_shop_per_page', get_option( 'jigoshop_catalog_per_page' ));
		}
		
		$request['tax_query']  = apply_filters('loop_shop_tax-query', $this->tax_query($request) );
		
		$request['meta_query'] = apply_filters('loop_shop_tax_meta-query', $this->meta_query( $request ) );

		if( ! $this->is_admin() )
			$this->set_all_posts_in_view ($request);
		
		return apply_filters('jigoshop-request', $request);	/* give it back to WordPress for query_posts() */
	}
	
	/**
	 * This function build the taxonomy query
	 * @param array $request
	 */
	private function tax_query ( $request ) {
		
		$tax_query = array('relation' => 'AND');
	
		// we add the product_cat an product_tag to the tax query so that is_tax() replies corectly
		if( !empty($request['product_cat']) ) {
			$tax_query[] = array (	'taxonomy' => 'product_cat',
									'field' => 'slug',
									'terms' => $request['product_cat'],
									'operator' => 'IN',
							 	 );
		}
		
		if( !empty($request['product_tag']) ) {
			$tax_query[] = array (	'taxonomy' => 'product_tag',
									'field' => 'slug',
									'terms' => $request['product_tag'],
									'operator' => 'IN',
							 	 );
		}
		
		return $tax_query;
	}
	
	/**
	 * This function build the meta query
	 * @param array $request
	 */
	private function meta_query( $request ) {
	
		$in = array( 'visible' );
		
		if ( $this->is_admin() ) {
			$in[] = 'hidden';
			$in[] = 'search';
			$in[] = 'catalog';
		}
		else {
			// NOTE: doesn't is_search true when $request['s'] is set ?!? 
			if ( $this->is_search() || isset( $request['s'] )) $in[] = 'search';
			if ( ! $this->is_search() && ! isset( $request['s'] ) ) $in[] = 'catalog';
		}
		
		$meta = $this->original_query->get( 'meta_query' );
		
		$meta[] = array(
			'key' => 'visibility',
			'value' => $in,
			'compare' => 'IN'
		);
		
		return $meta;
	}
	
	/**
	 * Gather all posts ids in current view, regardlesss of paging. 
	 * 
	 * This is usefull for filters to filter the products by price, by attributes etc..
	 * e.g. price filter widget
	 * 
	 * @param array $request The jigoshop request parameters
	 */
	private function set_all_posts_in_view ($request) {
		
		$request['posts_per_page'] = -1;
		$request['cache_results'] = false;
		$request['fields'] = 'ids';
		
		$wp_query = new WP_Query($request);
		
		$this->all_posts_in_view = $wp_query->posts;
				
	}
	
	/**
	 * Returns all posts ids in current view, regardlesss of paging.
	 * @return array
	 */
	public function posts_in_view () {
		return $this->all_posts_in_view;
	}
	
}