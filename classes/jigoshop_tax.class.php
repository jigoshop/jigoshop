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
 * @package    Jigoshop
 * @category   Checkout
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
class jigoshop_tax {

    public $rates;
    private $apply_to_retail;
    private $tax_amounts;
    private $retail_tax_amount;
    private $imploded_tax_amounts;
    private $tax_divisor;
    private $total_tax_rate;
    private $shipping_tax_class;

    /**
     * sets up current tax class without a divisor. May or may not have
     * args.
     */
    function __construct() {
        $this->tax_divisor = (func_num_args() == 1 ? func_get_arg(0) : -1);

        $this->rates = $this->get_tax_rates();
        $this->apply_to_retail = true;
        $this->tax_amounts = array();
        $this->retail_tax_amount = 0;
        $this->imploded_tax_amounts = '';
        $this->total_tax_rate = 0; 
        $this->shipping_tax_class = '';
    }

    /**
     * create a defined string out of the array to be saved during checkout
     * 
     * @param type $array the tax array to convert
     * @return type string the array as string
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

    /**
     * Converts the string back into the array. To be used with the order class
     * 
     * @param type $taxes_as_string the string that was originally coverted from an array
     * @param type $tax_divisor the divisor used on the tax to avoid decimal rounding errors
     * @return type array of tax information
     */
    public static function get_taxes_as_array($taxes_as_string, $tax_divisor = -1) {
        if (!$taxes_as_string)
            return array();

        $taxes = explode('|', $taxes_as_string);
        $tax_classes = array();
        foreach ($taxes as $tax) :
            $tax_class = explode(':', $tax);
            $tax_info = explode(',', $tax_class[1]);
            $tax_classes[$tax_class[0]] = array('amount' => ( $tax_divisor > 0 ? $tax_info[0] / $tax_divisor : $tax_info[0]), 'rate' => $tax_info[1], 'retail' => ($tax_info[2] ? true : false), 'display' => $tax_info[3]);
        endforeach;

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
        $classes = array_map('trim', $classes);
        $classes_array = array();
        if (sizeof($classes) > 0)
            foreach ($classes as $class) :
                if ($class)
                    $classes_array[] = $class;
            endforeach;
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
                if ($rate['class']) :
                    $tax_rates_array[$rate['country']][$rate['state']][$rate['class']] = array('rate' => $rate['rate'], 'shipping' => $rate['shipping'], 'retail' => $rate['retail'], 'label' => $rate['label']);
                else :
                    // Standard Rate
                    $tax_rates_array[$rate['country']][$rate['state']]['*'] = $rate['rate'] = array('rate' => $rate['rate'], 'shipping' => $rate['shipping'], 'retail' => $rate['retail'], 'label' => $rate['label']);
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
    private function find_rate($country, $state = '*', $tax_class = '') {

        $rate['rate'] = 0;

        if (isset($this->rates[$country][$state])) :
            if ($tax_class) :
                if (isset($this->rates[$country][$state][$tax_class])) :
                    $rate = $this->rates[$country][$state][$tax_class];
                endif;
            else :
                $rate = $this->rates[$country][$state]['*'];
            endif;
        elseif (isset($this->rates[$country]['*'])) :
            if ($tax_class) :
                if (isset($this->rates[$country]['*'][$tax_class])) :
                    $rate = $this->rates[$country]['*'][$tax_class];
                endif;
            else :
                $rate = $this->rates[$country]['*']['*'];
            endif;
        endif;

        // if apply_to_retail has been set to false, don't reset it. 
        // eg. Shipping tax could reset it to true again
        if (array_key_exists('retail', $rate) && $this->apply_to_retail) :
            $this->apply_to_retail = ( $rate['retail'] == 'yes' ? true : false );
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
        $state = (jigoshop_customer::get_shipping_state() ? jigoshop_customer::get_shipping_state() : '*');

        return array_keys($this->rates[$country][$state]);
    }

    private function get_online_label_for_customer($class = '*') {
        $country = jigoshop_customer::get_shipping_country();
        $state = (jigoshop_customer::get_shipping_state() ? jigoshop_customer::get_shipping_state() : '*');

        return $this->rates[$country][$state][$class]['label'];
    }

    /**
     * gets the tax classes for the shops base country and state
     * @return type array of tax classes
     */
    public function get_tax_classes_for_base() {
        $country = jigoshop_countries::get_base_country();
        $state = (jigoshop_countries::get_base_state() ? jigoshop_countries::get_base_state() : '*');

        return array_keys($this->rates[$country][$state]);
    }

    /**
     * determines if tax is applied to retail value or not. Since tax classes
     * are ordered according if they applied to retail or not, this will be false
     * when all tax calculations are completed if there was mixed class types.
     * 
     * @return type boolean true if applied to retail, otherwise false
     */
    public function is_applied_to_retail() {
        return $this->apply_to_retail;
    }

    /**
     * gets the amount of tax that has been applied to the retail value
     * @return type float value of retail tax
     */
    public function get_retail_tax_amount() {
        return ($this->tax_divisor > 0 ? number_format($this->retail_tax_amount / $this->tax_divisor, 2, '.', '') : number_format($this->retail_tax_amount, 2, '.', ''));
    }

    /**
     * gets the amount of tax that has been applied to non retail value
     * @return type float value of non retail tax
     */
    public function get_non_retail_tax_amount() {
        $tax_amount = 0;
        foreach ($this->get_applied_tax_classes() as $tax_class) :
            $tax_amount += $this->tax_amounts[$tax_class]['amount'];
        endforeach;
        
        return ($this->tax_divisor > 0 ? number_format(($tax_amount / $this->tax_divisor) - $this->get_retail_tax_amount(), 2, '.', '') : number_format($tax_amount - $this->get_retail_tax_amount, 2, '.', ''));
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
        $retail_tax_amount = 0;
        $total_tax = 0;

        // using order of tax classes for customer since order is important
        if ($this->get_tax_classes_for_customer())
            foreach ($this->get_tax_classes_for_customer() as $tax_class) :

                // make sure that the product is charging this particular tax_class. 
                // TODO: remember standard rate here. //should work on standard since it will be added to the product if existing
                if (!in_array($tax_class, $tax_classes))
                    return;

                $rate = $this->get_rate($tax_class);

                if ($this->is_applied_to_retail()) :
                    $tax = $this->calc_tax($total_item_price, $rate, $prices_include_tax);
                    if ($tax > 0) :
                        $tax_amount[$tax_class]['amount'] = $tax;
                        $tax_amount[$tax_class]['rate'] = $rate;
                        $tax_amount[$tax_class]['retail'] = true;
                        $tax_amount[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : 'Tax');
                        $retail_tax_amount += $tax;
                        $total_tax += $tax;
                    endif;
                else :
                    $tax = $this->calc_tax($total_item_price + $retail_tax_amount, $rate, $prices_include_tax);
                    if ($tax > 0) :
                        $tax_amount[$tax_class]['amount'] = $tax;
                        $tax_amount[$tax_class]['rate'] = $rate;
                        $tax_amount[$tax_class]['retail'] = false;
                        $tax_amount[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : 'Tax');
                        $total_tax += $tax;
                    endif;
                endif;

            endforeach;

        $this->retail_tax_amount = $retail_tax_amount;
        $this->tax_amounts = $tax_amount;
        $this->imploded_tax_amounts = $this->array_implode($this->tax_amounts);
        $this->total_tax_rate = round($total_tax / $total_item_price * 100, 2); 
    }

    // TODO: prices include tax?? Do we worry about shipping tax?
    public function update_tax_amount_with_shipping_tax($total_price) {
        $this->update_tax_amount($this->shipping_tax_class, $total_price);
    }
    
    //TODO: what about prices_include_tax??? What happens here? does shipping affect those prices?
    public function update_tax_amount($tax_class, $total_price) {
        if ($tax_class) :
            $tax_rate = $this->get_rate($tax_class);
            $tax = $this->calc_tax($total_price, $tax_rate, false);//for now just don't include taxes in price.
            $this->tax_amounts[$tax_class]['amount'] = $tax;
            $this->imploded_tax_amounts = $this->array_implode($this->tax_amounts);
            
            $retail_tax = 0;
            foreach($this->get_applied_tax_classes() as $tax_class) :
                if ($this->tax_amounts[$tax_class]['retail']) :
                    $retail_tax += $this->tax_amounts[$tax_class]['amount'];
                endif;
            endforeach;
            
            $this->retail_tax_amount = $retail_tax;
        endif;
    }
    /**
     * retrieve the tax classes that have been applied to the items
     * @return type array of tax classes
     */
    public function get_applied_tax_classes() {
        return array_keys($this->tax_amounts);
    }

    /**
     * get the tax class that was entered by the user to display
     * @param type $tax_class the tax class to retreive
     * @return type string which is the unsanitized tax class
     */
    public function get_tax_class_for_display($tax_class) {
        return $this->tax_amounts[$tax_class]['display'];
    }

    /**
     * Gets the amount of tax for the particular tax class
     * @param type $tax_class the tax class to retrieve the tax amount for
     * @return type returns the tax amount with 2 decimal places
     */
    function get_tax_amount($tax_class, $has_shipping_tax = true) {
        return ($this->tax_divisor > 0 ? $this->tax_amounts[$tax_class]['amount'] / $this->tax_divisor : $this->tax_amounts[$tax_class]['amount']);

/*        if ($has_shipping_tax) :
            if ($this->shipping_tax_class == $tax_class) :
                return ($this->tax_divisor > 0 ? ($this->tax_amounts[$tax_class]['amount'] + (jigoshop_shipping::get_tax() * $this->tax_divisor)) / $this->tax_divisor : $this->tax_amounts[$tax_class]['amount'] + jigoshop_shipping::get_tax());
            else :
                return ($this->tax_divisor > 0 ? $this->tax_amounts[$tax_class]['amount'] / $this->tax_divisor : $this->tax_amounts[$tax_class]['amount']);
            endif;
        else :
        endif;
*/
    }

    /**
     * get the tax rate at which the tax class is applying
     * @param type $tax_class the class to find the rate for
     * @return type the rate of tax
     */
    function get_tax_rate($tax_class) {
        return $this->tax_amounts[$tax_class]['rate'];
    }

    /**
     * validate if this is retail tax or not. If it's not retail tax
     * the tax gets applied once all retail taxes have been added into the 
     * retail price of the item.
     * 
     * @param type $tax_class the class to find if retail tax or not
     * @return type boolean true if retail tax otherwise false
     */
    function is_tax_retail($tax_class) {
        return $this->tax_amounts[$tax_class]['retail'];
    }
    
    function is_shipping_tax_retail() {
        return $this->is_tax_retail($this->shipping_tax_class);
    }

    /**
     * Get the current taxation rate using find_rate()
     *
     * @param   object	Tax Class
     * @return  int
     */
    function get_rate($tax_class = '') {

        /* Checkout uses customer location, otherwise use store base rate */
//		if ( defined('JIGOSHOP_CHECKOUT') && JIGOSHOP_CHECKOUT ) :

        $country = jigoshop_customer::get_shipping_country();
        $state = jigoshop_customer::get_shipping_state();

        $rate = $this->find_rate($country, $state, $tax_class);

        return $rate['rate'];

//		else :
//			return $this->get_shop_base_rate( $tax_class );
//		endif;
    }

    /**
     * Get the shop's taxation rate using find_rate()
     *
     * @param   object	Tax Class
     * @return  int
     */
    function get_shop_base_rate($tax_class = '') {

        $country = jigoshop_countries::get_base_country();
        $state = jigoshop_countries::get_base_state();

        $rate = $this->find_rate($country, $state, $tax_class);

        return $rate['rate'];
    }

    /**
     * Get the tax rate based on the country and state. 
     *
     * @param   object	Tax Class
     * @return  mixed		
     */
    function get_shipping_tax_rate($tax_class = '') {

        $this->shipping_tax_class = '';
        //Should always use shipping country and shipping state to apply taxes
        $country = jigoshop_customer::get_shipping_country();
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
                    $this->shipping_tax_class = 'standard';
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

                    //TODO: will need to work out this logic, since data is now data['tax_classes']
                    if ($item['data']->data['tax_classes']) :
                        
                        foreach($item['data']->data['tax_classes'] as $key=>$tax_class) :
                            $found_rate = $this->find_rate($country, $state, $tax_class);

                            if (isset($found_rate['shipping']) && $found_rate['shipping'] == 'yes') :
                                $this->shipping_tax_class = $tax_class;
                                //$found_rates[] = $found_rate['rate'];
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
                    $this->shipping_tax_class = 'standard';
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
