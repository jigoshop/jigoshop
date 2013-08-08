<?php
/**
 * Tax Class
 *
 * Calculates tax added value from a total
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
class jigoshop_tax extends Jigoshop_Base {

    public $rates;
    private $compound_tax;
    private $tax_amounts;
    private $imploded_tax_amounts;
    private $tax_divisor;
    private $shipping_tax_class;
    private $shipable;

    /**
     * sets up current tax class without a divisor. May or may not have
     * args.
     */
    function __construct() {
        // allow for multiple constructors. One with 1 arg, otherwise no arg constructor
        $this->tax_divisor = (func_num_args() == 1 ? func_get_arg(0) : -1);
        $this->init_tax();
    }

    /**
     * provide a way to initialize the data on the class so we don't need to create a
     * new object when we want to reset everything.
     * @since 1.2
     */
    public function init_tax() {
        $this->rates = $this->get_tax_rates();
        $this->compound_tax = false;
        $this->tax_amounts = array();
        $this->imploded_tax_amounts = '';
        $this->shipping_tax_class = '';
        $this->shipable = true; // default to true, as most shops will use shipping
    }
    
    /**
     * create a defined string out of the array to be saved during checkout
     *
     * @param array array the tax array to convert
     * @return string the array as string
     */
    public static function array_implode($array) {
        $glue = ':';
        $internal_glue = '^';
        if (!is_array($array))
            return $array;
        if (sizeof($array) <= 0)
            return '';
        $array_string = array();
        foreach ($array as $key => $val) {
            
            if (is_array($val)) :
                
                // -- reset internal_array
                $internal_array = array();
                foreach ($val as $index => $value) :
                    $internal_array[] = "{$index}{$internal_glue}{$value}";
                endforeach;
                $val = implode(',', $internal_array);
            
            endif;
                
            $array_string[] = "{$key}{$glue}{$val}";
        }
        return implode('|', $array_string);
    }

    /**
     * This function checks the tax_amounts array for the tax class passed in.
     * If set, then tax has already been calculated for the tax class before and
     * return true, otherwise return false.
     * @param string $tax_class
     * @return boolean true if the tax_amounts array has the tax class already
     * defined, otherwise false
     * @since 1.2
     */
    public function has_tax($tax_class) {
        return (isset($this->tax_amounts[$tax_class]));
    }

    /**
     * creates a customized tax string to include
     * @param type $price_ex_tax includes shipping price if shipping is available
     * @param type $total_tax full tax amount
     * @param type $shipping_tax shipping tax if available
     * @return string the tax array as a string
     */
    public static function create_custom_tax($price_ex_tax, $total_tax, $shipping_tax = 0, $divisor = -1) {
        if (!empty($total_tax)) :

            if (empty($shipping_tax)) :
                $shipping_tax = 0;
            endif;
            
            if (empty($divisor)) :
                $divisor = -1;
            endif;
            
            // absolute order must be amount, rate, compound, display, shipping. This is how the original tax
            // array is created, and order matters when calling array_implode as reversing
            // the string back into the array depends on the order
//            $tax_amount['jigoshop_custom_rate']['amount'] = ($divisor > 0 ? ($total_tax - $shipping_tax) * $divisor : $total_tax - $shipping_tax);
			// NOTE: above line commented out in 1.3, this function only used in order-data-save.php if an order is altered and we want total tax
            $tax_amount['jigoshop_custom_rate']['amount'] = ($divisor > 0 ? $total_tax * $divisor : $total_tax);
            $tax_rate = (empty($price_ex_tax) ? 0 : $total_tax / $price_ex_tax) * 100;
            $tax_amount['jigoshop_custom_rate']['rate'] = number_format($tax_rate, 4, '.', '');
            $tax_amount['jigoshop_custom_rate']['compound'] = false;
            $tax_amount['jigoshop_custom_rate']['display'] = __('Tax','jigoshop');
            $tax_amount['jigoshop_custom_rate']['shipping'] = ($divisor > 0 ? $shipping_tax * $divisor : $shipping_tax);
            
            return self::array_implode($tax_amount);
        endif;
        
        return '';
    }
    
    /**
     * Converts the string back into the array. To be used with the order class
     *
     * @param type $taxes_as_string the string that was originally coverted from an array
     * @param type $tax_divisor the divisor used on the tax to avoid decimal rounding errors
     * @return type array of tax information
     */
    public static function get_taxes_as_array($taxes_as_string, $tax_divisor = -1) {

        $tax_classes = array();

        if ($taxes_as_string) :

            $taxes = explode('|', $taxes_as_string);

            foreach ($taxes as $tax) :

                $tax_class = explode(':', $tax);
                if (isset($tax_class[1])) :
                    $tax_info = explode(',', $tax_class[1]);
                    if (isset($tax_class[0])) :
                        foreach ($tax_info as $info) :
                            if (isset($info)) :
                                $key_value = explode('^', $info);
                                if ($key_value[0] == 'rate' || $key_value[0] == 'display' || $key_value[0] == 'compound') :
                                    $tax_classes[$tax_class[0]][$key_value[0]] = (sizeof($key_value) > 1 ? ($key_value[0] == 'compound' && $key_value[1] == null ? false : $key_value[1]) : ($key_value[0] == 'compound' ? false : ''));
                                else :
                                    $tax_classes[$tax_class[0]][$key_value[0]] = (sizeof($key_value) > 1 ? ($tax_divisor > 0 ? $key_value[1] / $tax_divisor : $key_value[1]) : '');
                                endif;
                            endif;
                        endforeach;
                    endif;
                endif;

            endforeach;

        endif;

        return $tax_classes;
    }

    /**
     * the accessor to the string of tax data
     * @return type string of imploded tax amounts
     */
    public function get_taxes_as_string() {
        return $this->imploded_tax_amounts;
    }

    /**
     * The tax divisor to be used to avoid floating point rounding errors
     * @return type integer tax divisor
     */
    public function get_tax_divisor() {
        return $this->tax_divisor;
    }
    
    // TODO: currently used for admin pages. This should change in the future and the proper data displayed on the admin pages
    // this doesn't work correctly as shipping is applied to grand total, and also, discounts etc. Therefore, this calculation is only right if
    // the grandtotal was the total of the cart without the rest. Still this is not a good way of figuring out the tax rate.
    // Ultimately, fixing the admin panel to show all tax classes applied will be the best way.
    public function get_total_tax_rate($total_item_price = 0) {
        $tot_tax_rate = 0;
        //don't include shipping in the call, as the total item price doesn't include shipping
        $total_tax = $this->get_compound_tax_amount(false) + $this->get_non_compounded_tax_amount(false);
        if ($total_item_price > 0) :
            $tot_tax_rate = (self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes' ? round($total_tax / ($total_item_price - $total_tax) * 100, 2) : round($total_tax / $total_item_price * 100, 2));
        endif;

        return $tot_tax_rate;
    }
    
    /**
     * Get an array of tax classes
     *
     * @return  array
     */
    function get_tax_classes() {
        $classes = self::get_options()->get_option( 'jigoshop_tax_classes' );

        $classes = explode("\n", $classes);

        if (is_array($classes)) :
            $classes = array_map('trim', $classes);
            $classes_array = array();
            if (sizeof($classes) > 0) :
                foreach ($classes as $class) :
                    if ($class)
                        $classes_array[] = $class;
                endforeach;
            endif;
        else :
            $classes_array = array();
        endif;

        return $classes_array;
    }

    /**
     * Get the tax rates as an array
     *
     * @return  array
     */
    function get_tax_rates() {
        $tax_rates = self::get_options()->get_option( 'jigoshop_tax_rates' );
        $tax_rates_array = array();
        if ($tax_rates && is_array($tax_rates) && sizeof($tax_rates) > 0)
            foreach ($tax_rates as $rate) :
                if (isset($rate['class'])) :
                    if (isset($rate['country']) && isset($rate['state']) && isset($rate['rate']) && isset($rate['shipping']) && isset($rate['compound']) && isset($rate['label'])) :
                        $tax_rates_array[$rate['country']][$rate['state']][$rate['class']] = array('rate' => $rate['rate'], 'shipping' => $rate['shipping'], 'compound' => $rate['compound'], 'label' => $rate['label']);
                    endif;
                else :
                    // Standard Rate
                    if (isset($rate['country']) && isset($rate['state']) && isset($rate['rate']) && isset($rate['shipping']) && isset($rate['compound']) && isset($rate['label'])) :
                        $tax_rates_array[$rate['country']][$rate['state']]['*'] = array('rate' => $rate['rate'], 'shipping' => $rate['shipping'], 'compound' => $rate['compound'], 'label' => $rate['label']);
                    endif;
                endif;
            endforeach;
        return $tax_rates_array;
    }

    /**
     * Searches for a country / state tax rate
     *
     * @param   string	country
     * @param	string	state
     * @param	object	Tax Class
     * @return  int
     */
    private function find_rate($country, $state = '*', $tax_class = '*') {

        $rate['rate'] = 0;

        if (!jigoshop_countries::country_has_states($country)) :
            $state = '*'; // make sure $state is set to * if user has input some value for a state
        endif;

        if (isset($this->rates[$country][$state])) :
            if ($tax_class && isset($this->rates[$country][$state][$tax_class])) :
                $rate = $this->rates[$country][$state][$tax_class];
            endif;
        elseif (isset($this->rates[$country]['*'])) :
            if ($tax_class && isset($this->rates[$country]['*'][$tax_class])) :
                $rate = $this->rates[$country]['*'][$tax_class];
            endif;
        endif;

        // if compound tax has been set to true, don't reset it.
        // eg. Shipping tax could reset it to false again
        if (!$this->compound_tax) :
            $this->compound_tax = ( isset($rate['compound']) && $rate['compound'] == 'yes' ? true : false );
        endif;

        return $rate;
    }

    /**
     * validate if customer should be taxed or not
     * @return boolean true if customer is to be taxed, otherwise false
     * @since 1.2
     */
    private function charge_taxes_to_customer() {

        // always charge taxes if chosen shipping method is local_pickup since person is in the taxable country
        if (jigoshop_session::instance()->chosen_shipping_method_id == 'local_pickup') :
            return true;
        endif;
        
        $country = ($this->shipable ? jigoshop_customer::get_shipping_country() : jigoshop_customer::get_country());
        $base_country = jigoshop_countries::get_base_country();
        
        if (jigoshop_countries::is_eu_country($base_country)) :
            if (!jigoshop_countries::is_eu_country($country)) : 
                return false;
            endif;
        elseif ($country != $base_country) :
            return false;
        endif;
        
        return true;
    }
    
    /**
     * gets the tax classes for the customer based on customer shipping
     * country and state.
     * @return type array of tax classes
     */
    private function get_tax_classes_for_customer() {

        // if local pickup, we need to use the base tax classes
        if (jigoshop_session::instance()->chosen_shipping_method_id == 'local_pickup') :
            return $this->get_tax_classes_for_base();
        endif;
        
        $country = ($this->shipable ? jigoshop_customer::get_shipping_country() : jigoshop_customer::get_country());
        $state = ($this->shipable ? jigoshop_customer::get_shipping_state() : jigoshop_customer::get_state());
        
        if (!$this->charge_taxes_to_customer()) return array();
        
        $state = ($state && jigoshop_countries::country_has_states($country)? $state : '*');
        $tax_classes = (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state] : false);
        return ($tax_classes && is_array($tax_classes) ? array_keys( $tax_classes ) : array());

    }

    /**
     * return the label for display for base country, if one is set. Otherwise Tax will be returned
     * @param string $class the tax class to lookup
     * @return string label for online display 
     * @since 1.2
     */
    private function get_online_label_for_base($class = '*') {
        $country = jigoshop_countries::get_base_country();
        $state = jigoshop_countries::get_base_state();
        $state = (jigoshop_countries::country_has_states($country) && $state ? $state : '*');

        return (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state][$class]['label'] : __('Tax','jigoshop'));
    }

    private function get_online_label_for_customer($class = '*') {
        
        if (jigoshop_session::instance()->chosen_shipping_method_id == 'local_pickup') :
            return $this->get_online_label_for_base($class);
        endif;
        
        $country = ($this->shipable ? jigoshop_customer::get_shipping_country() : jigoshop_customer::get_country());
        $state = ($this->shipable ? jigoshop_customer::get_shipping_state() : jigoshop_customer::get_state());

        $state = (jigoshop_countries::country_has_states($country) && $state ? $state : '*');

        return (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state][$class]['label'] : __('Tax','jigoshop'));
    }

    /**
     * gets the tax classes for the shops base country and state
     * @return type array of tax classes
     */
    public function get_tax_classes_for_base() {
        $country = jigoshop_countries::get_base_country();
        $state = jigoshop_countries::get_base_state();

        $tax_classes = (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state] : false);

        return ($tax_classes && is_array($tax_classes) ? array_keys($tax_classes) : array());
    }

    /**
     * determines if tax is compound tax or not. Since tax classes
     * are ordered according if they are compounded or not, this will be true
     * when all tax calculations are completed if there was mixed class types.
     *
     * @return type boolean true if compound tax, otherwise false
     */
    public function is_compound_tax($rate = array()) {
        return (empty($rate) ? $this->compound_tax : (isset($rate['compound']) && $rate['compound'] == 'yes' ? true : false ));
    }

    /**
     * returns the total tax amount for all classes based on compounded or not compounded
     * @param boolean $compounded 
     * @return double the total tax amount of all classes on $compounded value. If $compounded is true, then 
     * the total amount of compounded tax amounts will be returned. Otherwise the total amount of regular
     * tax will be returned
     * @since 1.2 
     */
    private function get_total_tax_amount($compounded, $inc_shipping = true) {
        $tax_amount = 0;
        
        if (!empty($this->tax_amounts)) :
            foreach ($this->get_applied_tax_classes() as $tax_class) :
                if (isset($this->tax_amounts[$tax_class]['amount']) && isset($this->tax_amounts[$tax_class]['compound'])) :
                    if ($compounded && $this->tax_amounts[$tax_class]['compound'] == 'yes') :
                        $tax_amount += round($this->tax_amounts[$tax_class]['amount']);
                        if ($inc_shipping && isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id])) :
                            $tax_amount += round($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id], 2);
                        endif;
                    elseif (!$compounded && $this->tax_amounts[$tax_class]['compound'] != 'yes') :
                        $tax_amount += round($this->tax_amounts[$tax_class]['amount']);
                        if ($inc_shipping && isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id])) :
                            $tax_amount += round($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id], 2);
                        endif;
                    endif;
                endif;
            endforeach;
        endif;
        
        return $tax_amount;
        
    }
    
    /**
     * sets whether the product being taxed is shipable or not
     * @param boolean $is_shipable 
     * @since 1.2
     */
    public function set_is_shipable($is_shipable = true) {
    
        $this->shipable = $is_shipable;
    }

    public function get_total_shipping_tax_amount() {
        $tax_amount = 0;
        
        if (!empty($this->tax_amounts)) :
            foreach($this->get_applied_tax_classes() as $tax_class) :
                if (isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id])) :
                    $tax_amount += round($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id], 2);
                endif;
            endforeach;
        endif;
        
        return ($this->tax_divisor > 0 ? number_format($tax_amount / $this->tax_divisor, 2, '.', '') : number_format($tax_amount, 2, '.', ''));
    }
    
    /**
     * gets the amount of tax that has not been compounded
     * @return double value of non compound tax tax
     */
    public function get_non_compounded_tax_amount($inc_shipping = true) {
        
        $tax_amount = $this->get_total_tax_amount(false, $inc_shipping);
        //TODO: number_format... might need to change this because of jigoshop options available for formatting numbers on cart
        return ($this->tax_divisor > 0 ? number_format($tax_amount / $this->tax_divisor, 2, '.', '') : number_format($tax_amount, 2, '.', ''));

    }
    
    /**
     * gets the amount of tax that has been compounded
     * @return type float value of compound tax
     */
    public function get_compound_tax_amount($inc_shipping = true) {
        $tax_amount = $this->get_total_tax_amount(true, $inc_shipping);

        return ($this->tax_divisor > 0 ? number_format(($tax_amount / $this->tax_divisor), 2, '.', '') : number_format($tax_amount, 2, '.', ''));
    }

    /**
     * calculates the taxes on the total item price and creates the tax data array
     *
     * @param type $total_item_price the total value of the item
     * @param type $tax_classes the tax classes applicable to the item
     * @param type $prices_include_tax determines if the tax was already included
     * in the product or not
     */
    public function calculate_tax_amounts($total_item_price, $tax_classes, $prices_include_tax = true) {
        $tax_amount = array();
        $tax_classes_applied = array();
        $non_compound_tax_amount = 0;
        $compounded_tax_amount = 0;
        $total_tax = 0;

        // using order of tax classes for customer since order is important
        if ($this->get_tax_classes_for_customer()) :
            $customer_tax_classes = ($prices_include_tax ? array_reverse($this->get_tax_classes_for_customer()) : $this->get_tax_classes_for_customer());
            foreach ($customer_tax_classes as $tax_class) :

                // make sure that the product is charging this particular tax_class.
                if (!in_array($tax_class, $tax_classes))
                    continue;

                $rate = $this->get_rate($tax_class, false);
                $tax_rate = $rate['rate'];
                
                if (!$this->is_compound_tax($rate)) :
                    $tax = $this->calc_tax($total_item_price - $compounded_tax_amount, $tax_rate, $prices_include_tax);

                    if ($this->has_tax($tax_class)) :
                        $this->update_tax_amount($tax_class, $tax, false);
                        $tax_classes_applied[] = $tax_class;
                    else :
                        $tax_amount[$tax_class]['amount'] = $tax;
                        $tax_amount[$tax_class]['rate'] = $tax_rate;
                        $tax_amount[$tax_class]['compound'] = false;
                        $tax_amount[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : __('Tax','jigoshop'));
                        $tax_classes_applied[] = $tax_class;
                    endif;
                    
                    $non_compound_tax_amount += $tax;
                    $total_tax += $tax;

                else :
                    
                    $tax = $this->calc_tax($total_item_price + $non_compound_tax_amount, $tax_rate, $prices_include_tax);

                    if ($this->has_tax($tax_class)) :
                        $this->update_tax_amount($tax_class, $tax, false);
                        $tax_classes_applied[] = $tax_class;
                    else :
                        $tax_amount[$tax_class]['amount'] = $tax;
                        $tax_amount[$tax_class]['rate'] = $tax_rate;
                        $tax_amount[$tax_class]['compound'] = true;
                        $tax_amount[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : __('Tax','jigoshop'));
                        $tax_classes_applied[] = $tax_class;
                    endif;
                    
                    $compounded_tax_amount += $tax;
                    $total_tax += $tax;

                endif;

            endforeach;

        else :
            $tax_classes = $this->get_tax_classes_for_base();
        
            if (!empty($tax_classes)) :
                foreach ($tax_classes as $tax_class) :
                    // auto calculate zero rate for all other countries outside of tax base
                    $tax_amount[$tax_class]['amount'] = 0;
                    $tax_amount[$tax_class]['rate'] = 0;
                    $tax_amount[$tax_class]['compound'] = false;
                    $tax_amount[$tax_class]['display'] = ($this->get_online_label_for_base($tax_class) ? $this->get_online_label_for_base($tax_class) : __('Tax','jigoshop'));
                    $tax_classes_applied[] = $tax_class;
                endforeach;
            else :
                // auto calculate zero rate for all other countries outside of tax base
                $tax_amount['jigoshop_zero_rate']['amount'] = 0;
                $tax_amount['jigoshop_zero_rate']['rate'] = 0;
                $tax_amount['jigoshop_zero_rate']['compound'] = false;
                $tax_amount['jigoshop_zero_rate']['display'] = __('Tax','jigoshop');
                $tax_classes_applied[] = 'jigoshop_zero_rate';
            endif;
        endif;

        $this->tax_amounts = (empty($this->tax_amounts) ? $tax_amount : array_merge($this->tax_amounts, $tax_amount));
        $this->imploded_tax_amounts = self::array_implode($this->tax_amounts);

        return $tax_classes_applied;
        
    }

    /**
     * calculates the total tax rate that is applied to a product from the applied
     * tax classes defined on the product.
     *
     * @param array the product rates array
     * @return mixed null if no taxes or array is null, otherwise the total tax rate to apply
     */
    public static function calculate_total_tax_rate($product_rates_array) {

        $tax_rate = null;
        if ($product_rates_array && is_array($product_rates_array)) :

            if ( self::get_options()->get_option( 'jigoshop_calc_taxes' ) == 'yes') :

                if (!empty($product_rates_array)) :
                    $tax_rate = 0;

                    foreach($product_rates_array as $tax_class => $value) :
                       if (jigoshop_product::get_non_compounded_tax($tax_class, $product_rates_array)) :
                           $tax_rate += round(jigoshop_product::get_product_tax_rate($tax_class, $product_rates_array), 4);
                       else :
                           $tax_rate += round((100 + $tax_rate) * (jigoshop_product::get_product_tax_rate($tax_class, $product_rates_array) / 100), 4);
                       endif;
                    endforeach;

                endif;

            endif;
        endif;

        return $tax_rate;

    }
    
    /**
     * get applied shipping tax classes
     * @return array tax classes that have applied shipping
     * @since 1.2 
     */
    public function get_shipping_tax_classes() {
        // TODO: once deprecated functions for shipping go, the new_shipping_tax flag
        // can go, and just return an empty array or the array of tax classes obtained
        // from the tax_amounts array.
        $tax_classes = array();
        $new_shipping_tax = false;
        
        foreach($this->tax_amounts as $tax_class => $value) :
            if (isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id])) :
                $tax_classes[] = $tax_class;
                $new_shipping_tax = true;
            endif;
        endforeach;
        
        if ($new_shipping_tax) :
            return $tax_classes;
        else :
            return ($this->shipping_tax_class ? array($this->shipping_tax_class) : array());
        endif;
    }

    /**
     *
     * @deprecated shipping classes are to use calculate_shipping_tax($price, $tax_classes)
     */
    public function update_tax_amount_with_shipping_tax($tax_amount) {
        
        // shipping taxes may not be checked, and if they aren't, there will be no shipping tax class. Don't update
        // as the amount will be 0
        $new_shipping_tax = false;
        foreach($this->tax_amounts as $tax_class => $value) :
            if (isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id])) :
                $new_shipping_tax = true;
                break;
            endif;
        endforeach;
        
        if (!$new_shipping_tax) :
            if ($this->shipping_tax_class) :
                $this->update_tax_amount($this->shipping_tax_class, round($tax_amount), false);
            endif;
        endif;
        
    }

    public function update_tax_amount($tax_class, $amount, $recalculate_tax = true, $overwrite = false) {

        if ($tax_class) :

            if (empty($this->tax_amounts)) :
                
                $rate = $this->get_rate($tax_class, false);
                $this->tax_amounts[$tax_class]['rate'] = $rate['rate'];
                $this->tax_amounts[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : __('Tax','jigoshop'));
                $this->tax_amounts[$tax_class]['compound'] = false;
            endif;

            if ($recalculate_tax) :
                $rate = $this->get_rate($tax_class);
                $tax = $this->calc_tax($amount, $rate, ($this->is_compound_tax() ? false : self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes'));
                $this->tax_amounts[$tax_class]['amount'] = $tax;
            elseif ( $overwrite ) :
                $this->tax_amounts[$tax_class]['amount'] = $amount;
            else :
            	if ( isset($this->tax_amounts[$tax_class]['amount'])) :
                	$this->tax_amounts[$tax_class]['amount'] += $amount;
                else :
                    $this->tax_amounts[$tax_class]['amount'] = $amount;
                endif;
                    
            endif;

            $this->imploded_tax_amounts = self::array_implode($this->tax_amounts);
        endif;
    }
    /**
     * retrieve the tax classes that have been applied to the items
     * @return type array of tax classes
     */
    public function get_applied_tax_classes() {
        return ($this->tax_amounts && is_array($this->tax_amounts) ? array_keys($this->tax_amounts) : array());
    }

    /**
     * get the tax class that was entered by the user to display
     * @param string tax_class the tax class to retreive
     * @return string which is the unsanitized tax class
     */
    public function get_tax_class_for_display($tax_class) {
        return (!empty($this->tax_amounts[$tax_class]['display']) ? $this->tax_amounts[$tax_class]['display'] : __('Tax','jigoshop'));
    }

    /**
     * Gets the amount of tax for the particular tax class
     * @param string tax_class the tax class to retrieve the tax amount for
     * @return type returns the tax amount with 2 decimal places
     */
    function get_tax_amount($tax_class) {
        $tax_amount = 0;
        
        if (isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id])) :
            $tax_amount += round($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id . jigoshop_session::instance()->selected_rate_id], 2);
        endif;
        
        if (isset($this->tax_amounts[$tax_class]['amount'])) :
            $tax_amount += round($this->tax_amounts[$tax_class]['amount']);
        endif;
        
        return ($this->tax_divisor > 0 ? $tax_amount / $this->tax_divisor : $tax_amount);
    }

    /**
     * get the tax rate at which the tax class is applying
     * @param string tax_class the class to find the rate for
     * @return mixed the rate of tax or false if the rate hasn't been set on the class (error)
     */
    function get_tax_rate($tax_class) {
        return (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class]['rate']) ? $this->tax_amounts[$tax_class]['rate'] : false);
    }

    /**
     * validate if this is not compound tax or not. If it's compound tax
     * the tax gets applied once all non compounded taxes have been added into the
     * retail price of the item to the subtotal.
     *
     * @param string tax_class the class to find if compound tax or not
     * @return bool true if not compound tax otherwise false. Defaults to true
     */
    function is_tax_non_compounded($tax_class) {
        return (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class]['compound']) ? !$this->tax_amounts[$tax_class]['compound'] : true);
    }

    /**
     * Get the current taxation rate using find_rate()
     *
     * @param   string	tax_class the tax class to find rate on
     * @param   boolean rate_only if true, returns the tax rate, otherwise return the full rate array
     * @return  mixed return current rate array if rate_only is false, otherwise
     * return the double value of the rate
     */
    function get_rate( $tax_class = '*', $rate_only = true ) {

        if ( jigoshop_session::instance()->chosen_shipping_method_id == 'local_pickup' ) :
            return $this->get_shop_base_rate($tax_class, $rate_only);
        endif;

        $country = ($this->shipable ? jigoshop_customer::get_shipping_country() : jigoshop_customer::get_country());
        $state = ($this->shipable ? jigoshop_customer::get_shipping_state() : jigoshop_customer::get_state());
        
        $state = (jigoshop_countries::country_has_states($country) && $state ? $state : '*');
        $rate = $this->find_rate($country, $state, $tax_class);
        return ($rate_only ? $rate['rate'] : $rate);

    }


    /**
     * Get the shop's taxation rate using find_rate()
     *
     * @param   string	tax_class is the tax class (not object)
     * @return  int
     */
    function get_shop_base_rate($tax_class = '*', $rate_only = true) {

        $country = jigoshop_countries::get_base_country();
        $state = jigoshop_countries::get_base_state();

        $rate = $this->find_rate($country, $state, $tax_class);

        return ($rate_only ? $rate['rate'] : $rate);
    }

    private function get_shipping_tax_rates($tax_classes = array()) {

        $country = jigoshop_customer::get_shipping_country();
        $rates = array();

        // don't calculate if customer is shipping to another country
        if (!$this->charge_taxes_to_customer()) return array();
        $state = jigoshop_customer::get_shipping_state();

        // retains order of tax classes for compound tax
        $customer_tax_classes = $this->get_tax_classes_for_customer();
        // If we are here then shipping is taxable - work it out
        if (!empty($tax_classes)) :
            
            if ($customer_tax_classes) :
                //per item shipping
                foreach ($customer_tax_classes as $tax_class) :
                
                    // make sure that the product is charging this particular tax_class.
                    if (!in_array($tax_class, $tax_classes))
                        continue;
                    
                    $rate = $this->find_rate($country, $state, $tax_class);
                    if (isset($rate['shipping']) && $rate['shipping'] == 'yes') :
                        $rates[$tax_class] = $rate;
                    endif;
                endforeach;
            
                return $rates;
            endif;
            
        else :
            //per order shipping
            $found_rates = array();
            $found_shipping_rates = array();

            $total_tax_rate = 0;
            foreach ($customer_tax_classes as $tax_class) :
                $found_rate = $this->find_rate($country, $state, $tax_class);

                if (isset($found_rate['shipping']) && $found_rate['shipping'] == 'yes') :
                    $total_tax_rate += $found_rate['rate'];
                    $found_shipping_rates[$tax_class] = $found_rate;
                endif;
            endforeach;
            
            if ($total_tax_rate) :
                $rates[$total_tax_rate] = $found_shipping_rates;
            endif;
            
            if (sizeof($rates) > 0) :
                // sort reverse by keys. Largest key wins
                krsort($rates);
                // make sure pointer at first element in array
                reset($rates);
                return $rates[key($rates)];
            endif;
            
        endif;
        
        return $rates; // it will be an empty array
   
    }
    
    /**
     * Get the tax rate based on the country and state.
     *
     * @param   string	tax_class is the tax class that has shipping tax applied
     * @return  mixed
     * @deprecated - use calculate_shipping_tax($price, $tax_classes) to calculate shipping taxes. No need to get the rates first
     */
    function get_shipping_tax_rate($tax_class = '') {

        $this->shipping_tax_class = '';

        $country = jigoshop_customer::get_shipping_country();

        // don't calculate if customer is shipping to another country
        if (!$this->charge_taxes_to_customer()) return 0;
        $state = jigoshop_customer::get_shipping_state();

        // If we are here then shipping is taxable - work it out
        if ($tax_class) :

            // This will be per item shipping
            $rate = $this->find_rate($country, $state, $tax_class);

            if (isset($rate['shipping']) && $rate['shipping'] == 'yes') :
                $this->shipping_tax_class = $tax_class;
                return $rate['rate'];
            else :
                // Get standard rate
                $rate = $this->find_rate($country, $state);
                if (isset($rate['shipping']) && $rate['shipping'] == 'yes') :
                    $this->shipping_tax_class = '*'; //standard rate
                    return $rate['rate'];
                endif;

            endif;

        else :

            // This will be per order shipping - loop through the order and find the highest tax class rate

            $found_rates = array();
            $found_shipping_rates = array();
            $customer_tax_classes = $this->get_tax_classes_for_customer();

            foreach ($customer_tax_classes as $tax_class) :
                $found_rate = $this->find_rate($country, $state, $tax_class);

                if (isset($found_rate['shipping']) && $found_rate['shipping'] == 'yes') :
                    $this->shipping_tax_class = $tax_class;
                    $found_shipping_rates[] = $found_rate['rate'];
                endif;
            endforeach;
            
            if (sizeof($found_shipping_rates) > 0) :
                rsort($found_shipping_rates);
                return $found_shipping_rates[0];
            endif;

        endif;

        return 0; // return false
    }

    /**
     * Calculate the tax using the final value
     *
     * @param   int		Price
     * @param	int		Taxation Rate
     * @return  int
     */
    function calc_tax($price, $rate, $price_includes_tax = true) {

        // To avoid float rounding errors, work with integers (pence)
        $price = round($price * 100, 0);

        if ($price_includes_tax) :

            $rate = ($rate / 100) + 1;
            $tax_amount = $price - ( $price / $rate);

        else :
            $tax_amount = $price * ($rate / 100);
        endif;

        $tax_amount = round($tax_amount); // Round to the nearest pence
        $tax_amount = $tax_amount / 100; // Back to pounds


        //TODO: number_format... may need to change this because of jigoshop options with number formatting
        return number_format($tax_amount, 2, '.', '');
    }

    /**
     * Calculate the shipping tax using the final value
     *
     * @param   int		Price
     * @param	int		Taxation Rate
     * @return  int
     * @deprecated - use calculate_shipping_tax($price, $tax_classes) instead
     */
    function calc_shipping_tax($price, $rate) {

        $rate = round($rate, 4);

        $tax_amount = $price * ($rate / 100);

        return round($tax_amount, 2);
    }
    
    
    /**
     * Calculate the shipping tax using the final price from shipping
     *
     * @param   int		price - Shipping cost (always excluding tax)
     * @param	array	tax_classes - the tax_classes from the product if per-item
     * @since   1.2
     */
     public function calculate_shipping_tax($price, $shipping_method_id, $tax_classes = array()) {
        
        $rates = $this->get_shipping_tax_rates($tax_classes);
        $non_compound_amount = 0;
        $tax_amount = 0;
        
        if (!empty($rates)) :
            foreach ($rates as $tax_class => $rate) :
                
                // no tax on products, but shipping tax should be applied
                if (!isset($this->tax_amounts[$tax_class])) :
                    
                    $this->tax_amounts[$tax_class]['amount'] = 0;
                    $this->tax_amounts[$tax_class]['rate'] = $rate['rate'];
                    $this->tax_amounts[$tax_class]['compound'] = $rate['compound'];
                    $this->tax_amounts[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : __('Tax','jigoshop'));
                    $this->tax_amounts[$tax_class][$shipping_method_id] = 0;
                endif;

                // initialize shipping if not already initialized
                if (!isset($this->tax_amounts[$tax_class][$shipping_method_id])) :
                    $this->tax_amounts[$tax_class][$shipping_method_id] = 0;
                endif;

                $tax_rate = round($rate['rate'], 4);

                if ($rate['compound'] == 'yes') :
                    // calculate compounded taxes. Increment value because of per-item shipping
                    $this->tax_amounts[$tax_class][$shipping_method_id] += ($this->tax_divisor > 0 ? (($price + $non_compound_amount) * ($tax_rate / 100) * $this->tax_divisor) : ($price + $non_compound_amount) * ($tax_rate / 100));
                else :
                    // calculate regular taxes. Increment value because of per-item shipping
                    $non_compound_amount += ($price * ($tax_rate / 100)); // don't use divisor here, as it will be used with compound tax above
                    $this->tax_amounts[$tax_class][$shipping_method_id] += ($this->tax_divisor > 0 ? ($price * ($tax_rate / 100)) * $this->tax_divisor : $price * ($tax_rate / 100));
                endif;

            endforeach;
        else :
            $tax_classes = $this->get_tax_classes_for_base();
        
            if (!empty($tax_classes)) :
                foreach ($tax_classes as $tax_class) :
                    $this->tax_amounts[$tax_class][$shipping_method_id] = 0;
                endforeach;
            else :
                // auto calculate zero rate for all customers outside of tax base
                $this->tax_amounts['jigoshop_zero_rate'][$shipping_method_id] = 0;
            endif;
        endif;
        
        $this->imploded_tax_amounts = self::array_implode($this->tax_amounts);
        
    }

}
