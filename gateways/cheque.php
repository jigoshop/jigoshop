<?php
/**
 * Cheque Payment Gateway (BETA)
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package		Jigoshop
 * @category	Checkout
 * @author		Andrew Benbow
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */

/**
 * Add the gateway to JigoShop
 **/
function add_cheque_gateway( $methods ) {
	$methods[] = 'jigoshop_cheque';
	return $methods;
}
add_filter( 'jigoshop_payment_gateways', 'add_cheque_gateway' );


class jigoshop_cheque extends jigoshop_payment_gateway {

	public function __construct() {
	
		$jsOptions = Jigoshop_Options::instance();
		
//		$jsOptions->install_new_options( 'Payment Gateways', $this->get_default_options() );
		
        $this->id				= 'cheque';
        $this->icon 			= '';
        $this->has_fields 		= false;

		$this->enabled			= $jsOptions->get_option('jigoshop_cheque_enabled');
		$this->title 			= $jsOptions->get_option('jigoshop_cheque_title');
		$this->description 		= $jsOptions->get_option('jigoshop_cheque_description');

		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
    	add_action( 'thankyou_cheque', array( &$this, 'thankyou_page' ) );
    	
    }

	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *
	 * These should be installed on the Jigoshop_Options 'Shipping' tab
	 *
	 */	
	public function get_default_options() {
	
		$defaults = array();
		
		// Define the Section name for the Jigoshop_Options
		$defaults[] = array( 'name' => __('Cheque Payment', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Cheque Payment','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_cheque_enabled',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		$defaults[] = array(
			'name'		=> __('Method Title','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the title which the user sees during checkout.','jigoshop'),
			'id' 		=> 'jigoshop_cheque_title',
			'std' 		=> __('Cheque Payment','jigoshop'),
			'type' 		=> 'text'
		);
		
		$defaults[] = array(
			'name'		=> __('Customer Message','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Let the customer know the payee and where they should be sending the cheque too and that their order won\'t be shipping until you receive it.','jigoshop'),
			'id' 		=> 'jigoshop_cheque_description',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		return $defaults;
	}
	
	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 **/
	public function admin_options() {
		$jsOptions = Jigoshop_Options::instance();
    	?>
    	<thead><tr><th scope="col" width="200px"><?php _e('Cheque Payment', 'jigoshop'); ?></th><th scope="col" class="desc"><?php _e('Allows cheque payments. Allows you to make test purchases without having to use the sandbox area of a payment gateway. Quite useful for demonstrating to clients and for testing order emails and the \'success\' pages etc.', 'jigoshop'); ?></th></tr></thead>
    	<tr>
	        <td class="titledesc"><?php _e('Enable Cheque Payment', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_cheque_enabled" id="jigoshop_cheque_enabled" style="min-width:100px;">
		            <option value="yes" <?php if ($jsOptions->get_option('jigoshop_cheque_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
		            <option value="no" <?php if ($jsOptions->get_option('jigoshop_cheque_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_cheque_title" id="jigoshop_cheque_title" value="<?php if ($value = $jsOptions->get_option('jigoshop_cheque_title')) echo $value; else echo 'Cheque Payment'; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Let the customer know the payee and where they should be sending the cheque too and that their order won\'t be shipping until you receive it.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Customer Message', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text wide-input" type="text" name="jigoshop_cheque_description" id="jigoshop_cheque_description" value="<?php if ($value = $jsOptions->get_option('jigoshop_cheque_description')) echo $value; ?>" />
	        </td>
	    </tr>

    	<?php
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
   		if(isset($_POST['jigoshop_cheque_enabled'])) Jigoshop_Options::instance()->set_option('jigoshop_cheque_enabled', 	jigowatt_clean($_POST['jigoshop_cheque_enabled']));
   		if(isset($_POST['jigoshop_cheque_title'])) Jigoshop_Options::instance()->set_option('jigoshop_cheque_title', 	jigowatt_clean($_POST['jigoshop_cheque_title']));
   		if(isset($_POST['jigoshop_cheque_description'])) Jigoshop_Options::instance()->set_option('jigoshop_cheque_description', 	jigowatt_clean($_POST['jigoshop_cheque_description']));
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
