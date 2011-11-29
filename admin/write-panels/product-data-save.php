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

	// Product pricing
	// #TODO: Add context specific santizing here
	update_post_meta( $post_id, 'regular_price', sanitize_text_field($_POST['regular_price'])); 
	update_post_meta( $post_id, 'sale_price', sanitize_text_field($_POST['sale_price']));
	
	// Product taxation
	update_post_meta( $post_id, 'tax_status', sanitize_text_field($_POST['tax_status']));
	update_post_meta( $post_id, 'tax_class', sanitize_text_field($_POST['tax_class']));
	update_post_meta( $post_id, 'stock_status', sanitize_text_field($_POST['stock_status']));

	// Product physical properties
	update_post_meta( $post_id, 'weight', sanitize_text_field($_POST['weight']));
	update_post_meta( $post_id, 'length', sanitize_text_field($_POST['length']));
	update_post_meta( $post_id, 'width', sanitize_text_field($_POST['width']));
	update_post_meta( $post_id, 'height', sanitize_text_field($_POST['height']));

	// Other product info
	update_post_meta( $post_id, 'visibility', sanitize_text_field($_POST['visibility']));
	update_post_meta( $post_id, 'featured', sanitize_text_field($_POST['featured']));

	/// WARNING: Depreciated code
	$newdata = new jigoshop_sanitize( $_POST );
	$savedata = (array) get_post_meta( $post_id, 'product_data', true );
	//update_post_meta( $post_id, 'visibility', $newdata->__get( 'visibility' ));
	//update_post_meta( $post_id, 'featured', $newdata->__get( 'featured' ));
	/// End
	
	// Set the unique SKU (checks it too!)
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

	/// WARNING: Depreciated code
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
	/// End

	// #TODO: We need this but why?
	$product_type = sanitize_title( $newdata->__get( 'product-type' ));
	wp_set_object_terms( $post_id, $product_type, 'product_type' );
	
	if ( $product_type !== 'grouped' OR $product_type !== 'variable' ) :
		
		/// WARNING: Depreciated code
		$date_from = $newdata->__get( 'sale_price_dates_from' );
		$date_to = $newdata->__get( 'sale_price_dates_to' );

		// Configure from/to dates
		$date_from	= isset($_POST['sale_price_dates_from']) ? strtotime($_POST['sale_price_dates_from']) : null;
		$date_to	= isset($_POST['sale_price_dates_to']) ? strtotime($_POST['sale_price_dates_to']) : null;
 		
 		// Set the from date
		if ( $date_from ) {
			update_post_meta( $post_id, 'sale_price_dates_from', $date_from);
		}

		// Set the to date
		if ( $date_to ) {
			update_post_meta( $post_id, 'sale_price_dates_to', $date_to);
		} else if( $date_to AND ! $date_from ) {
			update_post_meta( $post_id, 'sale_price_dates_from', time());
		}

		/// WARNING: Depreciated code
		if ( $savedata['sale_price'] AND $date_to == '' AND $date_from == '' ) :
			update_post_meta( $post_id, 'price', $savedata['sale_price'] );
		else :
			update_post_meta( $post_id, 'price', $savedata['regular_price'] );
		endif;

		// If our time is now then set sale price
		if ( $date_from && strtotime( $date_from ) < strtotime( 'NOW' )) :
			update_post_meta( $post_id, 'price', $savedata['sale_price'] );
		endif;

		// if our time has passed reset to regular price
		if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW' )) :
			update_post_meta( $post_id, 'price', $savedata['regular_price'] );
			update_post_meta( $post_id, 'sale_price_dates_from', '' );
			update_post_meta( $post_id, 'sale_price_dates_to', '' );
		endif;
		/// END
	
	else :

		// TODO: Why save values in the db if they arent accepted? Read: Waste of time & space!
		$savedata['sale_price'] = '';
		$savedata['regular_price'] = '';
		update_post_meta( $post_id, 'sale_price_dates_from', '' );
		update_post_meta( $post_id, 'sale_price_dates_to', '' );
		update_post_meta( $post_id, 'price', '' );
		
	endif;
	
	// Update parent if grouped so price sorting works and stays in sync with the cheapest child
	if( (bool)$post->post_parent ) {
		$children_by_price = get_posts(array(
			'post_parent'		=> $post->post_parent,
			'orderby'			=> 'meta_value_num',
			'order'				=> 'DESC',
			'meta_key'			=> 'price',
			'posts_per_page'	=> 1,
			'post_type'			=> 'product',
			'fields'				=> 'ids',
		));

		if( $children ) {
			foreach($children as $child) {
				update_post_meta( $post->post_parent, 'price', 
					get_post_meta($child, 'price', true)
				);
			}
		}
	}
	
	// Stock Data
	if( get_option('jigoshop_manage_stock') == 'yes' ) {

		// Manage stock checkbox
		if( $product_type !== 'grouped' AND (bool)$_POST['manage_stock']) {

			update_post_meta( $post_id, 'stock', $_POST['stock']);
			update_post_meta( $post_id, 'manage_stock', 'yes');
			update_post_meta( $post_id, 'backorders', $_POST['backorders']);

			/// WARNING: Depreciated Code
			$savedata['manage_stock'] = 'yes';
			$savedata['backorders'] = $newdata->__get( 'backorders' );

		} else {
			// TODO: this could use cleaning up
			update_post_meta( $post_id, 'stock_status', 'instock' );
			update_post_meta( $post_id, 'stock', '0' );
			update_post_meta( $post_id, 'manage_stock', 'no' );
			update_post_meta( $post_id, 'backorders', 'no' );

			/// WARNING: Depreciated code
			$savedata['manage_stock'] = 'no';
			$savedata['backorders'] = 'no';
		}
	}
	
	
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
	
	/// WARNING: Depreciated code
	$savedata = apply_filters( 'process_product_meta', $savedata, $post_id );
	$savedata = apply_filters( 'filter_product_meta_' . $product_type, $savedata, $post_id );
	/// END

	do_action( 'jigoshop_process_product_meta_'.$product_type, $post_id );
	
	
	if ( function_exists( 'process_product_meta_' . $product_type )) {
		$meta_errors = call_user_func( 'process_product_meta_' . $product_type, $savedata, $post_id );
		if ( is_array( $meta_errors )) {
			$jigoshop_errors = array_merge( $jigoshop_errors, $meta_errors );
		}
	}
	
	/// WARNING: Depreciated code
	update_post_meta( $post_id, 'product_data', $savedata );

	// Save errors
	update_option( 'jigoshop_errors', $jigoshop_errors );

}