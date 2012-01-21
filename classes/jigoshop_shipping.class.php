<?php

/**
 * Shipping class
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
class jigoshop_shipping extends jigoshop_singleton {

    protected static $enabled = false;
    protected static $shipping_methods = array();
    protected static $chosen_method = null;
    protected static $shipping_total = 0;
    protected static $shipping_tax = 0;
    protected static $shipping_label = null;
    protected static $has_calculable_shipping = false;
    private static $shipping_error_message = null;

    /** Constructor */
    protected function __construct() {

        self::shipping_inits();
        if (get_option('jigoshop_calc_shipping') != 'no') :
            self::$enabled = true;
        endif;
    }

    /**
     * Initialize all shipping modules.
     */
    private static function shipping_inits() {

        do_action('jigoshop_shipping_init'); /* loaded plugins for shipping inits */

        $load_methods = apply_filters('jigoshop_shipping_methods', array());

        foreach ($load_methods as $method) :
            self::$shipping_methods[] = &new $method();
        endforeach;
    }

    public static function is_enabled() {
        return self::$enabled;
    }

    public static function get_total() {
        return self::$shipping_total;
    }

    public static function get_tax() {
        return self::$shipping_tax;
    }

    public static function get_label() {
        return self::$shipping_label;
    }

    public static function get_all_methods() {
        return self::$shipping_methods;
    }

    public static function has_calculable_shipping() {
        return self::$has_calculable_shipping;
    }

    public static function get_available_shipping_methods() {

        $_available_methods = array();

        if (self::$enabled == 'yes') :

            foreach (self::get_all_methods() as $method) :

                if ($method->is_available()) :
                    $_available_methods[$method->id] = $method;
                    if ($method instanceof jigoshop_calculable_shipping)
                        self::$has_calculable_shipping = true;
                endif;

                self::$shipping_error_message = $method->get_error_message();
                
            endforeach;
            

        endif;
        
        if (count($_available_methods) > 0) : 
            self::$shipping_error_message = null;
        endif;

        return $_available_methods;
    }
    
    public static function get_shipping_error_message() {
    	return self::$shipping_error_message;
    }

    public static function reset_shipping_methods() {
        foreach (self::$shipping_methods as $method) :
            $method->reset_method();
        endforeach;
    }

    /**
     * finds the cheapest shipping method
     * @param type $available_methods all methods that are available
     * @param type $tax jigoshop_tax class instance
     * @return type the cheapest shipping method being used
     */
    private static function get_cheapest_method($available_methods, $tax) {
        $_cheapest_fee = '';
        $_cheapest_method = '';
        self::$has_calculable_shipping = false;

        foreach ($available_methods as $method) :
            $method->set_tax($tax);
            $method->calculate_shipping();

            if ($method instanceof jigoshop_calculable_shipping) :
                if (!$method->has_error()) :
                    self::$has_calculable_shipping = true;
                    $fee = $method->shipping_total;
                    if ($fee >= 0 && $fee < $_cheapest_fee || !is_numeric($_cheapest_fee)) :
                        $_cheapest_fee = $fee;
                        $_cheapest_method = $method->id;
                    endif;
                endif;
            else : // handle normal shipping methods
                $fee = $method->shipping_total;
                if ($fee >= 0 && $fee < $_cheapest_fee || !is_numeric($_cheapest_fee)) :
                    $_cheapest_fee = $fee;
                    $_cheapest_method = $method->id;
                endif;
            endif;
        endforeach;

        return $_cheapest_method;
    }

    /** 
     * Calculate the shipping price
     * 
     * @param type $tax jigoshop_tax class instance
     */
    public static function calculate_shipping($tax) {

        if (self::$enabled == 'yes') :

            self::reset_shipping_methods();
            self::reset_shipping(); // do not reset session (chosen_shipping_method_id)
            $calc_cheapest = false;

            if (isset( jigoshop_session::instance()->chosen_shipping_method_id)) :
                $chosen_method = jigoshop_session::instance()->chosen_shipping_method_id;
            else :
                $chosen_method = '';
                $calc_cheapest = true;
            endif;

            $_available_methods = self::get_available_shipping_methods();

            if (sizeof($_available_methods) > 0) :

                if (isset( jigoshop_session::instance()->selected_rate_id )) :

                    //make sure all methods are re-calculated since prices have been reset. Otherwise the other shipping
                    //method prices will show free
                    foreach ($_available_methods as $method) :
                        $method->set_tax($tax);
                        $method->calculate_shipping();
                    endforeach;

                    // select chosen method
                    if ($_available_methods[$chosen_method] && !$_available_methods[$chosen_method]->has_error()) :
                        $chosen_method = $_available_methods[$chosen_method]->id;

                    // error returned from service api. Need to auto calculate cheapest method now
                    else :
                        $chosen_method = self::get_cheapest_method($_available_methods, $tax);
                    endif;

                else :
                    // current jigoshop functionality
                    $_cheapest_method = self::get_cheapest_method($_available_methods, $tax);
                    if ($calc_cheapest || !isset($_available_methods[$chosen_method])) :
                        $chosen_method = $_cheapest_method;
                    endif;
                endif;

                if ($chosen_method) :

                    //sets session in the method choose()
                    $_available_methods[$chosen_method]->choose();

                    // if selected_rate_id has been set, it means there are calculable shipping methods
                    if (isset( jigoshop_session::instance()->selected_rate_id )) :
                        if ( jigoshop_session::instance()->selected_rate_id != 'no_rate_id' && $_available_methods[$chosen_method] instanceof jigoshop_calculable_shipping) :
                            self::$shipping_total = $_available_methods[$chosen_method]->get_selected_price( jigoshop_session::instance()->selected_rate_id );
                            self::$shipping_tax = $_available_methods[$chosen_method]->get_selected_tax( jigoshop_session::instance()->selected_rate_id );
                        else :
                            self::$shipping_total = $_available_methods[$chosen_method]->shipping_total;
                            self::$shipping_tax = $_available_methods[$chosen_method]->shipping_tax;
                        endif;

                    else :
                        self::$shipping_total = $_available_methods[$chosen_method]->shipping_total;
                        self::$shipping_tax = $_available_methods[$chosen_method]->shipping_tax;
                    endif;

                    self::$shipping_label = $_available_methods[$chosen_method]->title;

                endif;

            endif; //sizeof available methods

        endif; //self enabled == 'yes'
    }

    public static function reset_shipping() {
        self::$shipping_total = 0;
        self::$shipping_tax = 0;
        self::$shipping_label = null;
        self::$has_calculable_shipping = false;
        self::$shipping_error_message = null;
    }
}
