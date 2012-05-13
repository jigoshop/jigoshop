<?php
/**
 * Skrill / Moneybookers Gateway
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

/** Add the gateway to JigoShop **/

function add_skrill_gateway( $methods ) {
	$methods[] = 'skrill';
	return $methods;
}
add_filter( 'jigoshop_payment_gateways', 'add_skrill_gateway', 40 );


class skrill extends jigoshop_payment_gateway {

	public function __construct() {
		
		Jigoshop_Options::install_external_options( __( 'Payment Gateways', 'jigoshop' ), $this->get_default_options() );

        $this->id			= 'skrill';
        $this->title 		= 'Skrill';
        $this->icon 		= jigoshop::assets_url() . '/assets/images/icons/skrill.png';
        $this->has_fields 	= false;
      	$this->enabled		= Jigoshop_Options::get_option('jigoshop_skrill_enabled');
		$this->title 		= Jigoshop_Options::get_option('jigoshop_skrill_title');
		$this->email 		= Jigoshop_Options::get_option('jigoshop_skrill_email');

		add_action( 'init', array(&$this, 'check_status_response') );

		if(isset($_GET['skrillPayment']) && $_GET['skrillPayment'] == true):
			add_action( 'init', array(&$this, 'generate_skrill_form') );
		endif;

		add_action('valid-skrill-status-report', array(&$this, 'successful_request') );
		add_action('receipt_skrill', array(&$this, 'receipt_skrill'));

    }


	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *
	 * These should be installed on the Jigoshop_Options 'Payment Gateways' tab
	 *
	 */	
	public function get_default_options() {
	
		$defaults = array();
		
		// Define the Section name for the Jigoshop_Options
		$defaults[] = array( 'name' => __('Skrill (Moneybookers)', 'jigoshop'), 'type' => 'title', 'desc' => __('Skrill works by using an iFrame to submit payment information securely to Moneybookers.', 'jigoshop') );
		
		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Skrill','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_skrill_enabled',
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
			'id' 		=> 'jigoshop_skrill_title',
			'std' 		=> __('Skrill','jigoshop'),
			'type' 		=> 'text'
		);
		
		$defaults[] = array(
			'name'		=> __('Skrill merchant e-mail','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Please enter your skrill email address; this is needed in order to take payment!','jigoshop'),
			'id' 		=> 'jigoshop_skrill_email',
			'std' 		=> '',
			'type' 		=> 'email'
		);

		$defaults[] = array(
			'name'		=> __('Skrill Secret Word','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Please enter your skrill secretword; this is needed in order to take payment!','jigoshop'),
			'id' 		=> 'jigoshop_skrill_secret_word',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Skrill Customer ID','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Please enter your skrill Customer ID; this is needed in order to take payment!','jigoshop'),
			'id' 		=> 'jigoshop_skrill_customer_id',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		return $defaults;
	}


	/**
	 * Generate the skrill button link
	 **/
    public function generate_skrill_form() {
		
		
    	$order_id = $_GET['orderId'];

		$order = new jigoshop_order( $order_id );

		$skrill_adr = 'https://www.moneybookers.com/app/payment.pl';

		$shipping_name = explode(' ', $order->shipping_method);

		$order_total = trim($order->order_total, 0);

		if( substr($order_total, -1) == '.' ) $order_total = str_replace('.', '', $order_total);

		// filter redirect page
		$checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks') );

		$skrill_args = array(
			'merchant_fields'      => 'partner',
			'partner'              => '21890813',
			'pay_to_email'         => $this->email,
			'recipient_description'=> get_bloginfo('name'),
			'transaction_id'       => $order_id,
			'return_url'           => get_permalink( $checkout_redirect ),
			'return_url_text'      => 'Return to Merchant',
			'new_window_redirect'  => 0,
			'rid'                  => 20521479,
			'prepare_only'         => 0,
			'return_url_target'    => 1,
			'cancel_url'           => trailingslashit(get_bloginfo('wpurl')).'?skrillListener=skrill_cancel',
			'cancel_url_target'    => 1,
			'status_url'           => trailingslashit(get_bloginfo('wpurl')).'?skrillListener=skrill_status',
			'dynamic_descriptor'   => 'Description',
			'language'             => 'EN',
			'hide_login'           => 1,
			'confirmation_note'    => 'Thank you for your custom',
			'pay_from_email'       => $order->billing_email,

			//'title'              => 'Mr',
			'firstname'            => $order->billing_first_name,
			'lastname'             => $order->billing_last_name,
			'address'              => $order->billing_address_1,
			'address2'             => $order->billing_address_2,
			'phone_number'         => $order->billing_phone,
			'postal_code'          => $order->billing_postcode,
			'city'                 => $order->billing_city,
			'state'                => $order->billing_state,
			'country'              => 'GBR',

			'amount'               => $order_total,
			'currency'             => get_option('jigoshop_currency'),
			'detail1_description'  => 'Order ID',
			'detail1_text'         => $order_id

		);

		// Cart Contents
		$item_loop = 0;
		if (sizeof($order->items)>0) : foreach ($order->items as $item) :

            if(!empty($item['variation_id'])) {
                $_product = new jigoshop_product_variation($item['variation_id']);
            } else {
                $_product = new jigoshop_product($item['id']);
            }

			if ($_product->exists() && $item['qty']) :

				$item_loop++;

				$skrill_args['item_name_'.$item_loop]= $_product->get_title();
				$skrill_args['quantity_'.$item_loop] = $item['qty'];
				$skrill_args['amount_'.$item_loop]   = $_product->get_price_excluding_tax();

			endif;
		endforeach; endif;

		// Shipping Cost
		$item_loop++;
		$skrill_args['item_name_'.$item_loop]= __('Shipping cost', 'jigoshop');
		$skrill_args['quantity_'.$item_loop] = '1';
		$skrill_args['amount_'.$item_loop]   = number_format($order->order_shipping, 2);

		$skrill_args_array = array();

		foreach ($skrill_args as $key => $value) {
			$skrill_args_array[] = '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'" />';
		}

		// Skirll MD5 concatenation

		$skrill_md = Jigoshop_Options::get_option('jigoshop_skrill_customer_id') . $skrill_args['transaction_id'] . strtoupper(md5(Jigoshop_Options::get_option('jigoshop_skrill_secret_word'))) . $order_total . Jigoshop_Options::get_option('jigoshop_currency') . '2';
		$skrill_md = md5($skrill_md);

		add_post_meta($order_id, '_skrillmd', $skrill_md);

		echo '<form name="moneybookers" id="moneybookers_place_form" action="'.$skrill_adr.'" method="POST">' . implode('', $skrill_args_array) . '</form>';

		echo '<script type="text/javascript">
		//<![CDATA[
    	var paymentform = document.getElementById(\'moneybookers_place_form\');
   		window.onload = paymentform.submit();
		//]]>
		</script>';

		exit();

	}

	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {

		$order = new jigoshop_order( $order_id );

		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(jigoshop_get_page_id('pay'))))
		);

	}

	/**
	 * receipt_page
	 **/
	function receipt_skrill( $order ) {

		echo '<p>'.__('Thank you for your order, please complete the secure (SSL) form below to pay with Skrill.', 'jigoshop').'</p>';

		echo '<iframe class="skrill-loader" width="100%" height="700"  id="2" src ="'.home_url('?skrillPayment=1&orderId='.$order).'">';
		echo '<p>Your browser does not support iFrames, please contact us to place an order.</p>';
		echo '</iframe>';

	}

	/**
	 * Check Skrill status report validity
	 **/
	function check_status_report_is_valid() {

		// Get Skrill post data array
        $params = $_POST;

        if(!isset($params['transaction_id'])) return false;

        $order_id = $params['transaction_id'];
        $_skrillmd = strtoupper(get_post_meta($order_id, '_skrillmd', true));

        // Check MD5 signiture
        if($params['md5sig'] == $_skrillmd) return true;

		return false;

    }

	/**
	 * Check for Skrill Status Response
	 **/
	function check_status_response() {

		if (isset($_GET['skrillListener']) && $_GET['skrillListener'] == 'skrill_status'):

        	$_POST = stripslashes_deep($_POST);

        	if (self::check_status_report_is_valid()) :

            	do_action("valid-skrill-status-report", $_POST);

       		endif;

       	endif;

	}

	/**
	 * Successful Payment!
	 **/
	function successful_request( $posted ) {

		// Custom holds post ID
	    if ( !empty($posted['mb_transaction_id']) ) {

			$order = new jigoshop_order( (int) $posted['transaction_id'] );

			if ($order->status !== 'completed') :
		        // We are here so lets check status and do actions
		        switch ($posted['status']) :
		            case '2' : // Processed
		                $order->add_order_note( __('Skrill payment completed', 'jigoshop') );
		                $order->payment_complete();
		            break;
		            case '0' : // Pending
		            case '-2' : // Failed
		                $order->update_status('on-hold', sprintf(__('Skrill payment failed (%s)', 'jigoshop'), strtolower($posted['status']) ) );
		            break;
		            case '-1' : // Cancelled
		            	$order->update_status('cancelled', __('Skrill payment cancelled', 'jigoshop'));
		            break;
		            default:
		            	$order->update_status('cancelled', __('Skrill exception', 'jigoshop'));
		            break;
		        endswitch;
			endif;

			exit;

	    }

	}

}
