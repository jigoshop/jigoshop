<?php
/**
 * Product Variation Class
 *
 * The JigoShop product variation class handles product variation data.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Catalog
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class jigoshop_product_variation extends jigoshop_product {

	public $variation_id;
	public $variation_data; // For formatting of variations

	/**
	 * Extends the parent constructor to overwrite with variable data
	 *
	 * @param   int     ID of the product to load
	 * @return  object
	 */
	public function __construct( $ID ) {

		// Setup the product
		$parent_id = wp_get_post_parent_id( $ID );
		parent::__construct( $parent_id );

		// Get the meta & for each meta item overwrite with the variations ID
		$meta = get_post_custom( $ID );
		foreach( $meta as $key => $array ) {
			if ( $array[0] ) $this->meta[$key] = $array;
			if ( $key == 'sku' ) if ( empty( $array[0] )) $tempsku = $ID;
		}

		// Merge with the variation data
		$this->variation_id = $ID;
		if ( isset( $this->meta['variation_data'][0] ))
			$this->variation_data = maybe_unserialize( $this->meta['variation_data'][0] );
		

		parent::__construct( $ID );
				
		// Restore the parent ID
		$this->ID = $parent_id;
		$this->id = $parent_id;
		if ( ! empty( $tempsku )) $this->sku = $tempsku;
		
		return $this;
	}

	/**
	 * Get variation ID
	 *
	 * @return  int
	 */
	public function get_variation_id() {
		return (int) $this->variation_id;
	}

	/**
	 * Get variation attribute values
	 * @uses    for get_available_attributes_variations()
	 *
	 * @return  two dimensional array array of attributes and their values for this variation
	 */
	public function get_variation_attributes() {
		return $this->variation_data; // @todo: This returns blank if its set to catch all, how would we deal with that?
	}

	/**
	 * Returns the products current price, either regular or sale
	 *
	 * @return  int
	 */
	public function get_price() {

		$price = null;
		if ( $this->is_on_sale() ) {
			if ( strstr($this->sale_price,'%') ) {
				$price = round($this->regular_price * ( (100 - str_replace('%','',$this->sale_price) ) / 100 ), 4);
			} else if ( $this->sale_price ) {
				$price = $this->sale_price;
			}
		} else {
			$price = apply_filters('jigoshop_product_get_regular_price', $this->regular_price, $this->variation_id);
		}
		return apply_filters( 'jigoshop_product_get_price', $price, $this->variation_id );

	}

	/**
	 * Check the stock levels to unsure we have enough to match request
	 *
	 * @param   int $quantity   Amount to verify that we have
	 * @return  bool
	 */
	public function has_enough_stock( $quantity ) {
		// always work from a new product to check actual stock available
		// this product instance could be sitting in a Cart for a user and
		// another customer purchases the last available
		$temp = new jigoshop_product_variation( $this->get_variation_id() );
		return ($this->backorders_allowed() || $temp->stock >= $quantity);
	}

	/**
	 * Modifies the stock levels for variations
	 *
	 * @param   int   Amount to modify
	 * @return  int
	 */
	public function modify_stock( $by ) {
		global $wpdb;
		
		// Only do this if we're updating
		if ( ! $this->managing_stock() )
			return false;

		// +- = minus
		$this->stock = $this->stock + $by;
		$amount_sold = ( ! empty( $this->stock_sold ) ? $this->stock_sold : 0 ) + $by;

		// Update & return the new value
		update_post_meta( $this->variation_id, 'stock', $this->stock );
		update_post_meta( $this->variation_id, 'stock_sold', $amount_sold );
		
		if ( self::get_options()->get_option('jigoshop_notify_no_stock_amount') >= 0
			&& self::get_options()->get_option('jigoshop_notify_no_stock_amount') >= $this->stock
			&& self::get_options()->get_option( 'jigoshop_hide_no_stock_product' )  == 'yes' ) {
			
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $this->variation_id ) );
			
		} else if ( $this->stock > self::get_options()->get_option('jigoshop_notify_no_stock_amount')
			&& get_post_status( $this->variation_id ) == 'draft'
			&& self::get_options()->get_option( 'jigoshop_hide_no_stock_product' )  == 'yes' ) {
			
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'publish' ), array( 'ID' => $this->variation_id ) );
		}
		
		return $this->stock;
	}

	/**
	 * Update values of variation attributes using given values
	 * TODO: Why do we need this?
	 *
	 * @param   array $data array of attributes and values
	 */
	function set_variation_attributes(array $data) {
		if ( ! empty( $this->variation_data ) && is_array( $this->variation_data ) ) foreach ($this->variation_data as $attribute=>$value) {
			if(isset($data[$attribute])) {
				$this->variation_data[$attribute] = $data[$attribute];
			}
		}
	}
}
