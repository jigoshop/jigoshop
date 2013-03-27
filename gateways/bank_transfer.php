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
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
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
	
        parent::__construct();
		
        $this->id				= 'bank_transfer';
        $this->icon 			= '';
        $this->has_fields 		= false;
		$this->enabled			= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_enabled');
		$this->title 			= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_title');
		$this->description 		= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_description');
		$this->bank_name 		= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_bank_name');
		$this->acc_number 		= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_acc_number');
		$this->sort_code 		= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_sort_code');
		$this->account_holder 	= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_account_holder');
		$this->iban 			= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_iban');
		$this->bic 				= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_bic');
		$this->additional 		= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_additional');

    	add_action( 'thankyou_bank_transfer', array(&$this, 'thankyou_page') );
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
		$defaults[] = array( 'name' => __('Bank Transfer', 'jigoshop'), 'type' => 'title', 'desc' => __('Accept Bank Transfers as a method of payment. There is no automated process associated with this, you must manually process an order when you receive payment.', 'jigoshop') );
		
		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Bank Transfer','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_bank_transfer_enabled',
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
			'name'		=> __('Account Holder','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('The account name your account is registered to.','jigoshop'),
			'id' 		=> 'jigoshop_bank_transfer_account_holder',
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
	* There are no payment fields for Bank Transfers, we need to show bank details instead.
	**/
	function payment_fields() {
		$bank_info = null;
		if ($this->bank_name) $bank_info .= '<strong>'.__('Bank Name', 'jigoshop').'</strong>: ' . wptexturize($this->bank_name) . '<br />';
		if ($this->acc_number) $bank_info .= '<strong>'.__('Account Number', 'jigoshop').'</strong>: '.wptexturize($this->acc_number) . '<br />';
		if ($this->account_holder) $bank_info .= '<strong>'.__('Account Holder', 'jigoshop').'</strong>: '. wptexturize($this->account_holder) . '<br />';
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
		if ($this->account_holder) $bank_info .= '<strong>'.__('Account Holder', 'jigoshop').'</strong>: '. wptexturize($this->account_holder) . '<br />';
		if ($this->sort_code) $bank_info .= '<strong>'.__('Sort Code', 'jigoshop').'</strong>: '. wptexturize($this->sort_code) . '<br />';
		if ($this->iban) $bank_info .= '<strong>'.__('IBAN', 'jigoshop').'</strong>: '.wptexturize($this->iban) . '<br />';
		if ($this->bic) $bank_info .= '<strong>'.__('BIC', 'jigoshop').'</strong>: '.wptexturize($this->bic) . '<br />';

		if ($this->description) echo wpautop(wptexturize($this->description));
		if ($bank_info) echo wpautop($bank_info);
		if ($this->additional) echo wpautop('<strong>'.__('Additional Information', 'jigoshop').'</strong>:');
		if ($this->additional) echo wpautop(wptexturize($this->additional));
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

	/**
	 * Format Bank information to display in emails
	 **/
	public static function get_bank_details() {

		$title 			= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_title');
		$description 	= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_description');
		$bank_name 		= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_bank_name');
		$acc_number 	= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_acc_number');
		$account_holder = Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_account_holder');
		$sort_code 		= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_sort_code');
		$iban 			= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_iban');
		$bic 			= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_bic');
		$additional 	= Jigoshop_Base::get_options()->get_option('jigoshop_bank_transfer_additional');

		$bank_info = null;
		if ($description) $bank_info .= wpautop(wptexturize($description)) . PHP_EOL;
		if ($bank_name) $bank_info .= __('Bank Name', 'jigoshop').": \t" . wptexturize($bank_name) . PHP_EOL;
		if ($acc_number) $bank_info .= __('Account Number', 'jigoshop').":\t " .wptexturize($acc_number) . PHP_EOL;
		if ($account_holder) $bank_info .= __('Account Holder', 'jigoshop').":\t " .wptexturize($account_holder) . PHP_EOL;
		if ($sort_code) $bank_info .= __('Sort Code', 'jigoshop').":\t" . wptexturize($sort_code) . PHP_EOL;
		if ($iban) $bank_info .= __('IBAN', 'jigoshop').": \t\t" .wptexturize($iban) . PHP_EOL;
		if ($bic) $bank_info .= __('BIC', 'jigoshop').": \t\t " .wptexturize($bic) . PHP_EOL;
		if ($additional) $bank_info .= wpautop(__('Additional Information', 'jigoshop').": " . PHP_EOL . wpautop(wptexturize($additional)));

		if ($bank_info)
			return wpautop($bank_info);

	}

}
