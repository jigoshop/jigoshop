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
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
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

    private $tax;
    private $error_message = null;

    public function is_available() {

    	if ($this->get_enabled()=="no") return false;

		if (isset(jigoshop_cart::$cart_contents_total_ex_dl) && isset($this->min_amount) && $this->min_amount && $this->min_amount > jigoshop_cart::$cart_contents_total_ex_dl) return false;

		if (is_array($this->get_ship_to_countries())) :
			if (!in_array(jigoshop_customer::get_shipping_country(), $this->get_ship_to_countries())) :
                $this->set_error_message('Sorry, it seems that there are no available shipping methods to your location. Please contact us if you require assistance or wish to make alternate arrangements.');
                return false;
            endif;
		endif;

		return true;

    }

    protected function get_ship_to_countries() {
		$ship_to_countries = '';

		if ($this->availability == 'specific') :
			$ship_to_countries = $this->countries;
		else :
			if (Jigoshop_Options::get_option('jigoshop_allowed_countries')=='specific') :
				$ship_to_countries = Jigoshop_Options::get_option('jigoshop_specific_allowed_countries');
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

	public function is_chosen() {
    	if ($this->chosen) return true;
    	return false;
    }

    public function choose() {
    	$this->chosen = true;
    	jigoshop_session::instance()->chosen_shipping_method_id = $this->id;
    }

    public function reset_method() {
    	$this->chosen = false;
    	$this->shipping_total = 0;
    	$this->shipping_tax = 0;
        $this->tax = null;
    }

    public function admin_options() {}

    public function process_admin_options() {}

}