<?php
/**
 * Products Class
 * 
 * The JigoShop products class loads all products and calculates counts
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Catalog
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
class jigoshop_products {

	var $simple_count;
	var $variable_count;
	var $grouped_count;
	var $downloadable_count;
	var $virtual_count;
	
	var $publish_count;
	var $draft_count;
	var $trash_count;

	var $catalog_count;
	var $search_count;
	var $hidden_count;
	
	function __construct() {
		
		$args = array(
			'post_type'	=> 'product',
			'posts_per_page' => -1,
			'ignore_sticky_posts'	=> 1,
			'meta_query' => array(
				array(
					'key' => 'visibility',
					'value' => array( 'visible', 'search', 'catalog', 'hidden' ),
					'compare' => 'IN'
				)
			),
			'tax_query' => array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'operator' => 'IN'
				)
			)
		);
		
		$args['tax_query'][0]['terms'] = 'simple';
		$this_query = new WP_Query( $args );
		$this->simple_count = $this_query->post_count;

		$args['tax_query'][0]['terms'] = 'variable';
		$this_query = new WP_Query( $args );
		$this->variable_count = $this_query->post_count;
		
		$args['tax_query'][0]['terms'] = 'grouped';
		$this_query = new WP_Query( $args );
		$this->grouped_count = $this_query->post_count;

		$args['tax_query'][0]['terms'] = 'downloadable';
		$this_query = new WP_Query( $args );
		$this->downloadable_count = $this_query->post_count;

		$args['tax_query'][0]['terms'] = 'virtual';
		$this_query = new WP_Query( $args );
		$this->virtual_count = $this_query->post_count;
		
		unset( $args['tax_query'] );
		
		$args['meta_query'][0]['value'] = 'catalog';
		$this_query = new WP_Query( $args );
		$this->catalog_count = $this_query->post_count;

		$args['meta_query'][0]['value'] = 'search';
		$this_query = new WP_Query( $args );
		$this->search_count = $this_query->post_count;

		$args['meta_query'][0]['value'] = 'hidden';
		$this_query = new WP_Query( $args );
		$this->hidden_count = $this_query->post_count;
		
		// these return incorrect results for hidden and trashed items -- checking -JAP-
//		$this->simple_count			= get_term_by( 'slug', 'simple', 'product_type' )->count;
//		$this->variable_count		= get_term_by( 'slug', 'variable', 'product_type' )->count;
//		$this->grouped_count		= get_term_by( 'slug', 'grouped', 'product_type' )->count;
//		$this->downloadable_count	= get_term_by( 'slug', 'downloadable', 'product_type' )->count;
//		$this->virtual_count		= get_term_by( 'slug', 'virtual', 'product_type' )->count;
		
		$this->trash_count			= wp_count_posts( 'product' )->trash;
		$this->draft_count			= wp_count_posts( 'product' )->draft;
		$this->publish_count		= wp_count_posts( 'product' )->publish;
		
	}
	
}

?>
