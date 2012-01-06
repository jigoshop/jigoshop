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
 * @package    Jigoshop
 * @category   Catalog
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
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

			if( $array[0] )
				$this->meta[$key] = $array;
		}

		// Merge with the variation data
		$this->variation_id = $ID;
		$this->variation_data = maybe_unserialize( $this->meta['variation_data'][0] );
		parent::__construct( $ID );
        
        // Restore the parent ID
        $this->ID = $parent_id;
        $this->id = $parent_id;

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
     * 
     * @uses    for get_available_attributes_variations()
     * @return  two dimensional array array of attributes and their values for this variation
     */
    public function get_variation_attributes() {
        return $this->variation_data; // @todo: This returns blank if its set to catch all, how would we deal with that?
    }

    /**
     * Modifies the stock levels for variations
     *
     * @param   int   Amount to modify
     * @return  int
     */
    public function modify_stock( $by ) {

        // Only do this if we're updating
        if ( ! $this->managing_stock() )
            return false;
        
        // +- = minus
        $this->stock = $this->stock + $by;
        $amount_sold = $this->stock_sold + $by;
        
        // Update & return the new value
        update_post_meta( $this->variation_id, 'stock', $this->stock );
        update_post_meta( $this->variation_id, 'stock_sold', $amount_sold );
        return $this->stock;
    }
    
    /**
     * Update values of variation attributes using given values
     * TODO: Why do we need this?
     *
     * @param   array $data array of attributes and values
     */
    function set_variation_attributes(array $data) {
        foreach($this->variation_data as $attribute=>$value) {
            if(isset($data[$attribute])) {
                $this->variation_data[$attribute] = $data[$attribute];
            }
        }
    }
}
