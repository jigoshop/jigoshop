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
 * @package		Jigoshop
 * @category	Checkout
 * @author		Andrew Benbow
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
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
		
		
// 		Jigoshop_Options::add_option('jigoshop_cod_enabled', 'yes');
// 		Jigoshop_Options::add_option('jigoshop_cod_title', __('Cash on Delivery', 'jigoshop') );
// 		Jigoshop_Options::add_option('jigoshop_cod_description', __('Please pay to Store Name, Store Street, Store Town, Store State / County, Store Postcode.', 'jigoshop'));
		
		// NOTE: The above add_options are used for now.  When the gateway is converted to using Jigoshop_Options class
		// sometime post Jigoshop 1.2, they won't be needed and only the following commented out line will be used
		
		Jigoshop_Options::install_external_options( __( 'Payment Gateways', 'jigoshop' ), $this->get_default_options() );
		
        $this->id				= 'cod';
        $this->icon 			= '';
        $this->has_fields 		= false;

		$this->enabled			= Jigoshop_Options::get_option('jigoshop_cod_enabled');
		$this->title 			= Jigoshop_Options::get_option('jigoshop_cod_title');
		$this->description 		= Jigoshop_Options::get_option('jigoshop_cod_description');

		// remove this hook 'jigoshop_update_options' for post Jigoshop 1.2 use
//		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
    	add_action('thankyou_cod', array(&$this, 'thankyou_page'));
    }


	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *
	 * These should be installed on the Jigoshop_Options 'Payment Gateways' tab
	 *
	 * NOTE: these are currently not used in Jigoshop 1.2 or less.  They will be implemented when all Gateways are
	 * converted for full Jigoshop_Options use post Jigoshop 1.2.
	 *
	 */	
	public function get_default_options() {
	
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
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * NOTE: this will be deprecated post Jigoshop 1.2 and no longer required for Admin options display
	 *
	 **/
	public function admin_options() {
    	?>
    	<thead><tr><th scope="col" width="200px"><?php _e('Cash on Delivery', 'jigoshop'); ?></th><th scope="col" class="desc"><?php _e('Allows cash payments. Good for offline stores or having customers pay at the time of receiving the product.', 'jigoshop'); ?></th></tr></thead>
    	<tr>
	        <td class="titledesc"><?php _e('Enable Cash on Delivery', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_cod_enabled" id="jigoshop_cod_enabled" style="min-width:100px;">
		            <option value="yes" <?php if (Jigoshop_Options::get_option('jigoshop_cod_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
		            <option value="no" <?php if (Jigoshop_Options::get_option('jigoshop_cod_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_cod_title" id="jigoshop_cod_title" value="<?php if ($value = Jigoshop_Options::get_option('jigoshop_cod_title')) echo $value; else echo 'Cash on Delivery'; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Let the customer know the payee and where they should be sending the cod too and that their order won\'t be shipping until you receive it.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Customer Message', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text wide-input" type="text" name="jigoshop_cod_description" id="jigoshop_cod_description" value="<?php if ($value = Jigoshop_Options::get_option('jigoshop_cod_description')) echo $value; ?>" />
	        </td>
	    </tr>

    	<?php
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
	 * Admin Panel Options Processing
	 * - Saves the options to the DB
	 *
	 * NOTE: this will be deprecated post Jigoshop 1.2 and no longer required for Admin options saving
	 *
	 **/
    public function process_admin_options() {
   		if(isset($_POST['jigoshop_cod_enabled']))
   			Jigoshop_Options::set_option('jigoshop_cod_enabled', jigowatt_clean($_POST['jigoshop_cod_enabled']));
   		if(isset($_POST['jigoshop_cod_title']))
   			Jigoshop_Options::set_option('jigoshop_cod_title', jigowatt_clean($_POST['jigoshop_cod_title']));
   		if(isset($_POST['jigoshop_cod_description']))
   			Jigoshop_Options::set_option('jigoshop_cod_description', jigowatt_clean($_POST['jigoshop_cod_description']));
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
