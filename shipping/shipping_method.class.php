<?php
/**
 * Shipping method class
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
class jigoshop_shipping_method {

	var $id;
	var $title;
	var $availability;
	var $countries;
	var $type;
	var $cost				= 0;
	var $fee				= 0;
	var $min_amount			= null;
	var $enabled			= false;
	var $chosen				= false;
	var $shipping_total 	= 0;
	var $shipping_tax 		= 0;
    
    protected $tax_status = '';
    
    protected $rates; // the rates in array format
    protected $has_error = false; // used for shipping methods that have issues and cannot be chosen

    private $tax;
    private $error_message = null;
    
    public function __construct() {        
    	Jigoshop_Base::get_options()->install_external_options_onto_tab( __( 'Shipping', 'jigoshop' ), $this->get_default_options() );
    }

    public function is_available() {

    	if ($this->get_enabled()=="no") return false;

		if (isset(jigoshop_cart::$cart_contents_total_ex_dl) && isset($this->min_amount) && $this->min_amount && apply_filters( 'jigoshop_shipping_min_amount', $this->min_amount, $this) > jigoshop_cart::$cart_contents_total_ex_dl) return false;

		if (is_array($this->get_ship_to_countries())) :
			if (!in_array(jigoshop_customer::get_shipping_country(), $this->get_ship_to_countries())) :
                $this->set_error_message('Sorry, it seems that there are no available shipping methods to your location. Please contact us if you require assistance or wish to make alternate arrangements.');
                return false;
            endif;
		endif;

		return !$this->has_error;

    }

    protected function get_ship_to_countries() {
		$ship_to_countries = '';

		if ($this->availability == 'specific') :
			$ship_to_countries = $this->countries;
		else :
			if (Jigoshop_Base::get_options()->get_option('jigoshop_allowed_countries')=='specific') :
				$ship_to_countries = Jigoshop_Base::get_options()->get_option('jigoshop_specific_allowed_countries');
			endif;
		endif;

        return $ship_to_countries;
    }

    public function get_error_message() {
    	return $this->error_message;
    }

    public function set_error_message($error_message = null) {
    	$this->error_message = $error_message;
    }

    public function get_enabled() {
        return $this->enabled;
    }

    /**
     * sets the tax class to shipping_method. Needed to maintain current tax
     * state from the shopping cart.
     *
     * @param type $tax jigoshop_tax instance
     */
    public function set_tax($tax) {
        $this->tax = $tax;
    }

    protected function get_tax() {
        return $this->tax;
    }

    public function get_fee( $fee, $total ) {
		if (strstr($fee, '%')) :
			return ($total/100) * str_replace('%', '', $fee);
		else :
			return $fee;
		endif;
	}

    // do not call this method from shipping plugins. Jigoshop core handles this
	public function is_chosen() {
    	if ($this->chosen) return true;
    	return false;
    }

    // do not call this method from shipping plugins. Jigoshop core handles this
    public function choose() {
    	$this->chosen = true;
    	jigoshop_session::instance()->chosen_shipping_method_id = $this->id;
    }
    
    // leave as a protected function so that services that override the functions that call this one can still call this function
    protected function get_selected_rate($rate_index) {

        return (empty($this->rates) ? NULL : $this->rates[$rate_index]);
    }

    /**
     * gets the cheapest rate from the rates returned by shipping service. If an error occurred on
     * on the shipping service service, NULL will be returned
     */
    protected function get_cheapest_rate() {

        $cheapest_rate = null;
        if ($this->rates != null) :
            for ($i = 0; $i < count($this->rates); $i++) :
                if (!isset($cheapest_rate) || $this->rates[$i]['price'] < $cheapest_rate['price']) :
                    $cheapest_rate = $this->rates[$i];
                endif;
            endfor;
        endif;

        return $cheapest_rate;
    }
    
    protected function add_rate($price, $service_name) {
        
        $price += (empty($this->fee) ? 0 : $this->get_fee($this->fee, jigoshop_cart::$cart_contents_total_ex_dl));

        $tax = 0;
        if (Jigoshop_Base::get_options()->get_option('jigoshop_calc_taxes') == 'yes' && $this->tax_status == 'taxable' && $price > 0) {
            $tax = $this->calculate_shipping_tax($price - jigoshop_cart::get_cart_discount_leftover());
        }
        
        // changed for 1.4.5 since there are instances where a shipping method may want to provide their own rules for
		// when shipping is free...that cannot be obtained within the free shipping method itself.
        if ($price >= 0) {
            $this->rates[] = array('service' => $service_name, 'price' => $price, 'tax' => $tax);
        }
        
    }
    
    protected function calculate_shipping_tax($rate) {

        $_tax = $this->get_tax();

        $tax_rate = $_tax->get_shipping_tax_rate();

        if ($tax_rate > 0) :
            return $_tax->calc_shipping_tax($rate, $tax_rate);
        endif;

        return 0;
    }

    /**
     * Set the index to the selected service on the session (selected_rate_id)
     * 
     * @param string $selected_service 
     * @since 1.2
     */
    public function set_selected_service_index($selected_service = '') {
        
        if (!empty($selected_service)) :
            for ($i = 0; $i < $this->get_rates_amount(); $i++) :
                if ($this->get_selected_service($i) == $selected_service) :
                    jigoshop_session::instance()->selected_rate_id = $i;
                    break;
                endif;
            endfor;
        endif;
        
    }
    
    // Override this functions if you want to provide your own
    // label to the service name displayed
    public function get_cheapest_service() {
        $my_cheapest_rate = $this->get_cheapest_rate();

		if ($this->title && $my_cheapest_rate['service'] != $this->title) :
			$service = $my_cheapest_rate['service'] . __( ' via ', 'jigoshop') . $this->title;
		else :
			$service = $my_cheapest_rate['service'];
		endif;
		
        return ($my_cheapest_rate == NULL ? $this->title : $service);
    }

	// call from shipping when calculating cheapest method
    public function get_cheapest_price() {
        $my_cheapest_rate = $this->get_cheapest_rate();
        return apply_filters( 'jigoshop_shipping_total_price', ($my_cheapest_rate == NULL ? $this->shipping_total : $my_cheapest_rate['price']) );
    }

    protected function get_cheapest_price_tax() {
        $my_cheapest_rate = $this->get_cheapest_rate();
        return apply_filters( 'jigoshop_shipping_tax_price', ($my_cheapest_rate == NULL ? $this->shipping_tax : $my_cheapest_rate['tax']) );
    }

    /**
     * Retrieves the service name from the rate array based on the service selected.
     * Override this method if you wish to provide your own user friendly service name
     * @return - NULL if the rate by index doesn't exist, otherwise the service name associated with the
     * service_id
     */
    public function get_selected_service($rate_index) {
        $my_rate = $this->get_selected_rate($rate_index);
		
		if ($this->title && $my_rate['service'] != $this->title) :
			$service = $my_rate['service'] . __( ' via ', 'jigoshop') . $this->title;
		else :
			$service = $my_rate['service'];
		endif;
		
        return ($my_rate == NULL ? $this->title : $service);
    }

    // if the method doesn't utilize the rates array, return the shipping total
    public function get_selected_price($rate_index) {
        $my_rate = $this->get_selected_rate($rate_index);
        return apply_filters( 'jigoshop_shipping_total_price', ($my_rate == NULL ? $this->shipping_total : $my_rate['price']) );
    }

    // if the method doesn't utilize the rates array, return what the method should return, and that is
    // the shipping tax
    public function get_selected_tax($rate_index) {
        $my_rate = $this->get_selected_rate($rate_index);
        return apply_filters( 'jigoshop_shipping_tax_price', ($my_rate == NULL ? $this->shipping_tax : $my_rate['tax']) );
    }
    
    // essentially if the method doesn't use the array, there is only 1 rate to return. Therefore if rates == null,
    // return a 1 from this function
    public function get_rates_amount() {
        return ($this->rates == NULL ? 1 : count($this->rates));
    }


    /**
     * If a shop sells mixed products and non-shippable products are all added to 
     * the cart, then the calculable service can call this method in that scenario
     * and it will create a free shipping charge.
     * @since 1.2
     */
    protected function create_no_shipping_rate() {
        $this->rates[] = array('service' => 'non-shippable', 'price' => 0, 'tax' => 0);
    }    
    
    public function has_error() {
        return $this->has_error;
    }


    public function reset_method() {
    	$this->chosen = false;
    	$this->shipping_total = 0;
    	$this->shipping_tax = 0;
        $this->tax = null;
        $this->rates = array();
        $this->has_error = false;
        $this->error_message = null;
    }

    /**
     * @deprecated - use get_default_options()
     */
    public function admin_options() {}

    /**
     * @deprecated - use get_default_options()
     */
    public function process_admin_options() {}

    /**
	 * Default Option settings for WordPress Settings API using an implementation of the Jigoshop_Options_Interface
	 *
	 * These should be installed on the Jigoshop_Options 'Shipping' tab
     * @since 1.3
	 *
	 */	
    protected function get_default_options() {
        return array();
    }

}