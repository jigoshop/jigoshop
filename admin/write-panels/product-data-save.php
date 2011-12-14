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

// Not sure on how to organise this yet -Rob
class jigoshop_product_meta
{
	public function __construct() {
		add_action( 'jigoshop_process_product_meta', array(&$this, 'save'), 1, 2 );
	}

	public function save( $post_id, $post ) {

		// Set the product type
		wp_set_object_terms( $post_id, sanitize_title($_POST['product-type']), 'product_type');

		// Process general product data
		// How to sanitize this block?
		update_post_meta( $post_id, 'regular_price',		$_POST['regular_price']);
		update_post_meta( $post_id, 'sale_price',		$_POST['sale_price']);

		update_post_meta( $post_id, 'weight',			$_POST['weight']);

		update_post_meta( $post_id, 'tax_status',		$_POST['tax_status']);
		update_post_meta( $post_id, 'tax_class',			$_POST['tax_class']);

		update_post_meta( $post_id, 'visibility',		$_POST['visibility']);
		update_post_meta( $post_id, 'featured',			$_POST['featured']);

		// Downloadable Only
		if( $_POST['product-type'] == 'downloadable' ) {
			update_post_meta( $post_id, 'file_path',			$_POST['file_path']);
			update_post_meta( $post_id, 'download_limit',	$_POST['download_limit']);
		}

		// Process the SKU
		( $this->is_unique_sku( $post_id, $_POST['sku'] ) )
			? update_post_meta( $post_id, 'sku', $_POST['sku'])
			: delete_post_meta( $post_id, 'sku' );

		// Process the attributes
		update_post_meta( $post_id, 'product_attributes', $this->process_attributes($_POST, $post_id));

		// Process the stock information
		foreach( $this->process_stock( $_POST ) as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
		
		// Process the sale dates
		foreach( $this->process_sale_dates( $_POST ) as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		// Process upsells
		( ! empty($_post['upsell_ids']) )
			? update_post_meta( $post_id, 'upsell_ids', $_post['upsell_ids'] )
			: delete_post_meta( $post_id, 'upsell_ids' );
		
		// Process crossells
		( ! empty($_post['crosssell_ids']) )
			? update_post_meta( $post_id, 'crosssell_ids', $_post['crosssell_ids'] )
			: delete_post_meta( $post_id, 'crosssell_ids' );

		// Do action for product type
		do_action( 'jigoshop_process_product_meta_' . $_POST['product-type'], $post_id );
	}
	
	/**
	 * Processes the sale dates
	 *
	 * @param	array		The postback
	 * @return	array
	 **/
	private function process_sale_dates( array $post ) {

		// Set the default values
		$array = array(
			'sale_price_dates_from'		=> false,
			'sale_price_dates_to'		=> false,
		);

		// If our product is grouped remove the dates
		if( $post['product-type'] !== 'grouped' ) {

			// Only set sale dates if we have an end
			// Set start as current time if null
			if( $sale_end = strtotime($post['sale_price_dates_to']) ) {
				$sale_start	= ($post['sale_price_dates_from']) 
					? strtotime($post['sale_price_dates_from'])
					: current_time('timestamp');

				$array['sale_price_dates_from'] = $sale_start;
				$array['sale_price_dates_to'] = $sale_end;
			}
		}

		return $array;
	}

	/**
	 * Processes the stock options
	 *
	 * @param	array		The postback
	 * @return	array
	 **/
	private function process_stock( array $post ) {

		// If the global stock switch is off
		if ( ! get_option('jigoshop_manage_stock', false) )
			return false;

		// Don't hold stock info for external & grouped products
		if( $post['product-type'] === 'external' || $post['product-type'] === 'grouped' )
			return false;

		// Always return the stock switch
		$array = array(
			'manage_stock' 	=> (bool) $post['manage_stock'],
		);

		// Store suitable stock data
		if( $array['manage_stock'] ) {
			$array['stock'] 		= $post['stock'];
			$array['backorders']	= $post['backorders']; // should have a space
			$array['stock_status']	= -1; // Discount if stock is managed
		} else {
			$array['stock_status']	= $post['stock_status'];
		}

		return $array;
	}

	/**
	 * Check if an SKU is unique to both the posts & post_meta tables
	 *
	 * @param		$post_id		Post ID
	 * @param		$new_sku		The SKU to be checked
	 * @return 		boolean|WP_error
	 **/
	private function is_unique_sku( $post_id, $new_sku ) {
		global $wpdb;

		// Check for an SKU value
		if ( ! $new_sku )
			return false;

		// Skip check if sku is the same 
		if( $new_sku === get_post_meta( $post_id, 'sku', true ) )
			return true;

		// Check that the new sku does not already exist as a meta value or a post ID
		$_unique_meta = $wpdb->prepare("SELECT COUNT(1) FROM $wpdb->postmeta WHERE meta_key = 'sku' AND meta_value = %s", $new_sku);
		$_unique_post_id = $wpdb->prepare("SELECT COUNT(1) FROM $wpdb->posts WHERE ID = %s AND ID != %s AND post_type = 'product'", $new_sku, $post_id);

		if ( $wpdb->get_var($_unique_meta) || $wpdb->get_var($_unique_post_id) )
			return new WP_Error( 'jigoshop_unique_sku', __('Product SKU must be unique', 'jigoshop') );

		return true;
	}

	/**
	 * Processes the attribute data from postback into an array
	 *
	 * @param		$post			the postback
	 * @param		$post_id		Post ID
	 * @return 		array
	 **/
	private function process_attributes( array $post, $post_id ) {

		if ( ! isset($_POST['attribute_values']) )
			return false; 
		
		$attr_names			= $post['attribute_names']; // This data returns all attributes?
		$attr_values			= $post['attribute_values'];
		$attr_visibility		= $post['attribute_visibility'];
		$attr_variation		= $post['attribute_variation']; // Null so unsure
		$attr_is_tax		= $post['attribute_is_taxonomy']; // Likewise
		$attr_position		= $post['attribute_position']; // and this?

		// Create empty attributes array
		$attributes = array();

		foreach( $attr_values as $key => $value ) {

			// If attribute is standard then create the relationship
			if ( (bool) $attr_is_tax[$key] && taxonomy_exists('pa_'.sanitize_title($attr_names[$key])) ) {
				// TODO: Adding pa and sanitizing fixes the bug but why not automatic?
				wp_set_object_terms( $post_id, $value, 'pa_'.sanitize_title($attr_names[$key]) );
				$value = null; // Set as null
			}

			$attributes[ sanitize_title($attr_names[$key]) ] = array(
				'name'			=> $attr_names[$key],
				'value'			=> $value,
				'position'		=> (int)  $attr_position[$key],
				'visible'		=> (bool) $attr_visibility[$key],
				'variation'		=> (bool) $attr_variation[$key],
				'is_taxonomy'	=> (bool) $attr_is_tax[$key]
			);
		}

		// Sort by position & return
		uasort($attributes, array($this, 'sort_attributes'));
		return $attributes;
	}

	/**
	 * Callback function to help sort the attributes array by position
	 *
	 * @param		$a		Master comparable
	 * @param		$b		Slave comparable
	 * @return		int
	 **/
	private function sort_attributes( $a, $b ) {
		if ($a['position'] == $b['position']) return 0;
		return ($a['position'] < $b['position']) ? -1 : 1;
	}
} new jigoshop_product_meta();

/*
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

}*/