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
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class jigoshop_product_meta
{
	public function __construct() {
		add_action( 'jigoshop_process_product_meta', array(&$this, 'save'), 1, 2 );
	}

	public function save( $post_id, $post ) {

		// Set the product type
		wp_set_object_terms( $post_id, sanitize_title($_POST['product-type']), 'product_type');

		// Process general product data
		update_post_meta( $post_id, 'regular_price', (isset($_POST['regular_price']) && $_POST['regular_price'] != null) ? jigoshop_sanitize_num($_POST['regular_price']) : '');

		$sale_price = ! empty( $_POST['sale_price'] )
			? ( ! strstr( $_POST['sale_price'], '%' ) ? jigoshop_sanitize_num( $_POST['sale_price'] ) : $_POST['sale_price'] )
			: '';
		if ( strstr( $_POST['sale_price'], '%' ) ) {
			update_post_meta( $post_id, 'sale_price', $sale_price );
		} else if ( ! empty( $sale_price ) && $sale_price < jigoshop_sanitize_num( $_POST['regular_price'] ) ) {
			update_post_meta( $post_id, 'sale_price', $sale_price );
		} else {
			// silently fail if entered sale price > regular price (or nothing entered)
			update_post_meta( $post_id, 'sale_price', '' );
		}
		// finally, if this product is a 'variable' or 'grouped', then there should never be a sale_price here
		// perhaps it was a 'simple' product once and was changed (see rhr #437)
		$terms = wp_get_object_terms( $post_id, 'product_type' );
		if ( ! empty( $terms ))
			if ( ! is_wp_error( $terms ))
				if ( sizeof( $terms ) == 1 )
					if ( in_array( $terms[0]->slug, array( 'variable', 'grouped' ) ) )
						delete_post_meta( $post_id, 'sale_price' );

		if ( isset( $_POST['weight'] ) )
			update_post_meta( $post_id, 'weight',        (float) $_POST['weight']);
		if ( isset( $_POST['length'] ) )
			update_post_meta( $post_id, 'length',        (float) $_POST['length']);
		if ( isset( $_POST['width'] ) )
			update_post_meta( $post_id, 'width',         (float) $_POST['width']);
		if ( isset( $_POST['height'] ) )
			update_post_meta( $post_id, 'height',        (float) $_POST['height']);

		update_post_meta( $post_id, 'tax_status',    $_POST['tax_status']);
		update_post_meta( $post_id, 'tax_classes',   isset($_POST['tax_classes']) ? $_POST['tax_classes'] : array() );

		update_post_meta( $post_id, 'visibility',    $_POST['product_visibility']);
		update_post_meta( $post_id, 'featured',      isset($_POST['featured']) );
		update_post_meta( $post_id, 'customizable',  $_POST['product_customize'] );
		update_post_meta( $post_id, 'customized_length',  $_POST['customized_length'] );

		// Downloadable Only
		if( $_POST['product-type'] == 'downloadable' ) {
			update_post_meta( $post_id, 'file_path',      $_POST['file_path']);
			update_post_meta( $post_id, 'download_limit', $_POST['download_limit']);
		}

		// Process the SKU
		if ( Jigoshop_Base::get_options()->get_option('jigoshop_enable_sku') !== 'no' ) {
			( $this->is_unique_sku( $post_id, $_POST['sku'] ) )
				? update_post_meta( $post_id, 'sku', $_POST['sku'])
				: delete_post_meta( $post_id, 'sku' );
		}

		// Process the attributes
		update_post_meta( $post_id, 'product_attributes', $this->process_attributes($_POST, $post_id));

		// Process the stock information
		$stockresult = $this->process_stock( $_POST );
		if ( is_array( $stockresult ) ) foreach( $stockresult as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		if( $_POST['product-type'] == 'external' ) {
			update_post_meta( $post_id, 'external_url', $_POST['external_url']);
			update_post_meta( $post_id, 'stock_status', 'instock' );
			update_post_meta( $post_id, 'manage_stock', false );
		}

		// Process the sale dates
		foreach( $this->process_sale_dates( $_POST ) as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		// Do action for product type
		do_action( 'jigoshop_process_product_meta_' . $_POST['product-type'], $post_id );
	}

	/**
	 * Processes the sale dates
	 *
	 * @param   array   The postback
	 * @return  array
	 **/
	private function process_sale_dates( array $post ) {

		// Set the default values
		$array = array(
			'sale_price_dates_from'  => false,
			'sale_price_dates_to'    => false,
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
				$array['sale_price_dates_to']   = $sale_end;
			}
		}

		return $array;
	}

	/**
	 * Processes the stock options
	 *
	 * @param   array   The postback
	 * @return  array
	 **/
	private function process_stock( array $post ) {

        $jigoshop_options = Jigoshop_Base::get_options();

		// If the global stock switch is off
		if ( !$jigoshop_options->get_option('jigoshop_manage_stock') )
			return false;

		// Don't hold stock info for external & grouped products
		if( $post['product-type'] === 'external' || $post['product-type'] === 'grouped' )
			return false;

		// Always return the stock switch
		$array = array(
			'manage_stock' 	=> isset($post['manage_stock']),
			'stock_status'  => isset($post['stock_status']) ? $post['stock_status'] : 0
		);

		// Store suitable stock data
		if( $array['manage_stock'] ) {
			$array['stock']        = absint( $post['stock'] );
			$array['backorders']   = $post['backorders']; // should have a space
			$array['stock_status'] = -1; // Discount if stock is managed
			if ( $jigoshop_options->get_option( 'jigoshop_hide_no_stock_product' ) == 'yes' ) {
				if ( $array['stock'] <= $jigoshop_options->get_option( 'jigoshop_notify_no_stock_amount' ) ) {
					if ( $post['product-type'] <> 'grouped' && $post['product-type'] <> 'variable' ) {
						update_post_meta( $post['ID'], 'visibility', 'hidden' );
					} else {
						// how to handle grouped and variable?  for now, ensure they are visible
						update_post_meta( $post['ID'], 'visibility', $_POST['product_visibility'] );
					}
				}
			}
		}

		return $array;
	}

	/**
	 * Check if an SKU is unique to both the posts & post_meta tables
	 *
	 * @param   $post_id   Post ID
	 * @param   $new_sku   The SKU to be checked
	 * @return  boolean
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
		$_unique_meta    = $wpdb->prepare("SELECT COUNT(1) FROM $wpdb->postmeta WHERE meta_key = 'sku' AND meta_value = %s", $new_sku);
		$_unique_post_id = $wpdb->prepare("SELECT COUNT(1) FROM $wpdb->posts WHERE ID = %s AND ID != %s AND post_type = 'product'", $new_sku, $post_id);

		if ( $wpdb->get_var($_unique_meta) || $wpdb->get_var($_unique_post_id) )
			return new WP_Error( 'jigoshop_unique_sku', __('Product SKU must be unique', 'jigoshop') );

		return true;
	}

	/**
	 * Processes the attribute data from postback into an array
	 *
	 * TODO: increase efficiency of this function
	 *
	 * @param   $post      the postback
	 * @param   $post_id   Post ID
	 * @return  array
	 **/
	private function process_attributes( array $post, $post_id ) {

		if ( ! isset($_POST['attribute_values']) )
			return false;

		$attr_names      = $post['attribute_names'];
		$attr_values     = $post['attribute_values'];
		$attr_visibility = isset($post['attribute_visibility']) ? $post['attribute_visibility'] : null;
		$attr_variation  = isset($post['attribute_variation']) ? $post['attribute_variation'] : null;
		$attr_is_tax     = $post['attribute_is_taxonomy'];
		$attr_position   = $post['attribute_position'];

		// Create empty attributes array
		$attributes = array();
		
		foreach( $attr_values as $key => $value ) {

			// Skip if no value
			if ( empty( $value )) continue;
			
			$has_tax = false;
			
			if ( ! is_array( $value ) && isset( $attr_variation[$key] ) && $attr_variation[$key] ) {
			 	$value = explode( ',', $value );
			 	$value = array_map( 'trim', $value );
			 	$value = implode( ',', $value );
			} else if ( ! is_array( $value ) ) {
				$value = trim( $value );
			}

			// If attribute is standard then create the relationship
			if ( $attr_is_tax[$key] && taxonomy_exists('pa_'.sanitize_title($attr_names[$key])) ) {
				wp_set_object_terms( $post_id, $value, 'pa_'.sanitize_title($attr_names[$key]) );
				$has_tax = true;
			}
			
			$attributes[ sanitize_title($attr_names[$key]) ] = array(
				'name'        => $attr_names[$key],
				'value'       => $value,
				'position'    => $attr_position[$key],
				'visible'     => isset( $attr_visibility[$key] ),
				'variation'   => isset( $attr_variation[$key] ) ? $attr_variation[$key] : null,
				'is_taxonomy' => $has_tax
			);
		}

		// Sort by position & return
		uasort($attributes, array($this, 'sort_attributes'));
		return $attributes;
	}

	/**
	 * Callback function to help sort the attributes array by position
	 *
	 * @param   $a   Master comparable
	 * @param   $b   Slave comparable
	 * @return  int
	 **/
	private function sort_attributes( $a, $b ) {
		if ($a['position'] == $b['position'])
			return 0;
		return ($a['position'] < $b['position']) ? -1 : 1;
	}
} new jigoshop_product_meta();
