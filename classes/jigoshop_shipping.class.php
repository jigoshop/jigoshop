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
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class jigoshop_shipping extends Jigoshop_Singleton {

    protected static $enabled = false;
    protected static $shipping_methods = array();
    protected static $chosen_method = null;
    protected static $shipping_total = 0;
    protected static $shipping_tax = 0;
    protected static $shipping_label = null;
    private static $shipping_error_message = null;

    /** Constructor */
    protected function __construct() {

		self::shipping_inits();
        if (self::get_options()->get_option('jigoshop_calc_shipping') != 'no') :
            self::$enabled = true;
        endif;
    }

    /**
     * Initialize all shipping modules.
     */
    public static function shipping_inits() {
		
        do_action('jigoshop_shipping_init'); /* loaded plugins for shipping inits */

        $load_methods = apply_filters('jigoshop_shipping_methods', array());

        foreach ($load_methods as $method) :
            self::$shipping_methods[] = new $method();
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

    public static function show_shipping_calculator() {
        return (self::is_enabled() && self::get_options()->get_option('jigoshop_enable_shipping_calc')=='yes' && jigoshop_cart::needs_shipping());
    }

    public static function get_available_shipping_methods() {

        $_available_methods = array();

        if (self::$enabled == 'yes') :
			
            foreach (self::get_all_methods() as $method) :

				if ( jigoshop_cart::has_free_shipping_coupon() && $method->id == 'free_shipping' )
                    $_available_methods[$method->id] = $method;

                if ($method->is_available()) :
                    $_available_methods[$method->id] = $method;
                endif;

            endforeach;

        endif;

		//throw error if there are no shipping methods
		if ( empty( $_available_methods )) {
			self::$shipping_error_message = __('Please enter your shipping destination and postal code to view shipping options and rates.', 'jigoshop');
			if ( self::get_options()->get_option('jigoshop_enable_shipping_calc') == 'no' && is_cart() ) {
			self::$shipping_error_message .= __(' If the Shipping Calculator is not available here, you will need to advance to the Checkout to do this.','jigoshop');
			}
			self::$shipping_error_message .= __(' There may be no methods available for your destination and you should contact us for assistance.', 'jigoshop');
		}
		
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
        $_selected_service = '';

        foreach ($available_methods as $method) :
            $method->set_tax($tax);
            $method->calculate_shipping();

            if ($method->id != 'local_pickup') : // don't let local_pickup be chosen automatically
                if (!$method->has_error()) :
                    $fee = $method->get_cheapest_price(); // obtain cheapest price
                    if ($fee >= 0 && $fee < $_cheapest_fee || !is_numeric($_cheapest_fee)) :
                        $_cheapest_fee = $fee;
                        $_cheapest_method = $method->id;
                        $_selected_service = $method->get_cheapest_service();
                    endif;
                else :
                    $method_error_message = $method->get_error_message();
                
                    if ($method_error_message) :
                        self::$shipping_error_message .= $method_error_message . PHP_EOL;
                    endif;
                    
                endif;
            endif;
        endforeach;

        if (!empty($_selected_service)) :
            $available_methods[$_cheapest_method]->set_selected_service_index($_selected_service);
        endif;

        return $_cheapest_method;
    }

    /**
     * 
     * @return mixed the id of the chosen shipping method or false if none are chosen
     */
    public static function get_chosen_method() {
        $_available_methods = self::get_available_shipping_methods();
        
        foreach ($_available_methods as $method) :
            if ($method->is_chosen()) :
                return $method->id;
            endif;
        endforeach;
        
        return false;
    }
	
	public static function get_chosen_method_title() {
		$_available_methods = self::get_available_shipping_methods();
		
		$chosen_method = self::get_chosen_method();
		return $_available_methods[$chosen_method]->title;
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

            if (!empty( jigoshop_session::instance()->chosen_shipping_method_id)) :
                $chosen_method = jigoshop_session::instance()->chosen_shipping_method_id;
            else :
                $chosen_method = '';
                $calc_cheapest = true;
            endif;

            $_available_methods = self::get_available_shipping_methods();

            if (sizeof($_available_methods) > 0) :

                // have to check numeric selected_rate_id because it can be 0, and empty returns true for 0. That is unwanted behaviour
                if (is_numeric( jigoshop_session::instance()->selected_rate_id ) && !empty($chosen_method)) :

                    //make sure all methods are re-calculated since prices have been reset. Otherwise the other shipping
                    //method prices will show free
                    foreach ($_available_methods as $method) :
                        $method->set_tax($tax);
                        $method->calculate_shipping();
                    endforeach;

                    // select chosen method. 
                    if (isset($_available_methods[$chosen_method]) && $_available_methods[$chosen_method] && !$_available_methods[$chosen_method]->has_error()) :
                        $chosen_method = $_available_methods[$chosen_method]->id;

                    // chosen shipping method had issues, need to auto calculate cheapest method now
                    else :
                        $chosen_method = self::get_cheapest_method($_available_methods, $tax);
                    endif;

                else :
                    // current jigoshop functionality
                    $_cheapest_method = self::get_cheapest_method($_available_methods, $tax);
					if (!$_cheapest_method) :
						// there was an error, and if chosen method was in the session we want to reset that
						$chosen_method = $_cheapest_method;
					else :
						if ($calc_cheapest || !isset($_available_methods[$chosen_method])) :
							$chosen_method = $_cheapest_method;
						endif;
					endif;
                endif;

                if ($chosen_method) :

                    //sets session in the method choose()
                    $_available_methods[$chosen_method]->choose();

                    self::$shipping_total = $_available_methods[$chosen_method]->get_selected_price( jigoshop_session::instance()->selected_rate_id );
                    self::$shipping_tax = $_available_methods[$chosen_method]->get_selected_tax( jigoshop_session::instance()->selected_rate_id );
	                self::$shipping_label = $_available_methods[$chosen_method]->get_selected_service(jigoshop_session::instance()->selected_rate_id );

                endif;

            endif; //sizeof available methods

        endif; //self enabled == 'yes'
    }

    public static function reset_shipping() {
        self::$shipping_total = 0;
        self::$shipping_tax = 0;
        self::$shipping_label = null;
        self::$shipping_error_message = null;
    }
}
