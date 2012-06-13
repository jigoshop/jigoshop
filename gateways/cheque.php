<?php
/**
 * Cheque Payment Gateway
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
class jigoshop_cheque extends jigoshop_payment_gateway {

	public function __construct() {
        $this->id				= 'cheque';
        $this->icon 			= '';
        $this->has_fields 		= false;

		$this->enabled			= get_option('jigoshop_cheque_enabled');
		$this->title 			= get_option('jigoshop_cheque_title');
		$this->description 		= get_option('jigoshop_cheque_description');

		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
		add_option('jigoshop_cheque_enabled', 'yes');
		add_option('jigoshop_cheque_title', __('Cheque Payment', 'jigoshop') );
		add_option('jigoshop_cheque_description', __('Please send your cheque to Store Name, Store Street, Store Town, Store State / County, Store Postcode.', 'jigoshop'));

    	add_action('thankyou_cheque', array(&$this, 'thankyou_page'));
    }

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 **/
	public function admin_options() {

		$options = array (

			array( 'name'        => __('Cheque Payment', 'jigoshop'), 'type' => 'title', 'desc' => __('Allows cheque payments. Allows you to make test purchases without having to use the sandbox area of a payment gateway. Quite useful for demonstrating to clients and for testing order emails and the \'success\' pages etc.', 'jigoshop') ),

			array(
				'name'           => __('Enable Cheque Payment','jigoshop'),
				'id'             => 'jigoshop_cheque_enabled',
				'type'           => 'checkbox',
				'std'            => 'no'
			),

			array(
				'name'           => __('Method Title','jigoshop'),
				'tip'            => __('This controls the title which the user sees during checkout.','jigoshop'),
				'id'             => 'jigoshop_cheque_title',
				'type'           => 'text',
				'std'            => 'Cheque Payment'
			),

			array(
				'name'           => __('Customer Message','jigoshop'),
				'id'             => 'jigoshop_cheque_description',
				'tip'            => __('Let the customer know the payee and where they should be sending the cheque to and that their order won\'t be shipping until you receive it.', 'jigoshop'),
				'type'           => 'textarea',
			),

		);

		jigoshop_admin_option_display($options);

    }

	/**
	* There are no payment fields for cheques, but we want to show the description if set.
	**/
	function payment_fields() {
		if ($this->description) echo wpautop(wptexturize($this->description));
	}

	function thankyou_page() {
		if ($this->description) echo wpautop(wptexturize($this->description));
	}

	/**
	 * Admin Panel Options Processing
	 * - Saves the options to the DB
	 **/
    public function process_admin_options() {
   		if(isset($_POST['jigoshop_cheque_enabled'])) 	update_option('jigoshop_cheque_enabled', 'yes'); else update_option('jigoshop_cheque_enabled', 'no');
   		if(isset($_POST['jigoshop_cheque_title'])) 	update_option('jigoshop_cheque_title', 	jigowatt_clean($_POST['jigoshop_cheque_title'])); else @delete_option('jigoshop_cheque_title');
   		if(isset($_POST['jigoshop_cheque_description'])) 	update_option('jigoshop_cheque_description', 	jigowatt_clean($_POST['jigoshop_cheque_description'])); else @delete_option('jigoshop_cheque_description');
    }

	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {

		$order = new jigoshop_order( $order_id );

		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status('on-hold', __('Awaiting cheque payment', 'jigoshop'));

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

/**
 * Add the gateway to JigoShop
 **/
function add_cheque_gateway( $methods ) {
	$methods[] = 'jigoshop_cheque'; return $methods;
}

add_filter('jigoshop_payment_gateways', 'add_cheque_gateway' );
