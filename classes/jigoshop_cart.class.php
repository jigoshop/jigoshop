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
 * @package    Jigoshop
 * @category   Checkout
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
class jigoshop_cart extends jigoshop_singleton {

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
    private static $subtotal_inc_tax;
    private static $tax;

    /** constructor */
    protected function __construct() {

        self::get_cart_from_session();

        self::$applied_coupons = array();

        if (isset($_SESSION['coupons']))
            self::$applied_coupons = $_SESSION['coupons'];

        self::$tax = new jigoshop_tax(100); //initialize tax on the cart with divisor of 100
        // needed to calculate cart total for cart widget. Separated from calculate_totals
        // so that shipping doesn't need to be calculated so many times. Calling the server
        // api's ofter per page request isn't a good idea.
        self::calculate_cart_total();
    }

    /** Gets the cart data from the PHP session */
    function get_cart_from_session() {

        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) :
            $cart = $_SESSION['cart'];

            foreach ($cart as $key => $values) :

                if ($values['data']->exists() && $values['quantity'] > 0) :

                    self::$cart_contents[$key] = array(
                        'product_id' => $values['product_id'],
                        'variation_id' => $values['variation_id'],
                        'variation' => $values['variation'],
                        'quantity' => $values['quantity'],
                        'data' => $values['data']
                    );

                endif;
            endforeach;

        else :
            self::$cart_contents = array();
        endif;

        if (!is_array(self::$cart_contents))
            self::$cart_contents = array();
    }

    /** sets the php session data for the cart and coupon */
    function set_session() {

        // we get here from cart additions, quantity adjustments, and coupon additions
        // reset any chosen shipping methods as these adjustments can effect shipping (free shipping)
        unset($_SESSION['chosen_shipping_method_id']);
        unset($_SESSION['selected_rate_id']); // calculable shipping 

        $_SESSION['cart'] = self::$cart_contents;

        $_SESSION['coupons'] = self::$applied_coupons;

        // This has to be tested. I believe all that needs to be calculated at time of setting session
        // is really the cart total and not shipping. All functions that use set_session are functions
        // that either add to the cart or apply coupons, etc. If the cart page is reloaded, the full
        // calculate_totals is already called.
        self::calculate_cart_total();
    }

    /** Empty the cart */
    function empty_cart() {
        self::$cart_contents = array();
        self::$applied_coupons = array();
        self::reset_totals();
        unset($_SESSION['cart']);
        unset($_SESSION['coupons']);
        unset($_SESSION['chosen_shipping_method_id']);
        unset($_SESSION['selected_rate_id']);
    }

    /**
     * Check if product is in the cart and return cart item key
     * 
     * @param int $product_id
     * @param int $variation_id optional variation id
     * @param array $variation array of attributre values
     * @return int|null
     */
    function find_product_in_cart($product_id, $variation_id, $variation = array()) {

        foreach (self::$cart_contents as $cart_item_key => $cart_item) {
            if (empty($variation_id) && $cart_item['product_id'] == $product_id) {
                return $cart_item_key;
            } else if ($cart_item['product_id'] == $product_id && $cart_item['variation_id'] == $variation_id) {
                if ($variation == $cart_item['variation']) {
                    return $cart_item_key;
                }
            }
        }

        return NULL;
    }

    /**
     * Add a product to the cart
     *
     * @param   string	product_id	contains the id of the product to add to the cart
     * @param   string	quantity	contains the quantity of the item to add
     * @param   int     variation_id
     * @param   array   variation attribute values
     */
    function add_to_cart($product_id, $quantity = 1, $variation_id = '', $variation = array()) {

        if ($quantity < 0) {
            $quantity = 0;
        }

        $found_cart_item_key = self::find_product_in_cart($product_id, $variation_id, $variation);

        if (empty($variation_id)) {
            $product = &new jigoshop_product($product_id);
        } else {
            $product = &new jigoshop_product_variation($variation_id);
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

        // prevents adding products to the cart without enough quantity on hand
        $in_cart_qty = is_numeric($found_cart_item_key) ? self::$cart_contents[$found_cart_item_key]['quantity'] : 0;
        if ($product->managing_stock() && !$product->has_enough_stock($quantity + $in_cart_qty)) {
            if ($in_cart_qty > 0)
                jigoshop::add_error(sprintf(__('We are sorry.  We do not have enough "%s" to fill your request.  You have %d of them in your Cart and we have %d available at this time.', 'jigoshop'), $product->get_title(), $in_cart_qty, $product->get_stock_quantity()));
            else
                jigoshop::add_error(sprintf(__('We are sorry.  We do not have enough "%s" to fill your request. There are only %d left in stock.', 'jigoshop'), $product->get_title(), $product->get_stock_quantity()));
            return false;
        }

        //if product is already in the cart change its quantity
        if (is_numeric($found_cart_item_key)) {

            $quantity = (int) $quantity + self::$cart_contents[$found_cart_item_key]['quantity'];

            self::set_quantity($found_cart_item_key, $quantity);
        } else {//othervise add new product to the cart
            $cart_item_key = sizeof(self::$cart_contents);

            self::$cart_contents[$cart_item_key] = array(
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'variation' => $variation,
                'quantity' => (int) $quantity,
                'data' => $product
            );
        }

        self::set_session();

        return true;
    }

    /**
     * Set the quantity for an item in the cart
     * Remove the item from the cart if no quantity
     * Also remove any product discounts if applied
     *
     * @param   string	cart_item_key	contains the id of the cart item
     * @param   string	quantity	contains the quantity of the item
     */
    function set_quantity($cart_item, $quantity = 1) {
        if ($quantity == 0 || $quantity < 0) :
            $_product = self::$cart_contents[$cart_item];
            if (self::$applied_coupons) :
                foreach (self::$applied_coupons as $key => $code) :
                    $coupon = jigoshop_coupons::get_coupon($code);
                    if (jigoshop_coupons::is_valid_product($code, $_product)) :
                        if ($coupon['type'] == 'fixed_product') {
                            self::$discount_total = self::$discount_total - ( $coupon['amount'] * $_product['quantity'] );
                            unset(self::$applied_coupons[$key]);
                        } else if ($coupon['type'] == 'percent_product') {
                            self::$discount_total = self::$discount_total - (( $_product['data']->get_price() / 100 ) * $coupon['amount']);
                            unset(self::$applied_coupons[$key]);
                        }
                    endif;
                endforeach;
            endif;
            unset(self::$cart_contents[$cart_item]);
        else :
            self::$cart_contents[$cart_item]['quantity'] = $quantity;
        endif;

        self::set_session();
    }

    /**
     * Returns the contents of the cart
     *
     * @return   array	cart_contents
     */
    static function get_cart() {
        return self::$cart_contents;
    }

    /**
     * Gets cross sells based on the items in the cart
     *
     * @return   array	cross_sells	item ids of cross sells
     */
    function get_cross_sells() {
        $cross_sells = array();
        $in_cart = array();
        if (sizeof(self::$cart_contents) > 0) : foreach (self::$cart_contents as $cart_item_key => $values) :
                if ($values['quantity'] > 0) :
                    $cross_sells = array_merge($values['data']->get_cross_sells(), $cross_sells);
                    $in_cart[] = $values['product_id'];
                endif;
            endforeach;
        endif;
        $cross_sells = array_diff($cross_sells, $in_cart);
        return $cross_sells;
    }

    /** gets the url to the cart page */
    function get_cart_url() {
        $cart_page_id = get_option('jigoshop_cart_page_id');
        if ($cart_page_id)
            return get_permalink($cart_page_id);
    }

    /** gets the url to the checkout page */
    function get_checkout_url() {
        $checkout_page_id = get_option('jigoshop_checkout_page_id');
        if ($checkout_page_id) :
            if (is_ssl())
                return str_replace('http:', 'https:', get_permalink($checkout_page_id));
            return get_permalink($checkout_page_id);
        endif;
    }

    /** gets the url to remove an item from the cart */
    function get_remove_url($cart_item_key) {
        $cart_page_id = get_option('jigoshop_cart_page_id');
        if ($cart_page_id)
            return jigoshop::nonce_url('cart', add_query_arg('remove_item', $cart_item_key, get_permalink($cart_page_id)));
    }

    /** looks through the cart to see if shipping is actually required */
    function needs_shipping() {

        if (!jigoshop_shipping::is_enabled())
            return false;
        if (!is_array(self::$cart_contents))
            return false;

        $needs_shipping = false;

        foreach (self::$cart_contents as $cart_item_key => $values) :
            $_product = $values['data'];
            if ($_product->is_type('simple') || $_product->is_type('variable')) :
                $needs_shipping = true;
            endif;
        endforeach;

        return $needs_shipping;
    }

    /** Sees if we need a shipping address */
    function ship_to_billing_address_only() {

        $ship_to_billing_address_only = get_option('jigoshop_ship_to_billing_address_only');

        if ($ship_to_billing_address_only == 'yes')
            return true;

        return false;
    }

    /** looks at the totals to see if payment is actually required */
    function needs_payment() {
        if (self::$total > 0)
            return true;
        return false;
    }

    /** looks through the cart to check each item is in stock */
    function check_cart_item_stock() {

        foreach (self::$cart_contents as $cart_item_key => $values) {
            $_product = $values['data'];

            if (!$_product->is_in_stock() || ($_product->managing_stock() && !$_product->has_enough_stock($values['quantity']))) {
                $error = new WP_Error();
                $error->add('out-of-stock', sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. We only have %d available at this time. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $_product->get_title(), $_product->get_stock_quantity()));
                return $error;
            }
        }

        return true;
    }

    /** reset all Cart totals */
    function reset_totals() {
        self::$total = 0;
        self::$cart_contents_total = 0;
        self::$cart_contents_total_ex_tax = 0;
        self::$cart_contents_weight = 0;
        self::$cart_contents_count = 0;
        self::$shipping_tax_total = 0;
        self::$subtotal = 0;
        self::$subtotal_ex_tax = 0;
        self::$discount_total = 0;
        self::$shipping_total = 0;
        self::$cart_dl_count = 0;
        self::$cart_contents_total_ex_dl = 0; /* for table rate shipping */
        self::$subtotal_inc_tax = 0;
        self::$tax = new jigoshop_tax(100);
        jigoshop_shipping::reset_shipping();
    }

    private static function calculate_cart_total() {

        self::reset_totals();

        if (!count(self::$cart_contents)) :
            self::empty_cart(); /* no items, make sure applied coupons and session data reset, nothing to calculate */
            return;
        endif;
        foreach (self::$cart_contents as $cart_item_key => $values) :
            $_product = $values['data'];
            if ($_product->exists() && $values['quantity'] > 0) :

                self::$cart_contents_count = self::$cart_contents_count + $values['quantity'];

                // If product is downloadable don't apply to product
                if ($_product->product_type == 'downloadable') {
                    self::$cart_dl_count = self::$cart_dl_count + $values['quantity'];
                } else {
                    // If product is downloadable don't apply to weight
                    self::$cart_contents_weight = self::$cart_contents_weight + ($_product->get_weight() * $values['quantity']);
                }

                if (get_option('jigoshop_calc_taxes') == 'yes') :

                    $total_item_price = $_product->get_price() * $values['quantity'] * 100; // Into pounds
                    if ($_product->is_taxable()) :
                        
                        if (get_option('jigoshop_prices_include_tax') == 'yes' && jigoshop_customer::is_customer_outside_base() && (get_option('jigoshop_enable_shipping_calc')=='yes' ||  (defined('JIGOSHOP_CHECKOUT') && JIGOSHOP_CHECKOUT ))) :

                            $total_item_price = $_product->get_price_excluding_tax() * $values['quantity'] * 100;

                            self::$tax->calculate_tax_amounts($total_item_price, $_product->data['tax_classes'], false);

                            // now add customer taxes back into the total item price because customer is outside base
                            // and we asked to have prices include taxes
                            foreach (self::get_applied_tax_classes() as $tax_class) :
                                $total_item_price += self::get_tax_amount($tax_class, false) * 100; // keep tax with multiplier too
                            endforeach;

                        else :
                            self::$tax->calculate_tax_amounts($total_item_price, $_product->data['tax_classes'], get_option('jigoshop_prices_include_tax') == 'yes');
                        endif;

                    endif;

                endif;

                $total_item_price = $total_item_price / 100; // Back to pounds
                //$total_item_price = number_format($total_item_price, 2, '.', '');
                
                if (self::$tax->get_retail_tax_amount()) :
                    if (get_option('jigoshop_prices_include_tax') == 'yes') :
                        self::$subtotal_inc_tax += $total_item_price - self::$tax->get_non_retail_tax_amount();
                    else :
                        self::$subtotal_inc_tax += self::$tax->get_retail_tax_amount() + $total_item_price;
                    endif;
                endif;

                self::$cart_contents_total = self::$cart_contents_total + $total_item_price;

                if ($_product->product_type <> 'downloadable') {
                    self::$cart_contents_total_ex_dl = self::$cart_contents_total_ex_dl + $total_item_price;
                }

                self::$cart_contents_total_ex_tax = self::$cart_contents_total_ex_tax + ($_product->get_price_excluding_tax() * $values['quantity']);

                // Product Discounts for specific product ID's
                if (self::$applied_coupons)
                    foreach (self::$applied_coupons as $code) :
                        $coupon = jigoshop_coupons::get_coupon($code);
                        if (jigoshop_coupons::is_valid_product($code, $values)) {
                            if ($coupon['type'] == 'fixed_product')
                                self::$discount_total += ( $coupon['amount'] * $values['quantity'] );
                            else if ($coupon['type'] == 'percent_product')
                                self::$discount_total += (( $values['data']->get_price() / 100 ) * $coupon['amount']);
                        }
                    endforeach;

            endif;
        endforeach;
    }

    /** calculate totals for the items in the cart */
    function calculate_totals() {

        // extracted cart totals so that the constructor can call it, rather than 
        // this full method. Cart totals are needed for cart widget and themes.
        // Don't want to call shipping api's multiple times per page load
        self::calculate_cart_total();

        // Cart Shipping
        if (self::needs_shipping())
            jigoshop_shipping::calculate_shipping(self::$tax);
        else
            jigoshop_shipping::reset_shipping();

        self::$shipping_total = jigoshop_shipping::get_total();

        self::$shipping_tax_total = jigoshop_shipping::get_tax();
        
        if (get_option('jigoshop_prices_include_tax') == 'yes') :
            self::$tax->update_tax_amount_with_shipping_tax((self::$cart_contents_total_ex_tax + self::$shipping_total) * 100);
        else :
            self::$tax->update_tax_amount_with_shipping_tax((self::$cart_contents_total + self::$shipping_total) * 100);
        endif;

        if (self::$tax->is_shipping_tax_retail()) :
            if (get_option('jigoshop_prices_include_tax') == 'yes') :
                self::$subtotal_inc_tax = self::$cart_contents_total_ex_tax + self::$shipping_total + self::$tax->get_retail_tax_amount();
            else :
                self::$subtotal_inc_tax += self::$shipping_tax_total + self::$shipping_total;
            endif;            
        endif;

        // Subtotal
        self::$subtotal_ex_tax = self::$cart_contents_total_ex_tax;
        self::$subtotal = self::$cart_contents_total;

        // Cart Discounts
        if (self::$applied_coupons)
            foreach (self::$applied_coupons as $code) :
                if ($coupon = jigoshop_coupons::get_coupon($code)) :

                    if ($coupon['type'] == 'fixed_cart') :
                        self::$discount_total = self::$discount_total + $coupon['amount'];
                    elseif ($coupon['type'] == 'percent') :
                        self::$discount_total = self::$discount_total + ( self::$subtotal / 100 ) * $coupon['amount'];
                    elseif ($coupon['type'] == 'fixed_product' && sizeof($coupon['products']) == 0) :
                        // allow coupons for all products without specific product ID's entered
                        self::$discount_total = self::$discount_total + ($coupon['amount'] * self::$cart_contents_count);
                    endif;

                endif;
            endforeach;

        // Total
       if (self::get_subtotal_inc_tax()) :
            self::$total = self::$subtotal_inc_tax - self::$discount_total; // subtotal_inc_tax includes shipping

            foreach (self::get_applied_tax_classes() as $tax_class) :
                if (!self::is_tax_retail($tax_class)) : //tax added on retail tax and subtotal
                    self::$tax->update_tax_amount($tax_class, self::$subtotal_inc_tax * 100);
                    self::$total += self::get_tax_amount($tax_class, false);
                endif;
            endforeach;

        else :
            self::$total = self::$subtotal + self::$shipping_tax_total - self::$discount_total + jigoshop_shipping::get_total();
            foreach (self::get_applied_tax_classes() as $tax_class) :
                self::$total += self::get_tax_amount($tax_class, false);
            endforeach;
        endif;

        if (self::$total < 0)
            self::$total = 0;
    }

    /** gets cart contents total excluding tax. Shipping methods use this, and the contents total are calculated ahead of shipping */
    public static function get_cart_contents_total_excluding_tax() {
        return self::$cart_contents_total_ex_tax;
    }

    /** gets the total (after calculation) */
    function get_total() {
        return jigoshop_price(self::$total);
    }

    /** gets the cart contens total (after calculation) */
    function get_cart_total() {
        return jigoshop_price(self::$cart_contents_total);
    }

    /** gets the sub total (after calculation) */
    function get_cart_subtotal() {

        //return jigoshop_price(self::$subtotal);
        //TODO: still need to see if we need this display or not
        // if we still need these tags, we'll likely need to add a parameter
        // to this method to indicate if subtotal is retail or not
        
        $cart_contents_total_ex_tax = self::$cart_contents_total_ex_tax;
        $subtotal = self::$subtotal;
        $subtotal_inc_tax = self::$subtotal_inc_tax;

        if (get_option('jigoshop_display_totals_tax') == 'excluding' || ( defined('JIGOSHOP_CHECKOUT') && JIGOSHOP_CHECKOUT )) :

            if (get_option('jigoshop_prices_include_tax') == 'yes') :

                $return = jigoshop_price(self::$cart_contents_total_ex_tax);

            else :

                $return = jigoshop_price(self::$subtotal);

            endif;
            
            

            if (self::$subtotal - self::$cart_contents_total_ex_tax > 0) :
                $return .= __(' <small>(ex. tax)</small>', 'jigoshop');
            endif;
            return $return;

        else :

            if (get_option('jigoshop_prices_include_tax') == 'yes') :

                $return = jigoshop_price(self::$subtotal);

            else :
                //don't use accessor function here, as it may not be right
                $return = jigoshop_price(self::$subtotal_inc_tax);

            endif;

            if (self::$subtotal_inc_tax - self::$subtotal > 0) :
                $return .= __(' <small>(inc. tax)</small>', 'jigoshop');
            endif;
            return $return;

        endif;
    }

    /**
     * gets the cart subtotal including retail taxes (after calculation) if necessary. 
     * Since the tax rates loop in order of retail tax first followed by non retail tax,
     * if is_applied_to_retail is false, then we need to return subtotal with tax, otherwise
     * don't return it. If only non retail tax is applied, this function will always return
     * false which is good.
     */
    public static function get_subtotal_inc_tax($use_price = true) {
        return (self::$tax->is_applied_to_retail() ? false : ($use_price ? jigoshop_price(self::$subtotal_inc_tax) : self::$subtotal_inc_tax));
    }

    public static function get_tax_class_for_display($tax_class) {
        return self::$tax->get_tax_class_for_display($tax_class);
    }

    // after calculation. Used with admin pages only
    public static function get_total_tax_rate() {
        return self::$tax->get_total_tax_rate();
    }

    public static function get_taxes_as_array($taxes_as_string) {
        return self::$tax->get_taxes_as_array($taxes_as_string, 100);
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
        if (self::$shipping_tax_total) :
            return ($with_price ? jigoshop_price(self::$tax->get_tax_amount($tax_class)) : self::$tax->get_tax_amount($tax_class));
        else :
            return ($with_price ? jigoshop_price(self::$tax->get_tax_amount($tax_class, false)) : self::$tax->get_tax_amount($tax_class, false));
        endif;
    }

    public static function get_tax_divisor() {
        return self::$tax->get_tax_divisor();
    }

    public static function is_tax_retail($tax_class) {
        return self::$tax->is_tax_retail($tax_class);
    }

    /** gets the shipping total (after calculation) */
    function get_cart_shipping_total() {
        if (jigoshop_shipping::get_label()) :
            if (jigoshop_shipping::get_total() > 0) :

                if (get_option('jigoshop_display_totals_tax') == 'excluding') :

                    $return = jigoshop_price(jigoshop_shipping::get_total());
                    if (self::$shipping_tax_total > 0) :
                        $return .= __(' <small>(ex. tax)</small>', 'jigoshop');
                    endif;
                    return $return;

                else :

                    $return = jigoshop_price(jigoshop_shipping::get_total() + jigoshop_shipping::get_tax());
                    if (self::$shipping_tax_total > 0) :
                        $return .= __(' <small>(inc. tax)</small>', 'jigoshop');
                    endif;
                    return $return;

                endif;

            else :
                return __('Free!', 'jigoshop');
            endif;
        endif;
    }

    /** gets title of the chosen shipping method */
    function get_cart_shipping_title() {
        if (jigoshop_shipping::get_label()) :
            return __('via ', 'jigoshop') . jigoshop_shipping::get_label();
        endif;
        return false;
    }

    /**
     * Applies a coupon code
     *
     * @param   string	code	The code to apply
     * @return   bool	True if the coupon is applied, false if it does not exist or cannot be applied
     */
    function add_discount($coupon_code) {

        if ($the_coupon = jigoshop_coupons::get_coupon($coupon_code)) :

            // Check if applied
            if (jigoshop_cart::has_discount($coupon_code)) :
                jigoshop::add_error(__('Discount code already applied!', 'jigoshop'));
                return false;
            endif;

            // Check it can be used with cart
            // get_coupon() checks for valid coupon. don't go any further without one
            if (!jigoshop_coupons::get_coupon($coupon_code)) :
                jigoshop::add_error(__('Invalid coupon!', 'jigoshop'));
                return false;
            endif;

            // if it's a percentage discount for products, make sure it's for a specific product, not all products
            if ($the_coupon['type'] == 'percent_product' && sizeof($the_coupon['products']) == 0) :
                jigoshop::add_error(__('Invalid coupon!', 'jigoshop'));
                return false;
            endif;

            // before adding this coupon, make sure no individual use coupons already exist
            foreach (self::$applied_coupons as $coupon) :
                $coupon = jigoshop_coupons::get_coupon($coupon);
                if ($coupon['individual_use'] == 'yes') :
                    self::$applied_coupons = array();
                endif;
            endforeach;

            // If its individual use then remove other coupons
            if ($the_coupon['individual_use'] == 'yes') :
                self::$applied_coupons = array();
            endif;

            self::$applied_coupons[] = $coupon_code;
            self::set_session();
            jigoshop::add_message(__('Discount code applied successfully.', 'jigoshop'));
            return true;

        else :
            jigoshop::add_error(__('Coupon does not exist or is no longer valid!', 'jigoshop'));
            return false;
        endif;
        return false;
    }

    /** returns whether or not a discount has been applied */
    function has_discount($code) {
        if (in_array($code, self::$applied_coupons))
            return true;
        return false;
    }

    /** gets the total discount amount */
    function get_total_discount() {
        if (self::$discount_total)
            return jigoshop_price(self::$discount_total); else
            return false;
    }

}