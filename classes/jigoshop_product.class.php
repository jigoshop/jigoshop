<?php
/**
 * Product Class
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
class jigoshop_product {
	
	private static $attribute_taxonomies = NULL;
	// reseting these all to public for now, fatal errors from certain places that direct access
	// the whole class needs refactoring  -JAP-
	public $id;
	public $exists;
	public $data;
	public $sku;
	public $attributes;
	public $post;
	public $stock;
	public $children;
	public $visibility;
	public $product_type;
	public $price;
    public $sale_price;
    
	/**
	 * Loads all product data from custom fields
	 *
	 * @param   int		$id		ID of the product to load
	 */
	function jigoshop_product( $id ) {

		$this->id = $id;

		$product_custom_fields = get_post_custom( $this->id );

        $this->sku = $this->id;
		if (isset($product_custom_fields['SKU'][0]) && !empty($product_custom_fields['SKU'][0])) {
            $this->sku = $product_custom_fields['SKU'][0];
        }

        $this->data = '';
		if (isset($product_custom_fields['product_data'][0])) {
            $this->data = maybe_unserialize( $product_custom_fields['product_data'][0] );
        }

        $this->attributes = array();
		if (isset($product_custom_fields['product_attributes'][0])) {
            $this->attributes = maybe_unserialize( $product_custom_fields['product_attributes'][0] );
        }
        
		// Again just in case, to fix WP bug
		$this->data = maybe_unserialize( $this->data );
		$this->attributes = maybe_unserialize( $this->attributes );

        $this->price = NULL;
		if (isset($this->data['regular_price'])) {
            $this->price = $this->data['regular_price'];
        }

        $this->sale_price = NULL;
		if (isset($this->data['sale_price'])) {
            $this->sale_price = $this->data['sale_price'];
        }

        $this->visibility = 'hidden';
		if (isset($product_custom_fields['visibility'][0])) {
            $this->visibility = $product_custom_fields['visibility'][0];
        }

        $this->stock = 0;
		if (isset($product_custom_fields['stock'][0])) {
            $this->stock = $product_custom_fields['stock'][0];
        }

		$terms = wp_get_object_terms( $id, 'product_type' );
		if (!is_wp_error($terms) && $terms) {
			$term = current($terms);
			$this->product_type = $term->slug;
        } else {
			$this->product_type = 'simple';
        }

		$this->get_children();

		if ($this->data) {
			$this->exists = true;
        } else {
			$this->exists = false;
        }
	}
    
    /**
     * Get SKU (Stock-keeping unit) - product uniqe ID
     * 
     * @return mixed
     */
    function get_sku() {
        return $this->sku;
    }
	
    /**
     * Returns the product's children
     * 
     * @return array stdClass objects array
     */
    function get_children() {

        //load the children if not yet loaded
        if (!is_array($this->children)) {

            $this->children = array();

            if ($this->is_type('variable')) {
                $child_post_type = 'product_variation';
            } else {
                $child_post_type = 'product';
            }

            $children_products = &get_children('post_parent=' . $this->id . '&post_type=' . $child_post_type . '&orderby=menu_order&order=ASC');

            if (is_array($children_products)) {
                
                //@fixme we just retrieved all the data about product children from DB, and we construct jigoshop_product* objects passing only ID to the constructor. In the constructor each product data will be retrieved *again* in the *seperate* queries. Performance fix needed (probably passing whole $child istead of $child->ID will do).
                foreach ($children_products as $child) {
                    if ($this->is_type('variable')) {
                        $child->product = &new jigoshop_product_variation($child->ID);
                    } else {
                        $child->product = &new jigoshop_product($child->ID);
                    }
                }

                $this->children = (array) $children_products;
            }
        }
        
        return $this->children;
    }

	/**
	 * Reduce stock level of the product
	 *
	 * @param   int		$by		Amount to reduce by
	 */
	function reduce_stock( $by = 1 ) {
		if ($this->managing_stock()) {
			$reduce_to = $this->stock - $by;
			update_post_meta($this->id, 'stock', $reduce_to);
			return $reduce_to;
        }
	}

	/**
	 * Increase stock level of the product
	 *
	 * @param   int		$by		Amount to increase by
	 */
	function increase_stock( $by = 1 ) {
		if ($this->managing_stock()) {
			$increase_to = $this->stock + $by;
			update_post_meta($this->id, 'stock', $increase_to);
			return $increase_to;
        }
	}

	/**
	 * Checks the product type
	 *
	 * @param   string		$type		Type to check against
	 */
	function is_type( $type ) {
		if (is_array($type) && in_array($this->product_type, $type)) {
            return true;
        } else if ($this->product_type == $type) {
            return true;
        }
        
		return false;
	}

	/** Returns whether or not the product has any child product */
	function has_child () {
        if(is_array($this->children) && count($this->children) > 0) {
            return true;
        }
        
		return false;
	}

	/** Returns whether or not the product post exists */
	function exists() {
		return ($this->exists);
	}

	/** Returns whether or not the product is taxable */
	function is_taxable() {
		if (isset($this->data['tax_status']) && $this->data['tax_status']=='taxable') {
            return true;
        }
        
		return false;
	}

	/** Returns whether or not the product shipping is taxable */
	function is_shipping_taxable() {
		if (isset($this->data['tax_status']) && ($this->data['tax_status']=='taxable' || $this->data['tax_status']=='shipping')) {
            return true;
        }
        
		return false;
	}

	/** Get the product's post data */
	function get_post_data() {
		if (empty($this->post)) {
			$this->post = get_post( $this->id );
        }

		return $this->post;
	}

	/** Get the title of the post */
	function get_title() {
		$this->get_post_data();
		return apply_filters('jigoshop_product_title', get_the_title($this->post->ID), $this);
	}

	/** Get the add to url */
	function add_to_cart_url() {

		if ($this->is_type('variable')) {
			$url = add_query_arg('add-to-cart', 'variation');
			$url = add_query_arg('product', $this->id, $url);
        } else if ( $this->has_child() ) {
			$url = add_query_arg('add-to-cart', 'group');
			$url = add_query_arg('product', $this->id, $url);
        } else {
			$url = add_query_arg('add-to-cart', $this->id);
        }

		$url = jigoshop::nonce_url( 'add_to_cart', $url );
		return $url;
	}

	/** Returns whether or not the product is stock managed */
	function managing_stock() {
		if (get_option('jigoshop_manage_stock') == 'yes') {
            if (isset($this->data['manage_stock']) && $this->data['manage_stock'] == 'yes') {
                return true;
            }
        }

        return false;
	}

	/** Returns whether or not the product is in stock */
	function is_in_stock() {
		if ($this->managing_stock()) {
            if ($this->data['stock_status'] != 'instock') {
                return false;
            } else if(!$this->backorders_allowed() && $this->stock <= 0) {
                return false;
            }
        }

        return true;
	}

	/** Returns whether or not the product can be backordered */
	function backorders_allowed() {
		if ($this->data['backorders']=='yes' || $this->data['backorders']=='notify') {
            return true;
        }
        
		return false;
	}

	/** Returns whether or not the product needs to notify the customer on backorder */
	function backorders_require_notification() {
		if ($this->data['backorders']=='notify') {
            return true;
        }
        
		return false;
	}

	/** Returns whether or not the product has enough stock for the order */
	function has_enough_stock( $quantity ) {
		if ($this->backorders_allowed() || $this->stock >= $quantity) {
			return true;
        }

		return false;
	}
    
    /**
     * Returns number of items available for sale.
     * 
     * @return int
     */
    function get_stock_quantity() {
        return (int)$this->stock;
    }
	
	/** Returns the availability of the product */
	function get_availability() {
		$availability = "";
		$class = "";

		if (!$this->managing_stock()) :
			if ($this->is_in_stock()) :
				//$availability = __('In stock', 'jigoshop'); /* Lets not bother showing stock if its not managed and is available */
			else :
				$availability = __('Out of stock', 'jigoshop');
				$class = 'out-of-stock';
			endif;
		else :
			if ($this->is_in_stock()) :
				if ($this->stock > 0) :
					$availability = __('In stock', 'jigoshop');

					if ($this->backorders_allowed()) :
						if ($this->backorders_require_notification()) :
							$availability .= ' &ndash; '.$this->stock.' ';
							$availability .= __('available', 'jigoshop');
							$availability .= __(' (backorders allowed)', 'jigoshop');
						endif;
					else :
						$availability .= ' &ndash; '.$this->stock.' ';
						$availability .= __('available', 'jigoshop');
					endif;

				else :

					if ($this->backorders_allowed()) :
						if ($this->backorders_require_notification()) :
							$availability = __('Available on backorder', 'jigoshop');
						else :
							$availability = __('In stock', 'jigoshop');
						endif;
					else :
						$availability = __('Out of stock', 'jigoshop');
						$class = 'out-of-stock';
					endif;

				endif;
			else :
				if ($this->backorders_allowed()) :
					$availability = __('Available on backorder', 'jigoshop');
				else :
					$availability = __('Out of stock', 'jigoshop');
					$class = 'out-of-stock';
				endif;
			endif;
		endif;

		return array( 'availability' => $availability, 'class' => $class);
	}

	/** Returns whether or not the product is featured */
	function is_featured() {
		if (get_post_meta($this->id, 'featured', true)=='yes') {
            return true;
        }
        
		return false;
	}

	/** Returns whether or not the product is visible */
	function is_visible() {
		if ($this->visibility=='hidden') return false;
		if ($this->visibility=='visible') return true;
		if ($this->visibility=='search' && is_search()) return true;
		if ($this->visibility=='search' && !is_search()) return false;
		if ($this->visibility=='catalog' && is_search()) return false;
		if ($this->visibility=='catalog' && !is_search()) return true;
	}

	/** Returns whether or not a sale price is valid based on product sale date settings */
	function in_sale_date_range() {
		$in_range = false;
		$custom_fields = get_post_custom( $this->id );
		$date_from = (int)$custom_fields['sale_price_dates_from'][0];
		$date_to = (int)$custom_fields['sale_price_dates_to'][0];
		$current_time = strtotime( 'NOW' );
		if ( $date_to == 0 && $date_from == 0 ) $in_range = true;
		else if ( $date_from == 0 || ( $date_from > 0 && $date_from < $current_time )) :
			if ( $date_to == 0 || $date_to > $current_time ) $in_range = true;
		endif;
		return $in_range;
	}
	
    /**
     * Returns whether or not the product is on sale.
     * If one of the child products is on sale, product is considered to be on sale.
     *
     * @return bool
     */
	function is_on_sale() {
		
		$on_sale = false;
		
		if ($this->has_child()) {
			foreach ($this->children as $child) {
				if( $this->product_type != 'grouped') {
					$on_sale = $child->product->variation_is_on_sale();
					if ( $on_sale ) break;
				} else {
					$on_sale = $child->product->is_on_sale();
					if ( $on_sale ) break;
				}
			}
		}
		// the kids may or may not have a sale price
		// we need to check the parent anyway and logical OR the results in
		if ( ! empty( $this->sale_price )) {
			$on_sale |= $this->in_sale_date_range();
		}
		
		return $on_sale;
	}

	/** Returns the product's weight */
	function get_weight() {
		if ($this->data['weight']) {
            return $this->data['weight'];
        }
        
        return NULL;
	}

	/** Returns the product's price */
	function get_price() {
        if(!empty($this->sale_price) && $this->in_sale_date_range()) {
            return $this->sale_price;
        }
        
		return $this->price;
	}

	/** Returns the price (excluding tax) */
	function get_price_excluding_tax() {
		$price = $this->get_price();

        if (get_option('jigoshop_prices_include_tax') == 'yes') {
            $rate = $this->get_tax_base_rate();

            if ($rate && $rate > 0) {
                $_tax = &new jigoshop_tax();
                $tax_amount = $_tax->calc_tax($price, $rate, true);
                $price = $price - $tax_amount;
            }
        }

        return $price;
	}

	/** Returns the base tax rate */
	function get_tax_base_rate() {
		if ($this->is_taxable() && get_option('jigoshop_calc_taxes') == 'yes') {
            $_tax = &new jigoshop_tax();
            $rate = $_tax->get_shop_base_rate($this->data['tax_class']);

            return $rate;
        }

        return NULL;
	}

	/** Returns the price in html format */
	function get_price_html()
    {
        $price_html = '';
        
        if ($this->has_child()) {
            $child_prices = array();
            $previous_price = -1.0;
            $has_price_variation = false;

            foreach ($this->children as $child) {
      
                // Nasty hack to prevent disabled variations from affecting the price
      		if($this->product_type == 'grouped' || 
      			($this->product_type != 'grouped' && $child->product->variation->post_status == 'publish') ) {
      		    $child_prices[] = (float)$child->product->get_price();
      		    
      		    // check for a price variation on the product variations
      		    if ($previous_price > 0.0 && $previous_price != (float)$child->product->get_price()) {
      		    	$has_price_variation = true;
      		    }
      		    $previous_price = (float)$child->product->get_price();
      		    
      		}
            }
            
            // only add from to tag when there is a price variation on variable products
            if ($has_price_variation) {
	        sort($child_prices);
	        $lowest_price = $child_prices[0];
		$price_html .= '<span class="from">' . __('From: ', 'jigoshop') . '</span>' . jigoshop_price($lowest_price);
	    }
	    // otherwise return price from product
	    else {
	    	$price_html .= jigoshop_price($this->get_price());
	    }
        } else {
            if ($this->price === '') {
                $price_html = __('Price Not Announced');
            } else if ($this->price === '0') {
                $price_html = __('Free');
            } else {
                if (!empty($this->sale_price) && !empty($this->price) && $this->in_sale_date_range()) {
                    $price_html .= '<del>' . jigoshop_price($this->price) . '</del> <ins>' . jigoshop_price($this->sale_price) . '</ins>';
                } else {
                    $price_html .= jigoshop_price($this->get_price());
                }
            }
        }

        return $price_html;
    }

	/** Returns the upsell product ids */
	function get_upsells() {
		if (isset($this->data['upsell_ids'])) {
            return (array) $this->data['upsell_ids']; 
        }
        
        return array();
	}

	/** Returns the crosssell product ids */
	function get_cross_sells() {
		if (isset($this->data['crosssell_ids'])) {
            return (array) $this->data['crosssell_ids'];
        }
        
        return array();
	}

	/** Returns the product categories */
	function get_categories( $sep = ', ', $before = '', $after = '' ) {
		return get_the_term_list($this->id, 'product_cat', $before, $sep, $after);
	}

	/** Returns the product tags */
	function get_tags( $sep = ', ', $before = '', $after = '' ) {
		return get_the_term_list($this->id, 'product_tag', $before, $sep, $after);
	}

	/** Get and return related products */
	function get_related( $limit = 5 ) {
		global $wpdb, $all_post_ids; /* $all_post_ids doesn't appear to be used in this file, checking -JAP- */
		// Related products are found from category and tag
		$tags_array = array(0);
		$cats_array = array(0);
		$tags = '';
		$cats = '';

		// Get tags
		$terms = wp_get_post_terms($this->id, 'product_tag');
		foreach ($terms as $term) {
			$tags_array[] = $term->term_id;
		}
		$tags = implode(',', $tags_array);

		$terms = wp_get_post_terms($this->id, 'product_cat');
		foreach ($terms as $term) {
			$cats_array[] = $term->term_id;
		}
		$cats = implode(',', $cats_array);

		$q = "
			SELECT p.ID
			FROM $wpdb->term_taxonomy AS tt, $wpdb->term_relationships AS tr, $wpdb->posts AS p, $wpdb->postmeta AS pm
			WHERE
				p.ID != $this->id
				AND p.post_status = 'publish'
				AND p.post_date_gmt < NOW()
				AND p.post_type = 'product'
				AND pm.meta_key = 'visibility'
				AND pm.meta_value IN ('visible', 'catalog')
				AND pm.post_id = p.ID
				AND
				(
					(
						tt.taxonomy ='product_cat'
						AND tt.term_taxonomy_id = tr.term_taxonomy_id
						AND tr.object_id  = p.ID
						AND tt.term_id IN ($cats)
					)
					OR
					(
						tt.taxonomy ='product_tag'
						AND tt.term_taxonomy_id = tr.term_taxonomy_id
						AND tr.object_id  = p.ID
						AND tt.term_id IN ($tags)
					)
				)
			GROUP BY tr.object_id
			ORDER BY RAND()
			LIMIT $limit;";

		$related = $wpdb->get_col($q);

		return $related;
	}

	/** Returns product attributes */
	function get_attributes() {
		return $this->attributes;
	}

	/** Returns whether or not the product has any attributes set */
	function has_attributes() {
		if (isset($this->attributes) && count($this->attributes) > 0) {
            foreach ($this->attributes as $attribute) {
                if ($attribute['visible'] == 'yes') {
                    return true;
                }
            }
        }

        return false;
	}

	/** Lists a table of attributes for the product page */
	function list_attributes() {
		$attributes = $this->get_attributes();
        
		if ($attributes && count($attributes)>0) {

			echo '<table cellspacing="0" class="shop_attributes">';
			$alt = 1;
			foreach ($attributes as $attribute) {
				if ($attribute['visible'] == 'no') {
                    continue;
                }
                
				$alt = $alt*-1;
				echo '<tr class="';
				if ($alt==1) echo 'alt';
				echo '"><th>'.wptexturize($attribute['name']).'</th><td>';

                $value = $attribute['value'];
                
                // if taxonomy we should replace all term slugs with original names
                if ( $attribute['is_taxonomy'] == 'yes' ) {
                    if ( ! is_array( $value )) {
                        $value = array( $value );
                    }
                    
                    $taxonomy_name = 'pa_' . sanitize_title( $attribute['name'] );
                    
                    $new_value = array();
                    foreach ( $value as $term_slug ) {
                        $term = get_term_by( 'slug', $term_slug, $taxonomy_name );
                        $new_value[] = $term->name;
                    }
                    $value = $new_value;
                }
                
                if ( is_array($value ) ) {
                    $value = implode( ', ', $value );
                }

				echo wpautop( wptexturize( $value ));

				echo '</td></tr>';
            }
			echo '</table>';

        }
	}
    
    /**
     * Returns an array of available values for attributes used in product variations
     * 
     * @todo Note that this is 'variable product' specific, and should be moved to separate class
     * with all 'variable product' logic form other methods in this class.
     * 
     * @return two dimensional array of attributes and their available values
     */   
    function get_available_attributes_variations() {
        if (!$this->is_type('variable') || !$this->has_child()) {
            return array();
        }
        
        $attributes = $this->get_attributes();
        
        if(!is_array($attributes)) {
            return array();
        }
        
        $available = array();
        $children = $this->get_children();
        
        foreach ($attributes as $attribute) {
            if ($attribute['variation'] !== 'yes') {
                continue;
            }

            $values = array();
            $name = 'tax_'.sanitize_title($attribute['name']);

            foreach ($children as $child) {
                /* @var $variation jigoshop_product_variation */
                $variation = $child->product;

                //check attributes of all variations that are visible (enabled)
                if ($variation instanceof jigoshop_product_variation && $variation->is_visible()) {
                    $options = $variation->get_variation_attributes();
					
                    if (is_array($options)) {
                        foreach ($options as $aname => $avalue) {
                            if ($aname == $name) {
                                $values[] = $avalue;
                            }
                        }
                    }
                }
            }
            
			sort( $values );
			
            //empty value indicates that all options for given attribute are available
            if ( in_array(  '', $values)) {
                $options = $attribute['value'];
                if (!is_array($options)) {
                    $options = explode(',', $options);
                }
				
                $values = $options;
            }
              
            //make sure values are unique
            $values = array_unique($values);

            $available[$attribute['name']] = $values;
        }
        
        return $available;
    }

    /**
     * Get attribute taxonomies. Taxonomies are lazy loaded.
     * 
     * @return array of stdClass objects representing attributes
     */
    public static function getAttributeTaxonomies() {
        global $wpdb;
                
        if(self::$attribute_taxonomies === NULL) {
            self::$attribute_taxonomies = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."jigoshop_attribute_taxonomies;"); 
        }
        
        return self::$attribute_taxonomies;
    }
	
}
