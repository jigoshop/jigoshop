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
 * @package    Jigoshop
 * @category   Checkout
 * @author     Chris Balchin / Robert Rhoades
 * @copyright  Copyright © 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

class jigoshop_bank_transfer extends jigoshop_payment_gateway {
		
	public function __construct() { 
        $this->id				= 'bank_transfer';
        $this->icon 			= '';
        $this->has_fields 		= false;
		
		$this->enabled			= get_option('jigoshop_bank_transfer_enabled');
		$this->title 			= get_option('jigoshop_bank_transfer_title');
		$this->description 		= get_option('jigoshop_bank_transfer_description');
		$this->bank_name 		= get_option('jigoshop_bank_transfer_bank_name');
		$this->acc_number 		= get_option('jigoshop_bank_transfer_acc_number');
		$this->sort_code 		= get_option('jigoshop_bank_transfer_sort_code');
		$this->iban 			= get_option('jigoshop_bank_transfer_iban');
		$this->bic 				= get_option('jigoshop_bank_transfer_bic');
		$this->additional 		= get_option('jigoshop_bank_transfer_additional');

		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
		add_option('jigoshop_bank_transfer_enabled', 'yes');
		add_option('jigoshop_bank_transfer_title', __('Bank Transfer', 'jigoshop') );
		add_option('jigoshop_bank_transfer_description', __('Please use the details below to transfer the payment for your order, once payment is received your order will be processed.', 'jigoshop'));
    
    	add_action('thankyou_bank_transfer', array(&$this, 'thankyou_page'));
    } 
    
	/**
	 * Admin Panel Options - Area to set your bank account details and additional information if necessary.
	 **/
	public function admin_options() {
    	?>
    	<thead><tr><th scope="col" width="200px"><?php _e('Bank Transfer', 'jigoshop'); ?></th><th scope="col" class="desc"><?php _e('Accept Bank Transfers as a method of payment, there is no automated process associated with this, you must manually process when you receive payment.', 'jigoshop'); ?></th></tr></thead>
    	<tr>
	        <td class="titledesc"><?php _e('Enable Bank Transfer', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_bank_transfer_enabled" id="jigoshop_bank_transfer_enabled" style="min-width:100px;">
		            <option value="yes" <?php if (get_option('jigoshop_bank_transfer_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
		            <option value="no" <?php if (get_option('jigoshop_bank_transfer_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_title" id="jigoshop_bank_transfer_title" value="<?php if ($value = get_option('jigoshop_bank_transfer_title')) echo $value; else echo 'Bank Transfer Payment'; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Let the customer know that their order won\'t be shipping until you receive payment.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Customer Message', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text wide-input" type="text" name="jigoshop_bank_transfer_description" id="jigoshop_bank_transfer_description" value="<?php if ($value = get_option('jigoshop_bank_transfer_description')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your bank name for reference. e.g. HSBC','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Bank Name', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_bank_name" id="jigoshop_bank_transfer_bank_name" value="<?php if ($value = get_option('jigoshop_bank_transfer_bank_name')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your Bank Account number.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Account Number', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_acc_number" id="jigoshop_bank_transfer_acc_number" value="<?php if ($value = get_option('jigoshop_bank_transfer_acc_number')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your branch Sort Code.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Sort Code', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_sort_code" id="jigoshop_bank_transfer_sort_code" value="<?php if ($value = get_option('jigoshop_bank_transfer_sort_code')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your IBAN number. (for International transfers)','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('IBAN', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_iban" id="jigoshop_bank_transfer_iban" value="<?php if ($value = get_option('jigoshop_bank_transfer_iban')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your Branch Identification Code. (BIC Number)','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('BIC Code', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input class="input-text" type="text" name="jigoshop_bank_transfer_bic" id="jigoshop_bank_transfer_bic" value="<?php if ($value = get_option('jigoshop_bank_transfer_bic')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Additional information you want to display to your customer.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Additional Info', 'jigoshop') ?>:</td>
	        <td class="forminp">
	        	<textarea class="input-text" name="jigoshop_bank_transfer_additional" id="jigoshop_bank_transfer_additional"><?php if ($value = get_option('jigoshop_bank_transfer_additional')) echo $value; ?></textarea>
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
	 **/
    public function process_admin_options() {
    
    	(isset($_POST['jigoshop_bank_transfer_enabled'])) ? update_option('jigoshop_bank_transfer_enabled', jigowatt_clean($_POST['jigoshop_bank_transfer_enabled'])) : @delete_option('jigoshop_bank_transfer_enabled');
    	
    	(isset($_POST['jigoshop_bank_transfer_title'])) ? update_option('jigoshop_bank_transfer_title', jigowatt_clean($_POST['jigoshop_bank_transfer_title'])) : @delete_option('jigoshop_bank_transfer_title');
    	
    	(isset($_POST['jigoshop_bank_transfer_description'])) ? update_option('jigoshop_bank_transfer_description', jigowatt_clean($_POST['jigoshop_bank_transfer_description'])) : @delete_option('jigoshop_bank_transfer_description');
    	
    	(isset($_POST['jigoshop_bank_transfer_bank_name'])) ? update_option('jigoshop_bank_transfer_bank_name', jigowatt_clean($_POST['jigoshop_bank_transfer_bank_name'])) : @delete_option('jigoshop_bank_transfer_bank_name');
    	
    	(isset($_POST['jigoshop_bank_transfer_acc_number'])) ? update_option('jigoshop_bank_transfer_acc_number', jigowatt_clean($_POST['jigoshop_bank_transfer_acc_number'])) : @delete_option('jigoshop_bank_transfer_acc_number');
    	
    	(isset($_POST['jigoshop_bank_transfer_sort_code'])) ? update_option('jigoshop_bank_transfer_sort_code', jigowatt_clean($_POST['jigoshop_bank_transfer_sort_code'])) : @delete_option('jigoshop_bank_transfer_sort_code');
    	
    	(isset($_POST['jigoshop_bank_transfer_iban'])) ? update_option('jigoshop_bank_transfer_iban', jigowatt_clean($_POST['jigoshop_bank_transfer_iban'])) : @delete_option('jigoshop_bank_transfer_iban');
    	
    	(isset($_POST['jigoshop_bank_transfer_bic'])) ? update_option('jigoshop_bank_transfer_bic', jigowatt_clean($_POST['jigoshop_bank_transfer_bic'])) : @delete_option('jigoshop_bank_transfer_bic');
    	
    	(isset($_POST['jigoshop_bank_transfer_additional'])) ? update_option('jigoshop_bank_transfer_additional', jigowatt_clean($_POST['jigoshop_bank_transfer_additional'])) : @delete_option('jigoshop_bank_transfer_additional');
    	
    }
	
	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {
		
		$order = &new jigoshop_order( $order_id );
		$order->update_status('on-hold', __('Awaiting Bank Transfer', 'jigoshop'));
		jigoshop_cart::empty_cart();
		$checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', get_option( 'jigoshop_thanks_page_id' ) );
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( $checkout_redirect ) ) )
		);
		
	}
	
}

/**
 * Add the gateway to Jigoshop
 **/
function add_bank_transfer_gateway( $methods ) {
	$methods[] = 'jigoshop_bank_transfer'; return $methods;
}

add_filter('jigoshop_payment_gateways', 'add_bank_transfer_gateway' );