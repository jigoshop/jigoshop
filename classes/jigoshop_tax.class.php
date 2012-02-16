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
 * @package		Jigoshop
 * @category	Checkout
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */
class jigoshop_tax {

    public $rates;
    private $compound_tax;
    private $tax_amounts;
    private $non_compound_tax_amount;
    private $imploded_tax_amounts;
    private $tax_divisor;
    private $total_tax_rate;
    private $shipping_tax_class;
    private $has_tax;

    /**
     * sets up current tax class without a divisor. May or may not have
     * args.
     */
    function __construct() {
        $this->tax_divisor = (func_num_args() == 1 ? func_get_arg(0) : -1);

        $this->rates = $this->get_tax_rates();
        $this->compound_tax = false;
        $this->tax_amounts = array();
        $this->non_compound_tax_amount = 0;
        $this->imploded_tax_amounts = '';
        $this->total_tax_rate = 0;
        $this->shipping_tax_class = '';
        $this->has_tax = false;
    }

    /**
     * create a defined string out of the array to be saved during checkout
     *
     * @param array array the tax array to convert
     * @return string the array as string
     */
    private function array_implode($array) {
        $glue = ':';
        if (!is_array($array))
            return $array;
        if (sizeof($array) <= 0)
            return '';
        $array_string = array();
        foreach ($array as $key => $val) {
            if (is_array($val))
                $val = implode(',', $val);
            $array_string[] = "{$key}{$glue}{$val}";
        }
        return implode('|', $array_string);
    }

    public function has_tax() {
        return $this->has_tax;
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

                    if (isset($tax_class[0]) && isset($tax_info[0]) && isset($tax_info[1]) && isset($tax_info[2]) && isset($tax_info[3])) :
                        $tax_classes[$tax_class[0]] = array('amount' => ( $tax_divisor > 0 ? $tax_info[0] / $tax_divisor : $tax_info[0]), 'rate' => $tax_info[1], 'compound' => ($tax_info[2] ? true : false), 'display' => $tax_info[3]);
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
    public function get_total_tax_rate() {
        return $this->total_tax_rate;
    }

    /**
     * Get an array of tax classes
     *
     * @return  array
     */
    function get_tax_classes() {
        $classes = get_option('jigoshop_tax_classes');

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
        $tax_rates = get_option('jigoshop_tax_rates');
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
     * gets the tax classes for the customer based on customer shipping
     * country and state.
     * @return type array of tax classes
     */
    public function get_tax_classes_for_customer() {
        $country = jigoshop_customer::get_shipping_country();

        // just make sure the customer can be charged taxes in the first place
        if ($country != jigoshop_countries::get_base_country()) return array();

        $state = (jigoshop_customer::get_shipping_state() && jigoshop_countries::country_has_states($country)? jigoshop_customer::get_shipping_state() : '*');
        $tax_classes = (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state] : false);
        return ($tax_classes && is_array($tax_classes) ? array_keys( $tax_classes ) : array());

    }

    private function get_online_label_for_customer($class = '*') {
        $country = jigoshop_customer::get_shipping_country();

        $state = (jigoshop_countries::country_has_states($country) && jigoshop_customer::get_shipping_state() ? jigoshop_customer::get_shipping_state() : '*');

        return (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state][$class]['label'] : 'Tax');
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
    public function is_compound_tax() {
        return $this->compound_tax;
    }

    /**
     * gets the amount of tax that has not been compounded
     * @return double value of non compound tax tax
     */
    public function get_non_compounded_tax_amount() {
        //TODO: number_format... might need to change this because of jigoshop options available for formatting numbers on cart
        return ($this->tax_divisor > 0 ? number_format(round($this->non_compound_tax_amount) / $this->tax_divisor, 2, '.', '') : number_format(round($this->non_compound_tax_amount), 2, '.', ''));
    }

    /**
     * gets the amount of tax that has been compounded
     * @return type float value of compound tax
     */
    public function get_compound_tax_amount() {
        $tax_amount = 0;
        foreach ($this->get_applied_tax_classes() as $tax_class) :
            if (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class]['amount'])) :
                $tax_amount += round($this->tax_amounts[$tax_class]['amount']);
            endif;
        endforeach;

        return ($this->tax_divisor > 0 ? number_format(($tax_amount / $this->tax_divisor) - $this->get_non_compounded_tax_amount(), 2, '.', '') : number_format($tax_amount - $this->non_compound_tax_amount, 2, '.', ''));
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
        $non_compound_tax_amount = 0;
        $total_tax = 0;

        // using order of tax classes for customer since order is important
        if ($this->get_tax_classes_for_customer())
            foreach ($this->get_tax_classes_for_customer() as $tax_class) :

                // make sure that the product is charging this particular tax_class.
                if (!in_array($tax_class, $tax_classes))
                    continue;

                $rate = $this->get_rate($tax_class);

                if (!$this->is_compound_tax()) :
                    $tax = $this->calc_tax($total_item_price, $rate, $prices_include_tax);

                    if ($this->has_tax && $tax > 0) :
                        $this->update_tax_amount($tax_class, $tax, false);
                    elseif ($tax > 0) :
                        $tax_amount[$tax_class]['amount'] = $tax;
                        $tax_amount[$tax_class]['rate'] = $rate;
                        $tax_amount[$tax_class]['compound'] = false;
                        $tax_amount[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : 'Tax');
                        $non_compound_tax_amount += $tax;
                        $total_tax += $tax;

                    endif;

                else :
                    $tax = $this->calc_tax($total_item_price + $non_compound_tax_amount, $rate, $prices_include_tax);

                    if ($this->has_tax && $tax > 0) :
                        $this->update_tax_amount($tax_class, $tax, false);
                    elseif ($tax > 0) :
                        $tax_amount[$tax_class]['amount'] = $tax;
                        $tax_amount[$tax_class]['rate'] = $rate;
                        $tax_amount[$tax_class]['compound'] = true;
                        $tax_amount[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : 'Tax');
                        $total_tax += $tax;
                    endif;

                endif;

            endforeach;

            // only update when we haven't calculated taxes yet. Otherwise, if we have
            // taxes already, then the call is made to update tax
            if ( empty($this->tax_amounts) ) :
                $this->has_tax = true;
                $this->non_compound_tax_amount = $non_compound_tax_amount;
                $this->tax_amounts = $tax_amount;
                $this->imploded_tax_amounts = $this->array_implode($this->tax_amounts);

                $tax_rate = 0;
                if ($total_item_price) :
                    $tax_rate = ($prices_include_tax ? round($total_tax / ($total_item_price - $total_tax) * 100, 2) : round($total_tax / $total_item_price * 100, 2));
                endif;

                $this->total_tax_rate = $tax_rate;

            endif;

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

            if (get_option('jigoshop_calc_taxes') == 'yes') :

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

    public function update_tax_amount_with_shipping_tax($tax_amount) {
        // shipping taxes may not be checked, and if they aren't, there will be no shipping tax class. Don't update
        // as the amount will be 0
        if ($this->shipping_tax_class) :
            $this->update_tax_amount($this->shipping_tax_class, round($tax_amount), false);
        endif;
    }

    public function update_tax_amount($tax_class, $amount, $recalculate_tax = true) {

        if ($tax_class) :

            if (empty($this->tax_amounts)) :
                $this->tax_amounts[$tax_class]['rate'] = $this->get_rate($tax_class);
                $this->tax_amounts[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : 'Tax');
                $this->tax_amounts[$tax_class]['compound'] = false;
                $this->has_tax = true;
                $this->total_tax_rate = $this->tax_amounts[$tax_class]['rate'];
            endif;

            if ($recalculate_tax) :
                $rate = $this->get_rate($tax_class);
                $tax = $this->calc_tax($amount, $rate, get_option('jigoshop_prices_include_tax') == 'yes');
                $this->tax_amounts[$tax_class]['amount'] = $tax;
            else :
            	if ( isset($this->tax_amounts[$tax_class]['amount']))
                	$this->tax_amounts[$tax_class]['amount'] += $amount;
            endif;

            $this->imploded_tax_amounts = $this->array_implode($this->tax_amounts);

            $non_compound_tax = 0;
            foreach($this->get_applied_tax_classes() as $tax_class) :
                if (!$this->tax_amounts[$tax_class]['compound']) :
                    $non_compound_tax += $this->tax_amounts[$tax_class]['amount'];
                endif;
            endforeach;

            $this->non_compound_tax_amount = $non_compound_tax;
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
        return (!empty($this->tax_amounts[$tax_class]['display']) ? $this->tax_amounts[$tax_class]['display'] : 'Tax');
    }

    /**
     * Gets the amount of tax for the particular tax class
     * @param string tax_class the tax class to retrieve the tax amount for
     * @param bool has_shipping_tax true if shipping is taxed, false otherwise. Default true
     * @return type returns the tax amount with 2 decimal places
     */
    function get_tax_amount($tax_class, $has_shipping_tax = true) {
        return ($this->tax_divisor > 0 ? (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class]['amount']) ? $this->tax_amounts[$tax_class]['amount'] : 0) / $this->tax_divisor : (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class]['amount']) ? $this->tax_amounts[$tax_class]['amount'] : 0));
    }

    /**
     * get the tax rate at which the tax class is applying
     * @param string tax_class the class to find the rate for
     * @return the rate of tax
     */
    function get_tax_rate($tax_class) {
        return (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class]['rate']) ? $this->tax_amounts[$tax_class]['rate'] : 0);
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

    function is_shipping_tax_non_compounded() {
        return $this->is_tax_non_compounded($this->shipping_tax_class);
    }

    /**
     * Get the current taxation rate using find_rate()
     *
     * @param   string	tax_class the tax class to find rate on
     * @return  int
     */
    function get_rate($tax_class = '*') {

        $country = jigoshop_customer::get_shipping_country();

        $state = (jigoshop_countries::country_has_states($country) && jigoshop_customer::get_shipping_state() ? jigoshop_customer::get_shipping_state() : '*');
        $rate = $this->find_rate($country, $state, $tax_class);
        return $rate['rate'];

    }

    /**
     * Get the shop's taxation rate using find_rate()
     *
     * @param   string	tax_class is the tax class (not object)
     * @return  int
     */
    function get_shop_base_rate($tax_class = '*') {

        $country = jigoshop_countries::get_base_country();
        $state = jigoshop_countries::get_base_state();

        $rate = $this->find_rate($country, $state, $tax_class);

        return $rate['rate'];
    }

    /**
     * Get the tax rate based on the country and state.
     *
     * @param   string	tax_class is the tax class that has shipping tax applied
     * @return  mixed
     */
    function get_shipping_tax_rate($tax_class = '') {

        $this->shipping_tax_class = '';

        $country = jigoshop_customer::get_shipping_country();

        // don't calculate if customer is shipping to another country
        if ($country && $country != jigoshop_countries::get_base_country()) return 0;
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

            // Loop cart and find the highest tax band
            if (sizeof(jigoshop_cart::$cart_contents) > 0) :

                foreach (jigoshop_cart::$cart_contents as $item) :

                    if ($item['data']->get_tax_classes()) :

                        foreach($item['data']->get_tax_classes() as $key=>$tax_class) :
                            $found_rate = $this->find_rate($country, $state, $tax_class);

                            if (isset($found_rate['shipping']) && $found_rate['shipping'] == 'yes') :
                                $this->shipping_tax_class = $tax_class;
                                $found_shipping_rates[] = $found_rate['rate'];
                            endif;

                        endforeach;

                    endif;

                endforeach;

            endif;

           // if (sizeof($found_rates) > 0 && sizeof($found_shipping_rates) > 0) :
            if (sizeof($found_shipping_rates) > 0) :
                //TODO: I don't think we really need to do a standard rate here.
                // rsort($found_rates);
                rsort($found_shipping_rates);
                return $found_shipping_rates[0];
               // if ($found_rates[0] == $found_shipping_rates[0]) :
               //     return $found_shipping_rates[0];
               // else :
                    // Use standard rate
                //    $rate = $this->find_rate($country, $state);
               //     if (isset($rate['shipping']) && $rate['shipping'] == 'yes') :
               //         $this->shipping_tax_class = 'standard';
               //         return $rate['rate'];
               //     endif;
               // endif;

            else :
                // Use standard rate
                $rate = $this->find_rate($country, $state);
                if (isset($rate['shipping']) && $rate['shipping'] == 'yes') :
                    $this->shipping_tax_class = '*'; //standard rate
                    return $rate['rate'];
                endif;
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
     */
    function calc_shipping_tax($price, $rate) {

        $rate = round($rate, 4);

        $tax_amount = $price * ($rate / 100);

        return round($tax_amount, 2);
    }

}
