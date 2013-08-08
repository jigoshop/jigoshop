<?php
/**
 * Jigoshop Payment Gateway class
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
class jigoshop_payment_gateway {

	var $id;
	var $title;
	var $chosen;
	var $has_fields;
	var $countries;
	var $availability;
	var $enabled;
	var $icon;
	var $description;
    
	public function __construct() {
        Jigoshop_Base::get_options()->install_external_options_onto_tab( __( 'Payment Gateways', 'jigoshop' ), $this->get_default_options() );
	}
	
	function is_available() {

		if ($this->enabled=="yes") :

			return true;

		endif;

		return false;
	}

	function set_current() {
		$this->chosen = true;
	}

	function icon() {
		if ($this->icon) :
			return '<img src="'. jigoshop::force_ssl($this->icon).'" alt="'.$this->title.'" />';
		endif;
	}

	function admin_options() {}

	function process_payment( $order_id ) {}

	function validate_fields() { return true; }
    
    /**
     * provides functionality to tell checkout if 
     * the gateway should be processed or not. If false, the gateway will not be 
     * processed, otherwise the gateway will be processed.
     * @return boolean defaults to needs_payment from cart class. If overridden, the gateway will provide
     * details as to when it should or shouldn't be processed.
     * @since 1.2
     */
    public function process_gateway($subtotal, $shipping_total, $discount = 0) { 
        // default to cart needs_payment() to keep the same functionality that jigoshop offers today
        // if overridden, the gateway will provide the details when to skip or not
        return jigoshop_cart::needs_payment();
    }
    
	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *
	 * These should be installed on the Jigoshop_Options 'Payment Gateways' tab
	 *
	 */	
    protected function get_default_options() {
        return array();
    }
}