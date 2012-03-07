<?php
/**
 * Bank Transfer Payment Gateway
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package		Jigoshop
 * @category	Checkout
 * @author		Chris Balchin / Robert Rhoades
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */

/**
 * Add the gateway to Jigoshop
 **/
function add_bank_transfer_gateway( $methods ) {
	$methods[] = 'jigoshop_bank_transfer';
	return $methods;
}
add_filter( 'jigoshop_payment_gateways', 'add_bank_transfer_gateway', 20 );


class jigoshop_bank_transfer extends jigoshop_payment_gateway {

	public function __construct() {
	
		Jigoshop_Options::install_new_options( __( 'Payment Gateways', 'jigoshop' ), $this->get_default_options() );
		
        $this->id				= 'bank_transfer';
        $this->icon 			= '';
        $this->has_fields 		= false;
		$this->enabled			= Jigoshop_Options::get_option('jigoshop_bank_transfer_enabled');
		$this->title 			= Jigoshop_Options::get_option('jigoshop_bank_transfer_title');
		$this->description 		= Jigoshop_Options::get_option('jigoshop_bank_transfer_description');
		$this->bank_name 		= Jigoshop_Options::get_option('jigoshop_bank_transfer_bank_name');
		$this->acc_number 		= Jigoshop_Options::get_option('jigoshop_bank_transfer_acc_number');
		$this->sort_code 		= Jigoshop_Options::get_option('jigoshop_bank_transfer_sort_code');
		$this->iban 			= Jigoshop_Options::get_option('jigoshop_bank_transfer_iban');
		$this->bic 				= Jigoshop_Options::get_option('jigoshop_bank_transfer_bic');
		$this->additional 		= Jigoshop_Options::get_option('jigoshop_bank_transfer_additional');

		// remove this hook 'jigoshop_update_options' for post Jigoshop 1.2 use
//		add_action( 'jigoshop_update_options', array(&$this, 'process_admin_options') );
    	add_action( 'thankyou_bank_transfer', array(&$this, 'thankyou_page') );
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
		$defaults[] = array( 'name' => __('Bank Transfer', 'jigoshop'), 'type' => 'title', 'desc' => __('<p>Accept Bank Transfers as a method of payment. There is no automated process associated with this, you must manually process an order when you receive payment.</p>', 'jigoshop') );
		
		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Bank Transfer','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_bank_transfer_enabled',
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
			'id' 		=> 'jigoshop_bank_transfer_title',
			'std' 		=> __('Bank Transfer Payment','jigoshop'),
			'type' 		=> 'text'
		);
		
		$defaults[] = array(
			'name'		=> __('Customer Message','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Let the customer know that their order won\'t be shipping until you receive payment.','jigoshop'),
			'id' 		=> 'jigoshop_bank_transfer_description',
			'std' 		=> __('Please use the details below to transfer the payment for your order, once payment is received your order will be processed.','jigoshop'),
			'type' 		=> 'longtext'
		);

		$defaults[] = array(
			'name'		=> __('Bank Name','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Your bank name for reference. e.g. HSBC','jigoshop'),
			'id' 		=> 'jigoshop_bank_transfer_bank_name',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Account Number','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Your Bank Account number.','jigoshop'),
			'id' 		=> 'jigoshop_bank_transfer_acc_number',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Sort Code','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Your branch Sort Code.','jigoshop'),
			'id' 		=> 'jigoshop_bank_transfer_sort_code',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('IBAN','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Your IBAN number. (for International transfers)','jigoshop'),
			'id' 		=> 'jigoshop_bank_transfer_iban',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('BIC Code','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Your Branch Identification Code. (BIC Number)','jigoshop'),
			'id' 		=> 'jigoshop_bank_transfer_bic',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Additional Info','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Additional information you want to display to your customer.','jigoshop'),
			'id' 		=> 'jigoshop_bank_transfer_additional',
			'std' 		=> '',
			'type' 		=> 'longtext'
		);

		return $defaults;
	}


	/**
	 * Admin Panel Options - Area to set your bank account details and additional information if necessary.
	 *
	 * NOTE: this will be deprecated post Jigoshop 1.2 and no longer required for Admin options display
	 *
	 **/
	public function admin_options() {
    	?>
    	<thead><tr><th scope="col" width="200px"><?php _e('Bank Transfer', 'jigoshop'); ?></th><th scope="col" class="desc"><?php _e('Accept Bank Transfers as a method of payment. There is no automated process associated with this, you must manually process an order when you receive payment.', 'jigoshop'); ?></th></tr></thead>
    	<tr>
	        <td class="titledesc"><?php _e('Enable Bank Transfer', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_bank_transfer_enabled" id="jigoshop_bank_transfer_enabled" style="min-width:100px;">
		            <option value="yes" <?php if (Jigoshop_Options::get_option('jigoshop_bank_transfer_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
		            <option value="no" <?php if (Jigoshop_Options::get_option('jigoshop_bank_transfer_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_title" id="jigoshop_bank_transfer_title" value="<?php if ($value = Jigoshop_Options::get_option('jigoshop_bank_transfer_title')) echo $value; else echo 'Bank Transfer Payment'; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Let the customer know that their order won\'t be shipping until you receive payment.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Customer Message', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text wide-input" type="text" name="jigoshop_bank_transfer_description" id="jigoshop_bank_transfer_description" value="<?php if ($value = Jigoshop_Options::get_option('jigoshop_bank_transfer_description')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your bank name for reference. e.g. HSBC','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Bank Name', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_bank_name" id="jigoshop_bank_transfer_bank_name" value="<?php if ($value = Jigoshop_Options::get_option('jigoshop_bank_transfer_bank_name')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your Bank Account number.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Account Number', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_acc_number" id="jigoshop_bank_transfer_acc_number" value="<?php if ($value = Jigoshop_Options::get_option('jigoshop_bank_transfer_acc_number')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your branch Sort Code.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Sort Code', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_sort_code" id="jigoshop_bank_transfer_sort_code" value="<?php if ($value = Jigoshop_Options::get_option('jigoshop_bank_transfer_sort_code')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your IBAN number. (for International transfers)','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('IBAN', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_iban" id="jigoshop_bank_transfer_iban" value="<?php if ($value = Jigoshop_Options::get_option('jigoshop_bank_transfer_iban')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your Branch Identification Code. (BIC Number)','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('BIC Code', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_bic" id="jigoshop_bank_transfer_bic" value="<?php if ($value = Jigoshop_Options::get_option('jigoshop_bank_transfer_bic')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Additional information you want to display to your customer.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Additional Info', 'jigoshop') ?>:</td>
	        <td class="forminp">
	        	<textarea class="input-text" name="jigoshop_bank_transfer_additional" id="jigoshop_bank_transfer_additional"><?php if ($value = Jigoshop_Options::get_option('jigoshop_bank_transfer_additional')) echo esc_textarea( $value ); ?></textarea>
	        </td>
	    </tr>
    	<?php
    }

	/**
	* There are no payment fields for Bank Transfers, we need to show bank details instead.
	**/
	function payment_fields() {
		$bank_info = null;
		if ($this->bank_name) $bank_info .= '<strong>'.__('Bank Name', 'jigoshop').'</strong>: ' . wptexturize($this->bank_name) . '<br />';
		if ($this->acc_number) $bank_info .= '<strong>'.__('Account Number', 'jigoshop').'</strong>: '.wptexturize($this->acc_number) . '<br />';
		if ($this->sort_code) $bank_info .= '<strong>'.__('Sort Code', 'jigoshop').'</strong>: '. wptexturize($this->sort_code) . '<br />';
		if ($this->iban) $bank_info .= '<strong>'.__('IBAN', 'jigoshop').'</strong>: '.wptexturize($this->iban) . '<br />';
		if ($this->bic) $bank_info .= '<strong>'.__('BIC', 'jigoshop').'</strong>: '.wptexturize($this->bic) . '<br />';
		if ($this->description) echo wpautop(wptexturize($this->description));
		if (!empty($bank_info)) echo wpautop($bank_info);
		if ($this->additional) echo wpautop('<strong>'.__('Additional Information', 'jigoshop').'</strong>:');
		if ($this->additional) echo wpautop(wptexturize($this->additional));
	}

	function thankyou_page() {
		$bank_info = null;
		if ($this->bank_name) $bank_info .= '<strong>'.__('Bank Name', 'jigoshop').'</strong>: ' . wptexturize($this->bank_name) . '<br />';
		if ($this->acc_number) $bank_info .= '<strong>'.__('Account Number', 'jigoshop').'</strong>: '.wptexturize($this->acc_number) . '<br />';
		if ($this->sort_code) $bank_info .= '<strong>'.__('Sort Code', 'jigoshop').'</strong>: '. wptexturize($this->sort_code) . '<br />';
		if ($this->iban) $bank_info .= '<strong>'.__('IBAN', 'jigoshop').'</strong>: '.wptexturize($this->iban) . '<br />';
		if ($this->bic) $bank_info .= '<strong>'.__('BIC', 'jigoshop').'</strong>: '.wptexturize($this->bic) . '<br />';

		if ($this->description) echo wpautop(wptexturize($this->description));
		if ($bank_info) echo wpautop($bank_info);
		if ($this->additional) echo wpautop('<strong>'.__('Additional Information', 'jigoshop').'</strong>:');
		if ($this->additional) echo wpautop(wptexturize($this->additional));
	}

	/**
	 * Admin Panel Options Processing - save options to the database.
	 *
	 * NOTE: this will be deprecated post Jigoshop 1.2 and no longer required for Admin options saving
	 *
	 **/
    public function process_admin_options() {

    	if (isset($_POST['jigoshop_bank_transfer_enabled'])) Jigoshop_Options::set_option('jigoshop_bank_transfer_enabled', jigowatt_clean($_POST['jigoshop_bank_transfer_enabled']));

    	if (isset($_POST['jigoshop_bank_transfer_title'])) Jigoshop_Options::set_option('jigoshop_bank_transfer_title', jigowatt_clean($_POST['jigoshop_bank_transfer_title']));

    	if (isset($_POST['jigoshop_bank_transfer_description'])) Jigoshop_Options::set_option('jigoshop_bank_transfer_description', jigowatt_clean($_POST['jigoshop_bank_transfer_description']));

    	if (isset($_POST['jigoshop_bank_transfer_bank_name'])) Jigoshop_Options::set_option('jigoshop_bank_transfer_bank_name', jigowatt_clean($_POST['jigoshop_bank_transfer_bank_name']));

    	if (isset($_POST['jigoshop_bank_transfer_acc_number'])) Jigoshop_Options::set_option('jigoshop_bank_transfer_acc_number', jigowatt_clean($_POST['jigoshop_bank_transfer_acc_number']));

    	if (isset($_POST['jigoshop_bank_transfer_sort_code'])) Jigoshop_Options::set_option('jigoshop_bank_transfer_sort_code', jigowatt_clean($_POST['jigoshop_bank_transfer_sort_code']));

    	if (isset($_POST['jigoshop_bank_transfer_iban'])) Jigoshop_Options::set_option('jigoshop_bank_transfer_iban', jigowatt_clean($_POST['jigoshop_bank_transfer_iban']));

    	if (isset($_POST['jigoshop_bank_transfer_bic'])) Jigoshop_Options::set_option('jigoshop_bank_transfer_bic', jigowatt_clean($_POST['jigoshop_bank_transfer_bic']));

    	if (isset($_POST['jigoshop_bank_transfer_additional'])) Jigoshop_Options::set_option('jigoshop_bank_transfer_additional', jigowatt_clean($_POST['jigoshop_bank_transfer_additional']));

    }

	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {

		$order = new jigoshop_order( $order_id );
		$order->update_status('on-hold', __('Awaiting Bank Transfer', 'jigoshop'));
		jigoshop_cart::empty_cart();
		$checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks') );
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( $checkout_redirect ) ) )
		);

	}

}
