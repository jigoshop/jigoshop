<?php
/**
 * Cart Class
 *
 * The JigoShop cart class stores cart data and active coupons as well as handling customer sessions and some cart related urls.
 * The cart class also has a price calculation function which calls upon other classes to calcualte totals.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class jigoshop_cart extends Jigoshop_Singleton {

    public static $cart_contents_total;
    public static $cart_contents_total_ex_tax;
    public static $cart_contents_weight;
    public static $cart_contents_count;
    public static $cart_dl_count;
    public static $cart_contents_total_ex_dl;
    public static $total;
    public static $subtotal;
    public static $subtotal_ex_tax;
    public static $discount_total;
    public static $shipping_total;
    public static $shipping_tax_total;
    public static $applied_coupons;
    public static $cart_contents;

    private static $cart_discount_leftover;     // TODO: version 1.5 needs investigation, not used
    private static $price_per_tax_class_ex_tax;
    private static $tax;
	
	
    /** constructor */
    protected function __construct() {

        self::get_cart_from_session();

        self::$applied_coupons = array();

        if ( isset( jigoshop_session::instance()->coupons ))
            self::$applied_coupons = jigoshop_session::instance()->coupons;

        self::$tax = new jigoshop_tax(100); //initialize tax on the cart with divisor of 100

        // needed to calculate cart total for cart widget. Separated from calculate_totals
        // so that shipping doesn't need to be calculated so many times. Calling the server
        // api's often per page request isn't a good idea.
        self::calculate_cart_total();
    }
	
	
    /** Gets the cart data from the PHP session */
    function get_cart_from_session() {

        self::$cart_contents = (array) jigoshop_session::instance()->cart;
        
    }
	
	
    /** sets the php session data for the cart and coupon */
    function set_session() {
        // we get here from cart additions, quantity adjustments, and coupon additions
        // reset any chosen shipping methods as these adjustments can effect shipping (free shipping)
        unset( jigoshop_session::instance()->chosen_shipping_method_id );
        unset( jigoshop_session::instance()->selected_rate_id ); // calculable shipping

        jigoshop_session::instance()->cart = apply_filters( 'jigoshop_cart_set_session', self::$cart_contents );

        jigoshop_session::instance()->coupons = self::$applied_coupons;

        self::calculate_totals();
    }
	
	
    /** Empty the cart */
    function empty_cart() {
        self::$cart_contents = array();
        self::$applied_coupons = array();
        self::reset_totals();
        unset(jigoshop_session::instance()->cart);
        unset(jigoshop_session::instance()->coupons);
        unset(jigoshop_session::instance()->chosen_shipping_method_id);
        unset(jigoshop_session::instance()->selected_rate_id);
    }
	
	
    /**
     * Generate a unique ID for the cart item being added
     *
     * @param int $product_id - id of the product the key is being generated for
     * @param int $variation_id of the product the key is being generated for
     * @param array $variation data for the cart item
     * @param array $cart_item_data other cart item data passed which affects this items uniqueness in the cart
     * @return string cart item key
     */
    function generate_cart_id( $product_id, $variation_id = '', $variation = '', $cart_item_data = '' ) {

        $id_parts = array( $product_id );

        if ( $variation_id ) $id_parts[] = $variation_id;

        if ( is_array( $variation ) ) {
            $variation_key = '';
            foreach ( $variation as $key => $value ) {
                $variation_key .= trim( $key ) . trim( $value );
            }
            $id_parts[] = $variation_key;
        }

        if ( is_array( $cart_item_data ) ) {
            $cart_item_data_key = '';
            foreach ( $cart_item_data as $key => $value ) {
                if ( is_array( $value ) ) $value = http_build_query( $value );
                $cart_item_data_key .= trim($key) . trim($value);
            }
            $id_parts[] = $cart_item_data_key;
        }

        return md5( implode( '_', $id_parts ) );
    }
	
	
    /**
     * Check if product is in the cart and return cart item key
     *
     * Cart item key will be unique based on the item and its properties, such as variations
     *
     * @param mixed id of product to find in the cart
     * @return string cart item key
     */
    function find_product_in_cart( $cart_id = false ) {
        if ( $cart_id !== false )
            foreach ( self::$cart_contents as $cart_item_key => $cart_item )
                if ( $cart_item_key == $cart_id )
                    return $cart_item_key;
    }
	
	
    /**
     * Add a product to the cart
     *
     * @param   string  product_id  contains the id of the product to add to the cart
     * @param   string  quantity    contains the quantity of the item to add
     * @param   int     variation_id
     * @param   array   variation attribute values
     */
    function add_to_cart($product_id, $quantity = 1, $variation_id = '', $variation = array()) {

        if ($quantity < 0) {
            $quantity = 0;
        }

        // Load cart item data - may be added by other plugins
        $cart_item_data = (array) apply_filters('jigoshop_add_cart_item_data', array(), $product_id);

        $cart_id = self::generate_cart_id($product_id, $variation_id, $variation, $cart_item_data);
        $cart_item_key = self::find_product_in_cart( $cart_id );

        if (empty($variation_id)) {
            $product = new jigoshop_product($product_id);
        } else {
            $product = new jigoshop_product_variation($variation_id);
        }

        //product with a given ID doesn't exists
        if (empty($product)) {
            return false;
        }

        // prevents adding products with no price to the cart
        if ($product->get_price() === '') {
            jigoshop::add_error(__('You cannot add this product to your cart because its price is not yet announced', 'jigoshop'));
            return false;
        }

		// products newly added to the Cart will not have a $cart_item_key, use 0 quantity
        $in_cart_qty = !empty($cart_item_key) ? self::$cart_contents[$cart_item_key]['quantity'] : 0;
        
		// prevents adding products to the cart without enough quantity on hand
        if ($product->managing_stock() && !$product->has_enough_stock($quantity + $in_cart_qty)) :
            if ($in_cart_qty > 0) :
                $error = (self::get_options()->get_option('jigoshop_show_stock') == 'yes') ? sprintf(__('We are sorry.  We do not have enough "%s" to fill your request.  You have %d of them in your Cart and we have %d available at this time.', 'jigoshop'), $product->get_title(), $in_cart_qty, $product->get_stock()) : sprintf(__('We are sorry.  We do not have enough "%s" to fill your request.', 'jigoshop'), $product->get_title());
            else :
                $error = (self::get_options()->get_option('jigoshop_show_stock') == 'yes') ? sprintf(__('We are sorry.  We do not have enough "%s" to fill your request. There are only %d left in stock.', 'jigoshop'), $product->get_title(), $product->get_stock()) : sprintf(__('We are sorry.  We do not have enough "%s" to fill your request.', 'jigoshop'), $product->get_title());
            endif;
            jigoshop::add_error($error);
            return false;
        endif;

        //if product is already in the cart change its quantity
        if ($cart_item_key) {

            $quantity = (int) $quantity + self::$cart_contents[$cart_item_key]['quantity'];

            self::set_quantity( $cart_item_key, apply_filters( 'jigoshop_cart_item_quantity', $quantity, $product, $cart_item_key ) );

        } else {
        	// otherwise add new item to the cart
            self::$cart_contents[$cart_id] = apply_filters( 'jigoshop_add_cart_item', array(
				'data'        => $product,
				'product_id'  => $product_id,
				'variation'   => $variation,
				'variation_id'=> $variation_id,
				'quantity'    => (int) $quantity,
				'unit_price'  => 0,
				'tax'         => 0,
				'discount'    => 0,
				'price_includes_tax' => self::get_options()->get_option('jigoshop_prices_include_tax')
			), $cart_item_data);
        }

        self::set_session();

        return true;
    }
	
	
    /**
     * Set the quantity for an item in the cart
     * Remove the item from the cart if no quantity
     * Also remove any product discounts if applied
     *
     * @param   string  cart_item_key   contains the id of the cart item
     * @param   string  quantity    contains the quantity of the item
     */
    function set_quantity($cart_item, $quantity = 1) {
        if ($quantity == 0 || $quantity < 0) {
            $_product = self::$cart_contents[$cart_item];
            if ( ! empty( self::$applied_coupons )) foreach (self::$applied_coupons as $key => $code) {
				$coupon = JS_Coupons::get_coupon($code);
				if (JS_Coupons::is_valid_coupon_for_product($code, $_product)) {
					if ($coupon['type'] == 'fixed_product') {
						self::$discount_total = self::$discount_total - ( apply_filters( 'jigoshop_coupon_product_fixed_amount', $coupon['amount'], $coupon ) * $_product['quantity'] );
						unset(self::$applied_coupons[$key]);
					} else if ($coupon['type'] == 'percent_product') {
						self::$discount_total = self::$discount_total - (( $_product['data']->get_price() * $_product['quantity'] / 100 ) * $coupon['amount']);
						unset(self::$applied_coupons[$key]);
					}
				}
			}
            unset(self::$cart_contents[$cart_item]);
        } else {
            if ( ! empty( self::$applied_coupons )) foreach (self::$applied_coupons as $key => $code) {
				$coupon = JS_Coupons::get_coupon($code);
				if ( ! self::valid_coupon( $code ) ) {
					unset( self::$applied_coupons[$key] );
				}
			}
            self::$cart_contents[$cart_item]['quantity'] = $quantity;
        }

        self::set_session();
    }
	
	
    /**
     * Returns the contents of the cart
     *
     * @return   array  cart_contents
     */
    static function get_cart() {
        if ( empty( self::$cart_contents ) ) self::get_cart_from_session();
        return self::$cart_contents;
    }
	
	
    /** gets the url to the cart page */
    function get_cart_url() {
        $cart_page_id = jigoshop_get_page_id('cart');
        if ($cart_page_id)
            return apply_filters('jigoshop_get_cart_url', get_permalink($cart_page_id));
    }
	
	
    /** gets the url to the checkout page */
    function get_checkout_url() {
        $checkout_page_id = jigoshop_get_page_id('checkout');
        if ($checkout_page_id) :
            if (is_ssl())
                return str_replace('http:', 'https:', get_permalink($checkout_page_id));
            return apply_filters('jigoshop_get_checkout_url', get_permalink($checkout_page_id));
        endif;
    }
	
	
    /** gets the url to the shop page */
    function get_shop_url() {
        $shop_page_id = jigoshop_get_page_id('shop_redirect');
        if ($shop_page_id) :
            return apply_filters('jigoshop_get_shop_page_id', get_permalink($shop_page_id));
        endif;
    }
	
	
    /** gets the url to remove an item from the cart */
    function get_remove_url($cart_item_key) {
        $cart_page_id = jigoshop_get_page_id('cart');
        if ($cart_page_id) {
            return apply_filters('jigoshop_get_remove_url', jigoshop::nonce_url( 'cart', add_query_arg('remove_item', $cart_item_key, get_permalink($cart_page_id))));
        }
    }
	
	
    /** looks through the cart to see if shipping is actually required */
    public static function needs_shipping() {

        if (!jigoshop_shipping::is_enabled())
            return false;
        if (!is_array(self::$cart_contents))
            return false;

        $needs_shipping = false;

        foreach (self::$cart_contents as $cart_item_key => $values) :
            $_product = $values['data'];
            if ($_product->requires_shipping()) :
                $needs_shipping = true;
                break; // once we know it's required, stop looping. We could have a mixture of non-shipping and shipping items
            endif;
        endforeach;

        return $needs_shipping;
    }
	
	
    /** Sees if we need a shipping address */
    function ship_to_billing_address_only() {
        return (self::get_options()->get_option('jigoshop_ship_to_billing_address_only') == 'yes');
    }
	
	
    /** looks at the totals to see if payment is actually required */
    function needs_payment() {
        if (self::$total > 0)
            return true;
        return false;
    }
	
	
    /* Looks through the cart to confirm that each item is in stock. */
    function check_cart_item_stock() {

        foreach (self::$cart_contents as $cart_item_key => $values) :

            $_product = $values['data'];

            if ( !$_product->is_in_stock() || ( $_product->managing_stock() && !$_product->has_enough_stock($values['quantity']) ) ) {
                $error = new WP_Error();
                $errormsg = (self::get_options()->get_option('jigoshop_show_stock') == 'yes')

                            ? sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. We only have %d available at this time. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'),
                            $_product->get_title(), $_product->get_stock())

                            : sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'),
                            $_product->get_title());

                $error->add( 'out-of-stock', $errormsg );
                return $error;
            }

        endforeach;

        return true;
    }
	
	
    /** reset all Cart totals */
    private static function reset_totals() {
        self::$total                      = 0;
        self::$cart_contents_total        = 0;
        self::$cart_contents_total_ex_tax = 0;
        self::$cart_contents_weight       = 0;
        self::$cart_contents_count        = 0;
        self::$shipping_tax_total         = 0;
        self::$subtotal                   = 0;
        self::$subtotal_ex_tax            = 0;
        self::$discount_total             = 0;
        self::$shipping_total             = 0;
        self::$cart_dl_count              = 0;
        self::$cart_discount_leftover     = 0; /* cart discounts greater than total product price */
        self::$cart_contents_total_ex_dl  = 0; /* for table rate shipping */
        self::$tax->init_tax();
        self::$price_per_tax_class_ex_tax = array(); /* currently used with norway */
        jigoshop_shipping::reset_shipping();
    }
	
	
	/**
	 *  Calculate total 'cart fixed' and 'cart percentage' discounts
	 *
	 *  @param  $total_to_use   the cart total price to base discounts on, tax in or out usually
	 *
	 *  @return $cart_discount  a total monetary amount from the applied cart discount coupons
	 */
	private static function calculate_cart_discounts_total( $total_to_use ) {
		
		$cart_discount = 0;
		if ( ! empty( self::$applied_coupons )) foreach ( self::$applied_coupons as $code ) {
			if ( $coupon = JS_Coupons::get_coupon( $code )) {

				switch ( $coupon['type'] ) :

					case 'fixed_cart' :
						$cart_discount += apply_filters( 'jigoshop_coupon_cart_fixed_amount', $coupon['amount'], $coupon);
						break;

					case 'percent' :
						$cart_discount += ( $total_to_use / 100 ) * $coupon['amount'];
						break;


				endswitch;

			}
		}
		return $cart_discount;
	}
	
	/**
	 *  Calculate total 'product fixed' and 'product percentage' discounts
	 *
	 *  @param  $_product   the product we are working with
	 *  @param  $values     the cart values for this product
	 *
	 *  @return $current_product_discount
	 */
	private static function calculate_product_discounts_total( $_product, $values ) {
	
		$current_product_discount = 0;
		
		if ( ! empty( self::$applied_coupons )) foreach ( self::$applied_coupons as $code ) {
			
			$coupon_discount = 0;
			$coupon = JS_Coupons::get_coupon( $code );

			if ( ! JS_Coupons::is_valid_coupon_for_product( $code, $values ) ) continue;

			$price = self::get_options()->get_option('jigoshop_tax_after_coupon') == 'yes'
				? $_product->get_price_excluding_tax()
				: $_product->get_price_with_tax();

			switch ( $coupon['type'] ) {
			
				case 'fixed_product' :
					$coupon_discount = apply_filters( 'jigoshop_coupon_product_fixed_amount', $coupon['amount'], $coupon ) * $values['quantity'];
					if ( $coupon_discount > $price * $values['quantity'] )
						$coupon_discount = $price * $values['quantity'];
					break;

				case 'percent_product' :
					$coupon_discount = ( $price * $values['quantity'] / 100 ) * $coupon['amount'];
					break;
			}

			$current_product_discount += $coupon_discount;

		}
		
		return $current_product_discount;
	
	}
	
	/**
	 *  
	 */
    private static function calculate_cart_total() {

        self::reset_totals();

        /* No items so nothing to calculate. Make sure applied coupons and session data are reset. */
        if ( empty( self::$cart_contents ) ) {
            self::empty_cart();
            return;
        }

		/*
		 *  Loop through each product in the cart
		 */
        if ( ! empty( self::$cart_contents )) foreach ( self::$cart_contents as $cart_item_key => $values ) {
        
            $_product = $values['data'];

			self::$cart_contents_count += $values['quantity'];
			
			// get actual applied discounts from coupons for this product
			$current_product_discount = self::calculate_product_discounts_total( $_product, $values );
			self::$discount_total += $current_product_discount;
			
			// this should never be less that 0.00 due to 'calculate_product_discounts_total()'
			$discounted_item_price = $_product->get_price_excluding_tax() * $values['quantity'] - $current_product_discount;				
			$total_item_price = $_product->get_price() * $values['quantity'] * 100;

			if ( self::get_options()->get_option('jigoshop_calc_taxes') == 'yes' ) {

				$tax_classes_applied = array();
				
				if ( $_product->is_taxable() ) {
				
                    $shipable = jigoshop_shipping::is_enabled() && $_product->requires_shipping();
                    
					self::$tax->set_is_shipable( $shipable );

					$price_includes_tax =
						self::get_options()->get_option('jigoshop_tax_after_coupon') == 'yes'
						&& $current_product_discount > 0
						? false
						: self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes';
					
					$product_discounted_price = self::get_options()->get_option('jigoshop_tax_after_coupon') == 'yes'
							&& $current_product_discount > 0
								? $discounted_item_price * 100
								: $total_item_price;
					
					$tax_classes_applied = self::$tax->calculate_tax_amounts(
							$product_discounted_price,
							$_product->get_tax_classes(),
							$price_includes_tax
					);
					
					if ( self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes') {
						
						if (self::get_options()->get_option('jigoshop_tax_after_coupon') == 'yes' && $discounted_item_price >= 0) {
						
							$total_item_price = ($discounted_item_price + self::$tax->get_non_compounded_tax_amount() + self::$tax->get_compound_tax_amount()) * 100;

						} else if ( !jigoshop_customer::is_taxable($shipable) ) {
							// adjust total_item_price for customers that are not in the taxable shop country
							$total_item_price = $_product->get_price_excluding_tax() * $values['quantity'] * 100;
						}
						
					}
												
					// reason we cannot use get_applied_tax_classes is because we may not have applied all tax classes for this product.
					// get_applied_tax_classes will return all of the tax classes that have been applied on all products
					foreach ( $tax_classes_applied as $tax_class ) {

						$price_ex_tax = $_product->get_price_excluding_tax() * $values['quantity'];

						if ( isset(self::$price_per_tax_class_ex_tax[$tax_class]) )
							self::$price_per_tax_class_ex_tax[$tax_class] += $price_ex_tax;
						else
							self::$price_per_tax_class_ex_tax[$tax_class] = $price_ex_tax;

					}
				}
			}
			
			$total_item_price = $total_item_price / 100;

			/* Apply weight only to non-downloadable products. */
			if ( $_product->product_type != 'downloadable' ) {
				self::$cart_contents_weight = self::$cart_contents_weight + ( $_product->get_weight() * $values['quantity'] );
				self::$cart_contents_total_ex_dl = self::$cart_contents_total_ex_dl + $total_item_price;
			} else {
				self::$cart_dl_count = self::$cart_dl_count + $values['quantity'];
			}

			self::$cart_contents_total += $total_item_price;
			self::$cart_contents_total_ex_tax += $_product->get_price_excluding_tax() * $values['quantity'];
			

        }
        
    }

    /** calculate totals for all taxable products in the cart */
	private function get_cart_taxable_products_total_excluding_tax() {
		
		$total = 0;
		
        if ( ! empty( self::$cart_contents )) foreach ( self::$cart_contents as $cart_item_key => $values ) {
        
            $_product = $values['data'];
            
            // do we need to exclude taxable products that have a zero tax rate?
			if ( $_product->is_taxable() ) {
				$total += $_product->get_price_excluding_tax() * $values['quantity'];
			}
			
        }
        
        return $total;
        
	}
	
    /** calculate totals for all non-taxable products in the cart */
	private function get_cart_non_taxable_products_total() {
		
		$total = 0;
		
        if ( ! empty( self::$cart_contents )) foreach ( self::$cart_contents as $cart_item_key => $values ) {
        
            $_product = $values['data'];
            
            // we may need to include taxable products that have a zero tax rate?
			if ( ! $_product->is_taxable() ) {
				$total += $_product->get_price_excluding_tax() * $values['quantity'];
			}
			
        }
        
        return $total;
        
	}
	
    /** calculate totals for the items in the cart */
    function calculate_totals() {

        // extracted cart totals so that the constructor can call it, rather than
        // this full method. Cart totals are needed for cart widget and themes.
        // Don't want to call shipping api's multiple times per page load
        self::calculate_cart_total();

        // Cart Shipping
        if (self::needs_shipping()) :
            jigoshop_shipping::calculate_shipping(self::$tax);
        else :
            jigoshop_shipping::reset_shipping();
        endif;

        self::$shipping_total = jigoshop_shipping::get_total();

        if (self::get_options()->get_option('jigoshop_calc_taxes') == 'yes') :
            self::$shipping_tax_total = jigoshop_shipping::get_tax();
            self::$tax->update_tax_amount_with_shipping_tax(self::$shipping_tax_total * 100);

            $shipping_tax_classes = self::$tax->get_shipping_tax_classes();

            foreach ($shipping_tax_classes as $tax_class) :
                if (empty(self::$price_per_tax_class_ex_tax[$tax_class])) :
                    self::$price_per_tax_class_ex_tax[$tax_class] = self::$shipping_total;
                else :
                    self::$price_per_tax_class_ex_tax[$tax_class] += self::$shipping_total;
                endif;
            endforeach;

        endif;

        // Subtotal
        self::$subtotal_ex_tax = self::$cart_contents_total_ex_tax;
        self::$subtotal = self::$cart_contents_total;

        if (self::get_options()->get_option('jigoshop_calc_taxes') == 'yes' && !self::$tax->get_total_shipping_tax_amount()) :

            foreach (self::get_applied_tax_classes() as $tax_class) :
                if (!self::is_not_compounded_tax($tax_class)) : //tax compounded
                    $discount = (self::get_options()->get_option('jigoshop_tax_after_coupon') == 'yes' ? self::$discount_total : 0);
                    self::$tax->update_tax_amount($tax_class, (self::$subtotal_ex_tax - $discount + self::$tax->get_non_compounded_tax_amount() + self::$shipping_total) * 100);
                endif;
            endforeach;
        endif;
		
		self::$total = self::get_cart_subtotal(false) + self::get_cart_shipping_total(false);
		if ( self::get_options()->get_option('jigoshop_calc_taxes') == 'yes'
			&& self::get_options()->get_option('jigoshop_prices_include_tax') == 'no'
			&& self::get_options()->get_option('jigoshop_tax_after_coupon') == 'no' ) {
			
			self::$total += self::$tax->get_non_compounded_tax_amount() + self::$tax->get_compound_tax_amount();
		}
		
		// calculate any cart wide discounts from coupons
		$total_product_discounts = self::$discount_total;
		self::$discount_total = $total_cart_discounts = $temp = 0;
		
		if ( self::get_options()->get_option('jigoshop_tax_after_coupon') == 'yes' ) {
		
			// we need products and shipping with tax out
			$total_cart_discounts = round( self::calculate_cart_discounts_total(
				self::$cart_contents_total_ex_tax + self::get_cart_shipping_total(false,true)), 2 );
			if ( $total_cart_discounts > 0 ) {
				$total_to_use = self::$cart_contents_total_ex_tax + self::$shipping_total;
				if ( $total_cart_discounts > $total_to_use ) $total_cart_discounts = $total_to_use - $total_product_discounts;
				$total_discounts = $total_cart_discounts + $total_product_discounts;
				if ( $total_discounts > $total_to_use ) $total_discounts = $total_to_use;
				foreach ( self::get_applied_tax_classes() as $tax_class ) {
					// recalc taxes based on discounts already applied, no effect if tax calcs disabled
					$rate = self::$tax->get_rate($tax_class);
					$taxable_total = self::get_cart_taxable_products_total_excluding_tax();
					// if shipping is taxable, add it into the total for a revised tax calc
					if ( self::$shipping_tax_total > 0.00 ) {
						$taxable_total += self::$shipping_total;
						self::$shipping_tax_total = 0.00; // too complicated, reset this, total tax will be only tax
					}
					$non_taxable_total = self::get_cart_non_taxable_products_total();
					// we have calculated a discount to taxable and non taxable products with shipping already
					// we need a ratio for this to recalc taxes for taxable items only, non-taxable products out
					$discount_ratio = abs(($total_discounts - $total_to_use) / $total_to_use);
					$revised_discount = $taxable_total - ($taxable_total * $discount_ratio);
					$revised_total = $taxable_total - $revised_discount;
					$tax = self::$tax->calc_tax( $revised_total, $rate, false );
					if ( $tax == 0.00 ) self::$shipping_tax_total = 0;
					self::$tax->update_tax_amount( $tax_class, $tax * 100, false, true );
					self::$tax->update_tax_amount_with_shipping_tax(self::$shipping_tax_total * 100);
					$temp += self::$tax->get_tax_amount($tax_class);
				}
				// check again in case tax calcs are disabled
				$total_discounts = $total_cart_discounts + $total_product_discounts;
				if ( $total_discounts > $total_to_use ) $total_cart_discounts = $total_to_use - $total_product_discounts;
			} else {
				foreach ( self::get_applied_tax_classes() as $tax_class ) {
					// otherwise use existing tax
					$temp += self::$tax->get_tax_amount($tax_class);
				}
			}
			
			if ( self::get_options()->get_option('jigoshop_prices_include_tax') == 'no' ) {
				self::$total += $temp;
			} else {
				self::$total = self::$cart_contents_total_ex_tax + self::$shipping_total + $temp;
			}
			
		} else {    //  Taxes are applied before coupons, 'jigoshop_tax_after_coupon' == 'no'

			if ( self::get_options()->get_option('jigoshop_prices_include_tax') == 'no' ) {
				$total_cart_discounts = self::calculate_cart_discounts_total( self::$total );
				if ( $total_cart_discounts > self::$total ) $total_cart_discounts = self::$total - $total_product_discounts;
			} else {
				$total_cart_discounts = self::calculate_cart_discounts_total( self::$cart_contents_total_ex_tax + self::$shipping_total );
				if ( $total_cart_discounts > 0 ) {
					// with an initial discount, recalc taxes and get a proper discount
					foreach ( self::get_applied_tax_classes() as $tax_class ) {
						$rate = self::$tax->get_rate($tax_class);
						$total_to_use = self::$cart_contents_total_ex_tax + self::$shipping_total;
						$tax = self::$tax->calc_tax( $total_to_use, $rate, false );
						self::$tax->update_tax_amount( $tax_class, $tax * 100, false, true );
						$temp += self::$tax->get_tax_amount($tax_class);
					}
					$total_to_use = self::$cart_contents_total_ex_tax + self::$shipping_total + $temp;
					$total_cart_discounts = self::calculate_cart_discounts_total( $total_to_use );
					if ( $total_cart_discounts > $total_to_use ) $total_cart_discounts = $total_to_use - $total_product_discounts;
				}
			}
			
		}
		
		// set the final discount
		self::$discount_total = $total_cart_discounts + $total_product_discounts;
		
		// adjust the grand total after all discounts
		self::$total -= self::$discount_total;
        if ( self::$total < 0 ) self::$total = 0;
        		
		// with everything calculated, check that coupons depending on cart totals are still valid
		// if they are not, remove them and recursively re-calculate everything all over again.
		$recalc = false;
		if ( ! empty( self::$applied_coupons )) foreach (self::$applied_coupons as $key => $code) {
			$coupon = JS_Coupons::get_coupon( $code );
			if ( ! self::valid_coupon( $code ) ) {
				unset( self::$applied_coupons[$key] );
        		jigoshop_session::instance()->coupons = self::$applied_coupons;
				$recalc = true;
			}
		}
		if ( $recalc ) self::calculate_totals();

    }

    // will return an empty array if taxes are not calculated
    public static function get_price_per_tax_class_ex_tax() {
        return self::$price_per_tax_class_ex_tax;
    }

    /** gets cart contents total excluding tax. Shipping methods use this, and the contents total are calculated ahead of shipping */
    public static function get_cart_contents_total_excluding_tax() {
        return self::$cart_contents_total_ex_tax;
    }

    /** gets the total (after calculation) */
    public static function get_total($for_display = true) {
        return ($for_display ? jigoshop_price(self::$total) : number_format(self::$total, 2, '.', ''));
    }

    /** gets the cart contents total (after calculation) */
    function get_cart_total() {
        return jigoshop_price(self::$cart_contents_total);
    }

    public static function get_total_cart_tax_without_shipping_tax() {
        return self::$tax->get_non_compounded_tax_amount() + self::$tax->get_compound_tax_amount() - self::$shipping_tax_total;
    }

    /**
     * Returns a calculated subtotal.
     *
     * @param boolean $for_display                  Just the price itself or with currency symbol + price with optional
     *                                              "(ex. tax)" / "(inc. tax)".
     * @param boolean $apply_discount_and_shipping  Subtotal with discount and shipping prices applied.
     * @param boolean $order_exclude_tax            Subtotal without taxes no matter settings used by Orders
     */
    public static function get_cart_subtotal( $for_display = true, $apply_discount_and_shipping = false, $order_exclude_tax = false ) {
		
		do_action( 'jigoshop_calculate_totals' );

        /* Just some initialization. */
        $discount = self::$discount_total;
        $subtotal = self::$subtotal;
        $tax_label = 0; // use with jigoshop_price. 0 for no label, 1 for ex. tax, 2 for inc. tax

        /**
         * Tax calculation turned ON.
         */
        if ( self::get_options()->get_option('jigoshop_calc_taxes') == 'yes' ) :

			// for final Orders in the Admin we always need tax out
            if ( $order_exclude_tax ) :
				$subtotal = self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes' ? self::$subtotal_ex_tax : $subtotal;
                $tax_label = 1; //ex. tax
            else :
            	if ( self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes' ) {
                 	$tax_label = 2; //inc. tax
           		} else {
            		$subtotal = self::$subtotal_ex_tax;
                	$tax_label = 1; //inc. tax
            	}
                
            endif;

        endif;

		// Don't show the discount bit in the subtotal because discount will be calculated after taxes
		// thus in the grand total (not the subtotal). */
		if ( self::get_options()->get_option('jigoshop_tax_after_coupon') == 'yes' ) {
			$discount = 0;
		}
		
        /* Display totals with discount & shipping applied? */
        // This is only 'true' with the 'Retail Price' displays on Cart, Checkout, View Order
        // Someone should explain why this is used instead of 'Subtotal'
        if ( $apply_discount_and_shipping ) :

            $subtotal = $subtotal + jigoshop_cart::get_cart_shipping_total( false );

            $subtotal = ( $discount > $subtotal ) ? $subtotal : $subtotal - $discount;

        endif;

        /* Return a pretty number or just the float. */
        $return = $for_display ? jigoshop_price($subtotal, array('ex_tax_label' => $tax_label)) : number_format($subtotal, 2, '.', '');

        return $return;

    }

    public static function get_tax_for_display($tax_class) {

        $return = false;

        if ( (jigoshop_cart::get_tax_amount($tax_class, false) > 0 
        	&& jigoshop_cart::get_tax_rate($tax_class) > 0) 
        	|| jigoshop_cart::get_tax_rate($tax_class) !== false ) {
        	
            $return = self::$tax->get_tax_class_for_display($tax_class) . ' (' . (float) jigoshop_cart::get_tax_rate($tax_class) . '%) ';

            // only show estimated tag when customer is on the cart page and no shipping calculator is enabled to be able to change country
            if (!jigoshop_shipping::show_shipping_calculator() && is_cart()) {

                if (self::needs_shipping() && jigoshop_shipping::is_enabled()) {
                    $return .= '<small>' . sprintf(__('estimated for: %s', 'jigoshop'), __(jigoshop_countries::$countries[jigoshop_countries::get_base_country()], 'jigoshop')) . '</small>';
                } else {
                    $return .= '<small>' . sprintf(__('estimated for: %s', 'jigoshop'), __(jigoshop_countries::$countries[jigoshop_customer::get_country()], 'jigoshop')) . '</small>';
                }

            }
        }

        return $return;
    }

    public function show_retail_price($order = '') {

        if ( self::get_options()->get_option('jigoshop_calc_taxes') != 'yes' )
            return false;

//        if ( self::get_options()->get_option('jigoshop_display_totals_tax') != 'no' )
//            return false;

        return ( jigoshop_cart::has_compound_tax() || jigoshop_cart::tax_after_coupon() );

    }


    public function tax_after_coupon() {

        if ( self::get_options()->get_option('jigoshop_calc_taxes') != 'yes' )
            return false;

        if ( !jigoshop_cart::get_total_discount() )
            return false;

        return ( self::get_options()->get_option('jigoshop_tax_after_coupon') == 'yes' );


    }

    public static function get_cart_discount_leftover() {
        return self::$cart_discount_leftover;
    }

    // after calculation. Used with admin pages only
    public static function get_total_tax_rate() {
        return self::$tax->get_total_tax_rate(self::$subtotal);
    }

    public static function get_taxes_as_array($taxes_as_string) {
        return self::$tax->get_taxes_as_array($taxes_as_string, 100);
    }

    public static function has_compound_tax() {
        return self::$tax->is_compound_tax();
    }

    public static function get_taxes_as_string() {
        return self::$tax->get_taxes_as_string();
    }

    public static function get_applied_tax_classes() {
        return self::$tax->get_applied_tax_classes();
    }

    public static function get_tax_rate($tax_class) {
        return self::$tax->get_tax_rate($tax_class);
    }

    public static function get_tax_amount($tax_class, $with_price = true) {
        return ($with_price ? jigoshop_price(self::$tax->get_tax_amount($tax_class)) : number_format(self::$tax->get_tax_amount($tax_class), 2, '.', ''));
    }

    public static function get_tax_divisor() {
        return self::$tax->get_tax_divisor();
    }

    public static function is_not_compounded_tax($tax_class) {
        return self::$tax->is_tax_non_compounded($tax_class);
    }

    /* Shipping total after calculation. */
    public static function get_cart_shipping_total($for_display = true, $order_exclude_tax = false) {

        /* Quit early if there is no shipping label. */
        if ( !jigoshop_shipping::get_label() )
            return false;

        /* Shipping price is 0.00. */
        if ( jigoshop_shipping::get_total() <= 0 )
            return ($for_display ? __('Free!', 'jigoshop') : 0);

        /* Not calculating taxes. */
        if ( self::get_options()->get_option('jigoshop_calc_taxes') == 'no' )
            return ($for_display ? jigoshop_price(self::$shipping_total) : number_format(self::$shipping_total, 2, '.', ''));

        if ( self::get_options()->get_option('jigoshop_prices_include_tax') == 'no' || $order_exclude_tax ) {

            $return = ($for_display ? jigoshop_price(self::$shipping_total) : number_format(self::$shipping_total, 2, '.', ''));

            if ( self::$shipping_tax_total > 0 && $for_display )
                $return .= ' <small>' . __('(ex. tax)', 'jigoshop') . '</small>';

        } else {

            $return = ($for_display ? jigoshop_price(self::$shipping_total + self::$shipping_tax_total) : number_format(self::$shipping_total + self::$shipping_tax_total, 2, '.', ''));
            if ( self::$shipping_tax_total > 0 && $for_display )
                $return .= ' <small>' . __('(inc. tax)', 'jigoshop') . '</small>';

        }

        return $return;

    }

    /* Title of the chosen shipping method. */
    function get_cart_shipping_title() {

		// in this instance we want the title of the shipping method only. If no title is provided, use
		// the label. 
		$title = jigoshop_shipping::get_chosen_method_title();
		$label = ($title ? $title : jigoshop_shipping::get_label());
        if ( !$label ) 
            return false;

        return sprintf(__('via %s','jigoshop'), $label);

    }

    /**
     * Applies a coupon code
     *
     * @param   string  code    The code to apply
     * @return   bool   True if the coupon is applied, false if it does not exist or cannot be applied
     */
    function add_discount( $coupon_code ) {

        $the_coupon = JS_Coupons::get_coupon( $coupon_code );

        /* Don't continue if the coupon isn't valid. */
        if ( ! self::valid_coupon( $coupon_code ) ) return false;

        /* Check for other individual_use coupons before adding this coupon. */
        if ( ! empty( self::$applied_coupons )) foreach ( self::$applied_coupons as $coupon ) {
            $this_coupon = JS_Coupons::get_coupon( $coupon );
            if ( $this_coupon['individual_use'] ) {
				jigoshop::add_error(__("There is already an 'individual use' coupon on the Cart.  No other coupons can be added until it is removed.", 'jigoshop'));
				return false;
			}
        }

        /* Remove other coupons if this one is individual_use. */
        if ( $the_coupon['individual_use'] ) {
        	if ( ! empty( self::$applied_coupons )) {
				jigoshop::add_error(__("This is an 'individual use' coupon.  All other discount coupons have been removed.", 'jigoshop'));
				self::$applied_coupons = array();
			}
		}
		
		// for Jigoshop 1.4.2 'self::valid_coupon' no longer returns error if coupon already applied
		// check now and only add a new coupon if it isn't already applied
        if ( ! self::has_discount( $coupon_code ) && !empty( $_POST['coupon_code'] ) ) self::$applied_coupons[] = $coupon_code;
        
        self::set_session();
        jigoshop::add_message(__('Discount coupon applied successfully.', 'jigoshop'));

        return true;

    }

    function valid_coupon($coupon_code) {

        if (!$the_coupon = JS_Coupons::get_coupon($coupon_code)) {
            jigoshop::add_error(__('Coupon does not exist or is no longer valid!', 'jigoshop'));
            return false;
        }

        $payment_method = !empty($_POST['payment_method']) ? $_POST['payment_method'] : '';
        $pay_methods    = !is_array($the_coupon['pay_methods']) && !empty($the_coupon['pay_methods']) ? array($the_coupon['pay_methods']) : $the_coupon['pay_methods'];

        /* Whether the order has a valid payment method which the coupon requires. */
		if ( !empty($pay_methods) ) {

			if ( !empty($payment_method) && !in_array($payment_method, $pay_methods) ) {
				jigoshop::add_error(sprintf(__("The coupon '%s' is invalid with that payment method!", 'jigoshop'), $coupon_code));
				return false;
			}

        }

        /* Subtotal minimum or maximum. */
        if ( !empty($the_coupon['order_total_min']) || !empty($the_coupon['order_total_max']) ) {

            /* Can't use the jigoshop_cart::get_cart_subtotal() method as it's not ready at this point yet. */
            $subtotal = self::$cart_contents_total;
			
            $order_total_max = apply_filters( 'jigoshop_coupon_order_total_max', $the_coupon['order_total_max'], $the_coupon);
            if ( !empty($the_coupon['order_total_max']) && $subtotal > $order_total_max ) {
                jigoshop::add_error(sprintf(__('Your subtotal does not match the <strong>maximum</strong> order total requirements of %.2f for coupon "%s" and it has been removed.', 'jigoshop'), $order_total_max, $coupon_code));
                return false;
            }

            $order_total_min = apply_filters( 'jigoshop_coupon_order_total_min', $the_coupon['order_total_min'], $the_coupon);
            if ( !empty($the_coupon['order_total_min']) && $subtotal < $order_total_min ) {
                jigoshop::add_error(sprintf(__('Your subtotal does not match the <strong>minimum</strong> order total requirements of %.2f for coupon "%s" and it has been removed.', 'jigoshop'), $order_total_min, $coupon_code));
                return false;
            }
        }

        // Check it can be used with cart
        // get_coupon() checks for valid coupon code. don't go any further without one
        if (!JS_Coupons::get_coupon($coupon_code)) {
            jigoshop::add_error(__('Invalid coupon!', 'jigoshop'));
            return false;
        }

        // Check if coupon products are in cart
        if ( ! jigoshop_cart::has_valid_coupon_for_products( $the_coupon ) ) {
            jigoshop::add_error(__('No products in your cart match that coupon!', 'jigoshop'));
            return false;
        }

        return true;

    }

    function has_valid_coupon_for_products( $thecoupon ) {

        /* Look through each product in the cart for a valid coupon. */
        foreach ( self::$cart_contents as $product )
            if ( JS_Coupons::is_valid_coupon_for_product( $thecoupon['code'], $product ) )
                return true;

        return false;

    }

    /** returns whether or not a free shipping coupon has been applied */
    function has_free_shipping_coupon() {

		if ( ! empty( self::$applied_coupons )) foreach ( self::$applied_coupons as $code ) {
			if ( $coupon = JS_Coupons::get_coupon( $code) ) {
				if ( $coupon['free_shipping'] )
					if ( self::has_valid_coupon_for_products( $coupon )) return true;
			}
		}
		return false;

    }

    /** returns whether or not a discount has been applied */
    function has_discount($code) {

        return (in_array($code, self::$applied_coupons));

    }

    /** Returns the total discount amount. */
    function get_total_discount() {

        if ( empty( self::$discount_total ) )
            return false;

		return ( jigoshop_price(self::$discount_total) );
		
		/*
		 * No idea what all the following is ... hacking as above 'return' ignores it
		 */
        $total = self::get_cart_subtotal(false) + self::get_cart_shipping_total(false);
		
		if ( self::get_options()->get_option('jigoshop_calc_taxes') == 'yes'
			&& self::get_options()->get_option('jigoshop_prices_include_tax') == 'no') {
			
			$total += self::$tax->get_non_compounded_tax_amount() + self::$tax->get_compound_tax_amount();
		}
		$discount = self::$discount_total;
		return ( $discount > $total )
			? jigoshop_price($total)
			: jigoshop_price($discount);

    }

    /**
     * Gets and formats a list of cart item data + variations for display on the frontend
     */
    static function get_item_data( $cart_item, $flat = false ) {

        $has_data = false;
		$return = '';
		
        if (!$flat) $return .= '<dl class="variation">';

        // Variation data
        if($cart_item['data'] instanceof jigoshop_product_variation && is_array($cart_item['variation'])) :

            $variation_list = array();

            foreach ( $cart_item['variation'] as $name => $value ) :

                $name = str_replace('tax_', '', $name);
                if ( taxonomy_exists( 'pa_'.$name ) ) :

                    $terms = get_terms( 'pa_'.$name, array( 'orderby' => 'slug', 'hide_empty' => '0' ) );

                    foreach ( $terms as $term )
                        if ( $term->slug == $value ) $value = $term->name;

                    $name = get_taxonomy( 'pa_'.$name )->labels->name;
                    $name = jigoshop_product::attribute_label('pa_'.$name);

                endif;

                $variation_list[] = $flat
                                    ? sprintf('%s: %s<br />', $name, $value)
                                    : sprintf('<dt>%s:</dt> <dd>%s</dd><br />', $name, $value);

            endforeach;

            $return .= $flat
                       ? implode(', ', $variation_list)
                       : implode('',   $variation_list);

            $has_data = true;

        endif;

        // Other data - returned as array with name/value values
        $other_data = apply_filters('jigoshop_get_item_data', array(), $cart_item);

        if ($other_data && is_array($other_data) && sizeof($other_data) > 0 ) :

            $data_list = array();

            foreach ($other_data as $data) :

                $display_value = ( !empty($data['display']) )
                                  ? $data['display']
                                  : $data['value'];

                $data_list[] = $flat
                               ? sprintf('%s: %s<br />', $data['name'], $display_value)
                               : sprintf('<dt>%s:</dt> <dd>%s</dd><br />', $data['name'], $display_value);

            endforeach;

            $return .= $flat
                       ? implode(', ', $data_list)
                       : implode('',   $data_list);

            $has_data = true;

        endif;

        if (!$flat) $return .= '</dl>';

        if ($has_data) return $return;

    }
}