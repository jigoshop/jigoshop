<?php
/**
 * Product Data Save
 * 
 * Function for processing and storing all product data.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Admin
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

add_action( 'jigoshop_process_product_meta', 'jigoshop_process_product_meta', 1, 2 );

function jigoshop_process_product_meta( $post_id, $post ) {

	global $wpdb;
	
	
	$jigoshop_errors = array();
	
	$newdata = new jigoshop_sanitize( $_POST );
	
	$savedata = (array) get_post_meta( $post_id, 'product_data', true );
	
	$product_type = sanitize_title( $newdata->__get( 'product-type' ));
	
	wp_set_object_terms( $post_id, $product_type, 'product_type' );
	update_post_meta( $post_id, 'visibility', $newdata->__get( 'visibility' ));
	update_post_meta( $post_id, 'featured', $newdata->__get( 'featured' ));
	
	
	$SKU = get_post_meta( $post_id, 'SKU', true );
	$new_sku = $newdata->__get( 'sku' );
	if ( $new_sku !== $SKU ) :
		if ( $new_sku && !empty( $new_sku )) :
			if (
				$wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key='SKU' AND meta_value='%s';", $new_sku)) || 
				$wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID='%s' AND ID!='%s' AND post_type='product';", $new_sku, $post_id))
				) :
				$jigoshop_errors[] = __( 'Product SKU must be unique.', 'jigoshop' );
			else :
				update_post_meta( $post_id, 'SKU', $new_sku );
			endif;
		else :
			update_post_meta( $post_id, 'SKU', '' );
		endif;
	endif;
	
	
	$product_fields = array(
		'regular_price',
		'sale_price',
		'weight',
		'tax_status',
		'tax_class',
		'stock_status'
	);
	foreach ( $product_fields as $field_name ) {
		$savedata[$field_name] = $newdata->__get( $field_name );
	}
	
	
	if ( $product_type !== 'grouped' ) :
		
		$date_from = $newdata->__get( 'sale_price_dates_from' );
		$date_to = $newdata->__get( 'sale_price_dates_to' );
		
		if ( $date_from ) :
			update_post_meta( $post_id, 'sale_price_dates_from', strtotime( $date_from ));
		else :
			update_post_meta( $post_id, 'sale_price_dates_from', '' );
		endif;
		if ( $date_to ) :
			update_post_meta( $post_id, 'sale_price_dates_to', strtotime( $date_to ));
		else :
			update_post_meta( $post_id, 'sale_price_dates_to', '' );
		endif;
		if ( $date_to && ! $date_from ) :
			update_post_meta( $post_id, 'sale_price_dates_from', strtotime( 'NOW' ));
		endif;
		if ( $savedata['sale_price'] && $date_to == '' && $date_from == '' ) :
			update_post_meta( $post_id, 'price', $savedata['sale_price'] );
		else :
			update_post_meta( $post_id, 'price', $savedata['regular_price'] );
		endif;	
		if ( $date_from && strtotime( $date_from ) < strtotime( 'NOW' )) :
			update_post_meta( $post_id, 'price', $savedata['sale_price'] );
		endif;
		if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW' )) :
			update_post_meta( $post_id, 'price', $savedata['regular_price'] );
			update_post_meta( $post_id, 'sale_price_dates_from', '' );
			update_post_meta( $post_id, 'sale_price_dates_to', '' );
		endif;
	
	else :
		
		$savedata['sale_price'] = '';
		$savedata['regular_price'] = '';
		update_post_meta( $post_id, 'sale_price_dates_from', '' );
		update_post_meta( $post_id, 'sale_price_dates_to', '' );
		update_post_meta( $post_id, 'price', '' );
		
	endif;
	
	
	if ( get_option( 'jigoshop_manage_stock' ) == 'yes' ) :
		if ( $product_type !== 'grouped' && $newdata->__get( 'manage_stock' )) :
			update_post_meta( $post_id, 'stock', $newdata->__get( 'stock' ));
			$savedata['manage_stock'] = 'yes';
			$savedata['backorders'] = $newdata->__get( 'backorders' );
		else :
			update_post_meta( $post_id, 'stock', '0' );
			$savedata['manage_stock'] = 'no';
			$savedata['backorders'] = 'no';
		endif;
	endif;
	
	
	$new_attributes = array();
	$aposition = $newdata->__get( 'attribute_position' );
	$anames = $newdata->__get( 'attribute_names' );
	$avalues = $newdata->__get( 'attribute_values' );
	$avisibility = $newdata->__get( 'attribute_visibility' );
	$avariation = $newdata->__get( 'attribute_variation' );
	$ataxonomy = $newdata->__get( 'attribute_is_taxonomy' );
	for ( $i=0 ; $i < sizeof( $aposition ) ; $i++ ) {
		if ( empty( $avalues[$i] ) ) {
			if ( $ataxonomy[$i] && taxonomy_exists( 'pa_'.sanitize_title( $anames[$i] ))) :
				// delete these empty taxonomies from this product
				wp_set_object_terms( $post_id, NULL, 'pa_'.sanitize_title( $anames[$i] ));
			endif;
			continue;
		}
		$new_attributes[ sanitize_title( $anames[$i] ) ] = array(
			'name' => $anames[$i], 
			'value' => $avalues[$i],
			'position' => $aposition[$i],
			'visible' => !empty( $avisibility[$i] ) ? 'yes' : 'no',
			'variation' => !empty( $avariation[$i] ) ? 'yes' : 'no',
			'is_taxonomy' => !empty( $ataxonomy[$i] ) ? 'yes' : 'no'
		);

		if ( !empty( $ataxonomy[$i] )) :
			$taxonomy = $anames[$i];
			$value = $avalues[$i];
			if ( taxonomy_exists( 'pa_'.sanitize_title( $taxonomy ))) :
				wp_set_object_terms( $post_id, $value, 'pa_'.sanitize_title( $taxonomy ));
			endif;
		endif;

	}
	if ( ! function_exists( 'attributes_cmp' )) {
		function attributes_cmp( $a, $b ) {
			if ( $a['position'] == $b['position'] ) {
				return 0;
			}
			return ( $a['position'] < $b['position'] ) ? -1 : 1;
		}
	}
	uasort( $new_attributes, 'attributes_cmp' );
	update_post_meta( $post_id, 'product_attributes', $new_attributes );
	
	
	$savedata = apply_filters( 'process_product_meta', $savedata, $post_id );
	$savedata = apply_filters( 'filter_product_meta_' . $product_type, $savedata, $post_id );
	
	
	if ( function_exists( 'process_product_meta_' . $product_type )) {
		$meta_errors = call_user_func( 'process_product_meta_' . $product_type, $savedata, $post_id );
		if ( is_array( $meta_errors )) {
			$jigoshop_errors = array_merge( $jigoshop_errors, $meta_errors );
		}
	}
	
	update_post_meta( $post_id, 'product_data', $savedata );
	update_option( 'jigoshop_errors', $jigoshop_errors );

}