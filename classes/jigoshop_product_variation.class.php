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
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
class jigoshop_product_variation extends jigoshop_product {

	public $variation_id;
	public $variation_data; // For formatting of variations
	public $sale_price_dates_from;
	public $sale_price_dates_to;


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
		$variable_stock = 0;
		foreach( $meta as $key => $array ) {
			if ( $array[0] ) $this->meta[$key] = $array;
			if ( $key == 'sku' ) if ( empty( $array[0] )) $tempsku = $ID;
			if ( $key == 'stock' ) {
				// if no value then parent stock value is used for variation stock tracking
				// otherwise the variation stock even if '0' as that is a value, is used
				if ( $array[0] == '' ) $variable_stock = '-9999999'; /* signal parent stock tracking */
				else $variable_stock = $array[0];
			}
		}

		// Merge with the variation data
		$this->variation_id = $ID;
		if ( isset( $this->meta['variation_data'][0] ))
			$this->variation_data = maybe_unserialize( $this->meta['variation_data'][0] );

		$sale_from = $this->sale_price_dates_from;
		$sale_to = $this->sale_price_dates_to;

		parent::__construct( $ID );

		// Restore the parent ID
		$this->ID = $parent_id;
		$this->id = $parent_id;
		if ( ! empty( $tempsku )) $this->sku = $tempsku;
		if(empty($this->sale_price_dates_from))
		$this->sale_price_dates_from = $sale_from;
		if(empty($this->sale_price_dates_to))
		$this->sale_price_dates_to = $sale_to;
		// signal parent stock tracking or variation stock tracking
		$this->stock = $variable_stock == '-9999999' ? $variable_stock : $this->stock;

		return $this;
	}

	public function get_sku()
	{
		$sku = get_post_meta($this->variation_id, 'sku', true);

		if ($sku === false) {
			$sku = parent::get_sku();
		}

		return $sku;
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
	 * Returns whether or not the variation is on sale.
	 *
	 * @return  bool
	 */
	public function is_on_sale() {

		$on_sale = false;
		$time = current_time('timestamp');

		// Check if the sale is still in range (if we have a range)
		if ( ! empty( $this->sale_price_dates_from ) && ! empty( $this->sale_price_dates_to ) ) {
			if ( $this->sale_price_dates_from	<= $time &&
				 $this->sale_price_dates_to >= $time &&
				 $this->sale_price ) {

				$on_sale = true;
			}
		}
		// Otherwise if we have a sale price
		if ( empty( $this->sale_price_dates_to ) && $this->sale_price ) $on_sale = true;

		return $on_sale;
	}

	public function get_stock()
	{
		$stock = get_post_meta($this->variation_id, 'stock', true);

		if ( empty($stock) || $stock == '-9999999' ) {
			$stock = parent::get_stock();
		}

		return (int)$stock;
	}

	/**
	 * Reduce stock level of the product
	 * Acts as an alias for modify_stock()
	 *
	 * @param   int   Amount to reduce by
	 * @return  int
	 */
	public function reduce_stock( $by = -1 ) {
		if ( $this->stock == '-9999999' ) {
			$_parent = new jigoshop_product( $this->ID );
			return $_parent->modify_stock( -$by );
		} else {
			return $this->modify_stock( -$by );
		}
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

		if ( self::get_options()->get('jigoshop_notify_no_stock_amount') >= 0
			&& self::get_options()->get('jigoshop_notify_no_stock_amount') >= $this->stock
			&& self::get_options()->get( 'jigoshop_hide_no_stock_product' )  == 'yes' ) {

			$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $this->variation_id ) );

		} else if ( $this->stock > self::get_options()->get('jigoshop_notify_no_stock_amount')
			&& get_post_status( $this->variation_id ) == 'draft'
			&& self::get_options()->get( 'jigoshop_hide_no_stock_product' )  == 'yes' ) {

			$wpdb->update( $wpdb->posts, array( 'post_status' => 'publish' ), array( 'ID' => $this->variation_id ) );
		}

		return $this->stock;
	}

	/**
	 * Update values of variation attributes using given values
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
