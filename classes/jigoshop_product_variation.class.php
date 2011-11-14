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
	
	public $variation;
	public $variation_data;
	public $variation_id;
	public $variation_has_weight;
	public $variation_has_price;
	public $variation_has_sale_price;
	public $variation_has_stock;
	public $variation_has_sku;
	
	/**
	 * Loads all product data from custom fields
	 *
	 * @param   int		$id		ID of the product to load
	 */
	function jigoshop_product_variation( $variation_id ) {
		//load variation data
		$this->variation_id = $variation_id;
		$product_custom_fields = get_post_custom( $this->variation_id );
		$this->get_variation_post_data();
        
        //load variation parent data
        //@todo this data are already loaded in the most cases, we should think about passing parent product object to constructor
		$this->id = $this->variation->post_parent;
		$parent_custom_fields = get_post_custom( $this->id );

        /*
         * Set variation information by combining variation options and parent options
         */
        $this->sku = $this->id;
        if (isset($product_custom_fields['SKU'][0]) && !empty($product_custom_fields['SKU'][0])) {
            $this->variation_has_sku = true;
			$this->sku = $product_custom_fields['SKU'][0];
        } else if (isset($parent_custom_fields['SKU'][0]) &&!empty($parent_custom_fields['SKU'][0])) {
            $this->sku = $parent_custom_fields['SKU'][0];
        }
        
        $this->stock = 0;
		if (isset($product_custom_fields['stock'][0]) && !empty($product_custom_fields['stock'][0])) {
			$this->variation_has_stock = true;
			$this->stock = $product_custom_fields['stock'][0];
        } else if (isset($parent_custom_fields['stock'][0])) {
            $this->stock = $parent_custom_fields['stock'][0];
        }

        $this->price = 0;
        if (isset($product_custom_fields['price'][0]) && !empty($product_custom_fields['price'][0])) {
			$this->variation_has_price = true;
			$this->price = $product_custom_fields['price'][0];
        } else if (isset($parent_custom_fields['price'][0])) {
            $this->price = $parent_custom_fields['price'][0];
        }
        
        if (isset($product_custom_fields['sale_price'][0]) && !empty($product_custom_fields['sale_price'][0])) {
			$this->variation_has_sale_price = true;
			$this->sale_price = $product_custom_fields['sale_price'][0];
        }
        
        $this->data = '';
        if (isset($parent_custom_fields['product_data'][0])) {
            $this->data = maybe_unserialize($parent_custom_fields['product_data'][0]);
        }
        
        $this->attributes = array();
        if (isset($parent_custom_fields['product_attributes'][0])) {
            $this->attributes = maybe_unserialize($parent_custom_fields['product_attributes'][0]);
        }

        $this->visibility = 'hidden';
        if (isset($parent_custom_fields['visibility'][0])) {
            $this->visibility = $parent_custom_fields['visibility'][0];
            
            //if the main product is visible, but vairiation is not enabled make it hidden
            if($this->visibility == 'visible' && $this->variation->post_status == 'private') {
                $this->visibility = 'hidden';
            }
        }
        
        if (isset($product_custom_fields['weight'][0]) && !empty($product_custom_fields['weight'][0])) {
			$this->variation_has_weight = true;
			$this->data['weight'] = $product_custom_fields['weight'][0];
        }
        
        //process variation data
		$this->variation_data = array();
		
		foreach ($product_custom_fields as $name => $value) {
			if (!strstr($name, 'tax_')) {
                continue;
            }
			
			$this->variation_data[$name] = $value[0];
        }
        
		// Again just in case, to fix WP bug
		$this->data = maybe_unserialize( $this->data );
		$this->attributes = maybe_unserialize( $this->attributes );
		$this->product_type = 'variable';
			
		if ($this->data) {
			$this->exists = true;		
        } else {
			$this->exists = false;	
        }
		
		//parent::jigoshop_product( $this->variation->post_parent );
	}

	/** Get the product's post data */
	function get_variation_post_data() {
		if (empty($this->variation)) {
			$this->variation = get_post( $this->variation_id );
        }
        
		return $this->variation;
	}
    
    /**
     * Get variation ID
     * 
     * @return int
     */
    function get_variation_id() {
        return (int)$this->variation_id;
    }
    
    /**
     * Is variation visible/enabled?
     * 
     * @return bool
     */
    function is_visible() {
        return ($this->visibility == 'visible') ? true : false;
    }
    
    /**
     * Get variation attribute values
     * 
     * @return two dimensional array array of attributes and their values for this variation
     */
    function get_variation_attributes() {
        return $this->variation_data;
    }
    
    /**
     * Update values of variation attributes using given values
     * 
     * @param array $data array of attributes and values
     */
    function set_variation_attributes(array $data) {
        foreach($this->variation_data as $attribute=>$value) {
            if(isset($data[$attribute])) {
                $this->variation_data[$attribute] = $data[$attribute];
            }
        }
    }
	
	/**
	 * Determines whether this variation is on Sale
	 * 
	 * @return boolean - true or false depending on variation sale price and parent dates
	 */
	function variation_is_on_sale() {
	
		$on_sale = false;
	
		if ( $this->variation_has_price ) :
			if ( $this->variation_has_sale_price ) :
				$on_sale = $this->in_sale_date_range();
			endif;
		endif;
		
		return $on_sale;
	}
	
	/** Returns the product's price */
	function get_price() {

        if ($this->variation_has_price) {
            if ($this->variation_is_on_sale()) {
                return $this->sale_price;
            }
            
            return $this->price;
        }

        return parent::get_price();
    }
	
	/** Returns the price in html format */
	function get_price_html() {
		if ($this->variation_has_price) {
			if ($this->price) {
				if ($this->variation_is_on_sale()) {
					return '<del>'.jigoshop_price( $this->price ).'</del> <ins>'.jigoshop_price( $this->sale_price ).'</ins>';
                }
				return jigoshop_price( $this->price );
            }
	
			return '';
        }
        
		return jigoshop_price(parent::get_price());
	}
	
	/**
	 * Reduce stock level of the product
	 *
	 * @param   int		$by		Amount to reduce by
	 */
	function reduce_stock( $by = 1 ) {
		if ($this->variation_has_stock) :
			if ($this->managing_stock()) :
				$reduce_to = $this->stock - $by;
				update_post_meta($this->variation_id, 'stock', $reduce_to);
				return $reduce_to;
			endif;
		else :
			return parent::reduce_stock( $by );
		endif;
	}
	
	/**
	 * Increase stock level of the product
	 *
	 * @param   int		$by		Amount to increase by
	 */
	function increase_stock( $by = 1 ) {
		if ($this->variation_has_stock) :
			if ($this->managing_stock()) :
				$increase_to = $this->stock + $by;
				update_post_meta($this->variation_id, 'stock', $increase_to);
				return $increase_to;
			endif;
		else :
			return parent::increase_stock( $by );
		endif;
	}

}
