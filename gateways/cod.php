<?php
/**
 * Cash on delivery Payment Gateway
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

/**
 * Add the gateway to JigoShop
 **/
function add_cod_gateway( $methods ) {
	$methods[] = 'jigoshop_cod';
	return $methods;
}
add_filter( 'jigoshop_payment_gateways', 'add_cod_gateway', 30 );


class jigoshop_cod extends jigoshop_payment_gateway {

	public function __construct() {
		
        parent::__construct();
		
        $this->id				= 'cod';
        $this->icon 			= '';
        $this->has_fields 		= false;

		$this->enabled			= Jigoshop_Base::get_options()->get_option('jigoshop_cod_enabled');
		$this->title 			= Jigoshop_Base::get_options()->get_option('jigoshop_cod_title');
		$this->description 		= Jigoshop_Base::get_options()->get_option('jigoshop_cod_description');

    	add_action('thankyou_cod', array(&$this, 'thankyou_page'));
    }


	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *
	 * These will be installed on the Jigoshop_Options 'Payment Gateways' tab by the parent class 'jigoshop_payment_gateway'
	 *
	 */	
	protected function get_default_options() {
	
		$defaults = array();
		
		// Define the Section name for the Jigoshop_Options
		$defaults[] = array( 'name' => __('Cash on Delivery', 'jigoshop'), 'type' => 'title', 'desc' => __('Allows cash payments. Good for offline stores or having customers pay at the time of receiving the product.', 'jigoshop') );
		
		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Cash on Delivery','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_cod_enabled',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		$defaults[] = array(
			'name'		=> __('Method Title','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the title which the user sees during checkout.','jigoshop'),
			'id' 		=> 'jigoshop_cod_title',
			'std' 		=> __('Cash on Delivery','jigoshop'),
			'type' 		=> 'text'
		);
		
		$defaults[] = array(
			'name'		=> __('Customer Message','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Let the customer know the payee and where they should be sending the cod too and that their order won\'t be shipping until you receive it.','jigoshop'),
			'id' 		=> 'jigoshop_cod_description',
			'std' 		=> __('Please pay to Store Name, Store Street, Store Town, Store State / County, Store Postcode.', 'jigoshop'),
			'type' 		=> 'longtext'
		);

		return $defaults;
	}


	/**
	* There are no payment fields for cods, but we want to show the description if set.
	**/
	function payment_fields() {
		if ($this->description) echo wpautop(wptexturize($this->description));
	}

	function thankyou_page() {
		if ($this->description) echo wpautop(wptexturize($this->description));
	}

	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {

		$order = new jigoshop_order( $order_id );

		// Mark as on-hold (we're awaiting the cod)
		$order->update_status('on-hold', __('Waiting for cash delivery', 'jigoshop'));

		// Remove cart
		jigoshop_cart::empty_cart();

		// Return thankyou redirect
		$checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks') );
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink( $checkout_redirect )))
		);

	}

}
