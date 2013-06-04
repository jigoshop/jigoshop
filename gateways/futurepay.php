<?php
/**
 * FuturePay Standard Gateway
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
 */
function add_futurepay_gateway( $methods ) {
	$methods[] = 'futurepay';
	return $methods;
}
add_filter( 'jigoshop_payment_gateways', 'add_futurepay_gateway', 1 );

class futurepay extends jigoshop_payment_gateway {

	private static $request_url = array(
		'yes' => 'https://demo.futurepay.com/remote/',
		'no' => 'https://api.futurepay.com/remote/'
	);

	private $available_countries = array( 'US' );
	private $current_country;
	private $accepted_currency = array( 'USD' );

	public function __construct() {

		parent::__construct();

		$this->id           = 'futurepay';
		$this->icon         = jigoshop::assets_url() . '/assets/images/icons/futurepay.png';
		$this->has_fields 	= false;
		$this->enabled      = Jigoshop_Base::get_options()->get_option('jigoshop_futurepay_enabled');
		$this->title        = Jigoshop_Base::get_options()->get_option('jigoshop_futurepay_title');
		$this->description  = Jigoshop_Base::get_options()->get_option('jigoshop_futurepay_description');
		$this->gmid         = Jigoshop_Base::get_options()->get_option('jigoshop_futurepay_gmid');
		$this->request_url  = static::$request_url[Jigoshop_Base::get_options()->get_option('jigoshop_futurepay_mode')];

		add_action( 'init', array($this, 'check_ipn_response') );
		add_action( 'valid-futurepay-ipn-request', array($this, 'successful_request'), 10, 2 );
		add_action( 'receipt_futurepay', array($this, 'receipt_page') );

		add_action( 'admin_notices', array( $this, 'futurepay_notices' ) );

		$this->current_country = (strpos(Jigoshop_Base::get_options()->get_option( 'jigoshop_default_country' ), ':' ) !== false ) 
	      ? substr(Jigoshop_Base::get_options()->get_option( 'jigoshop_default_country'),0,strpos(get_option('jigoshop_default_country' ), ':' )) 
	      : Jigoshop_Base::get_options()->get_option( 'jigoshop_default_country' );
	}


	/**
	 *  Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *
	 *  These will be installed on the Jigoshop_Options 'Payment Gateways' tab by the parent class 'jigoshop_payment_gateway'
	 */
	protected function get_default_options() {

		$defaults = array();

		// Define the Section name for the Jigoshop_Options
		$defaults[] = array( 
			'name' => sprintf(__('Future Pay %s', 'jigoshop'), '<img style="vertical-align:middle;margin-top:-4px;margin-left:10px;" src="'.jigoshop::assets_url() .'/assets/images/icons/futurepay.png" alt="FuturePay">'), 
			'type' => 'title', 
			'desc' => sprintf(__('This module allow you to accept online payments via FuturePay, allowing customers to buy now and pay later, without a credit card.  FuturePay is a safe, convenient and secure way for US customers to buy online in one-step.  %s', 'jigoshop'), '<a href="https://www.futurepay.com/main/merchant-signup?platform=77_FPM495845-1" target="_blank">Signup for a Merchant Account</a>' )
		);

		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Future Pay','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_futurepay_enabled',
			'std' 		=> 'yes',
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
			'id' 		=> 'jigoshop_futurepay_title',
			'std' 		=> __('Future Pay','jigoshop'),
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Description','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the description which the user sees during checkout.','jigoshop'),
			'id' 		=> 'jigoshop_futurepay_description',
			'std' 		=> __('Pay with Future Pay. Buy now and pay later.', 'jigoshop'),
			'type' 		=> 'longtext'
		);

		$defaults[] = array(
			'name'		=> __('Merchant API Key','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Your unique FuturePay Merchant Identifier is provided to you when you create a Merchant Account with FuturePay.','jigoshop'),
			'id' 		=> 'jigoshop_futurepay_gmid',
			'std' 		=> '',
			'type' 		=> 'longtext'
		);

		$defaults[] = array(
			'name'		=> __('Enable Sandbox','jigoshop'),
			'desc' 		=> __('Turn on to enable the FuturePay sandbox for testing.', 'jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_futurepay_mode',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'		=> __('No', 'jigoshop'),
				'yes'		=> __('Yes', 'jigoshop')
			)
		);

		return $defaults;
	}
	
	
	/**
	 *  Admin Notices for conditions under which FuturePay is available on a Shop
	 */
	public function futurepay_notices() {
	    
	    if ( $this->enabled == 'no' ) return;
		if ( ! in_array( Jigoshop_Base::get_options()->get_option( 'jigoshop_currency' ), $this->accepted_currency )) {
			echo '<div class="error"><p>'.sprintf(__('The FuturePay gateway accepts payments in currencies of %s. Your current currency is %s. FuturePay won\'t work until you change the Jigoshop currency to an accepted one.','jigoshop'), implode( ', ', $this->accepted_currency ), Jigoshop_Base::get_options()->get_option( 'jigoshop_currency' ) ).'</p></div>';
		}
		if ( ! in_array( $this->current_country, $this->available_countries )) {
			echo '<div class="error"><p>'.sprintf(__('The FuturePay gateway is available for merchants from %s. Your country is %s. FuturePay won\'t work until you change the Jigoshop Shop Base country to an accepted one.','jigoshop'), implode( ', ', $this->available_countries ), Jigoshop_Base::get_options()->get_option( 'jigoshop_default_country' )).'</p></div>';
		}
	}
	
	
	/**
	 *  Determine conditions for which FuturePay is available on a Shop
	 */
	public function is_available() {
		if ( $this->enabled != 'yes' ) {
			return false;
		}

		if ( ! in_array( Jigoshop_Base::get_options()->get_option( 'jigoshop_currency' ), $this->accepted_currency ) ) {
			return false;
		}

		if ( ! in_array( $this->current_country, $this->available_countries )) {
			return false;
		}
		
		return true;
	}
	
	
	/**
	 *  There are payment fields, but first we need to file the order before contacting futurepay
	 *  This is displayed on the Checkout for the gateway description when selected
	 */
	public function payment_fields() {
		if ($this->description) 
			echo wpautop(wptexturize($this->description));
	}

	/**
	 *  Generate the futurepay payment iframe
	 */
	protected function call_futurepay( $order_id ) {

		// Get the order
		$order = new jigoshop_order( $order_id );

		$data = array(
			'gmid' => $this->gmid,
			'reference' => $order_id . '-'.uniqid(),
			'email' => $order->billing_email,
			'first_name' => $order->billing_first_name,
			'last_name' => $order->billing_last_name,
			'company' => $order->billing_company,
			'address_line_1' => $order->billing_address_1,
			'address_line_2' => $order->billing_address_2,
			'city' => $order->billing_city,
			'state' => $order->billing_state,
			'country' => $order->billing_country,
			'zip' => $order->billing_postcode,
			'phone' => $order->billing_phone,
			'shipping_address_line_1' => $order->shipping_address_1,
			'shipping_address_line_2' => $order->shipping_address_2,
			'shipping_city' => $order->shipping_city,
			'shipping_state' => $order->shipping_state,
			'shipping_country' => $order->shipping_country,
			'shipping_zip' => $order->shipping_postcode,
			'shipping_date' => date('Y/m/d g:i:s') // Current date & time
		);

		// for Jigoshop 1.7, FuturePay doesn't allow negative prices (or 0.00 ) which affects discounts
		// with FuturePay doing the calcs, so we will bundle all products into ONE line item with
		// a quantity of ONE and send it that way using the final order total after shipping
		// and discounts are applied
		// all product titles will be comma delimited with their quantities
		$item_names = array();
		if ( sizeof( $order->items ) > 0 ) foreach ( $order->items as $item ) {
		
			$_product = $order->get_product_from_item( $item );
			$title = $_product->get_title();
			// if variation, insert variation details into product title
			if ( $_product instanceof jigoshop_product_variation ) {
				$title .= ' (' . jigoshop_get_formatted_variation( $item['variation'], true) . ')';
			}
			
			$item_names[] = $item['qty'] . ' x ' . $title;
			
		}
		// now add the one line item to the necessary product field arrays
		$data['sku'][] = "Products";
		$data['price'][] = $order->order_total; // futurepay only needs final order amount
		$data['tax_amount'][]  = 0;
		$data['description'][] = sprintf( __('Order %s' , 'jigoshop'), $order->get_order_number() ) . ' = ' . implode(', ', $item_names);
		$data['quantity'][]    = 1;

		/**
		 *  we will leave this commented out for now, we may be able to use it with modifications
		 *  to FuturePay at a later date and adjust this previous code
		 */
// 		foreach ($order->items as $item) {
// 			$_product = $order->get_product_from_item($item);
// 
// 			$data['sku'][] = $_product->get_sku();
// 			$data['price'][] = $_product->get_price();
// 			$data['tax_amount'][]  = 0;
// 			$title = $_product->get_title();
// 			if ($_product instanceof jigoshop_product_variation) {
// 				$title .= ' (' . jigoshop_get_formatted_variation( $item['variation'], true) . ')';
// 			}
// 			$data['description'][] = $title;
// 			$data['quantity'][]    = $item['qty'];
// 		}
// 		
// 		if ( $order->order_discount > 0 ) {
// 			$data['sku'][] = "Discount";
// 			$data['price'][] = $order->order_discount * -1; /* convert to negative for futurepay calcs */
// 			$data['tax_amount'][]  = 0;
// 			$data['description'][] = __('Discount','jigoshop');
// 			$data['quantity'][]    = 1;
// 		}
// 
// 		if ( $order->order_shipping > 0 ) {
// 			$data['sku'][] = "Shipping";
// 			$data['price'][] = $order->order_shipping;
// 			$data['tax_amount'][]  = 0;
// 			$data['description'][] = __('Shipping','jigoshop');
// 			$data['quantity'][]    = 1;
// 		}
// 
// 		$tax = $order->get_total_tax(false,false);
// 		if ( $tax > 0 ) {
// 			$data['sku'][] = "Tax";
// 			$data['price'][] = $tax;
// 			$data['tax_amount'][]  = 0;
// 			$data['description'][] = __('Tax','jigoshop');
// 			$data['quantity'][]    = 1;
// 		}

		try {
			$response = wp_remote_post( $this->request_url . 'merchant-request-order-token', array(
				'body' => http_build_query($data),
				'sslverify' => false
			) );

			// Convert error to exception
			if ( is_wp_error( $response ) ) {
				if (class_exists('WP_Exception') && $response instanceof WP_Exception) {
		            throw $response;
		        }
		        else {
		        	jigoshop_log($response->get_error_message());
		            throw new Exception( $response->get_error_message() );
		        }
			}

			// Fetch the body from the result, any errors should be caught before proceeding
			$response = trim(wp_remote_retrieve_body( $response ));
			
			// we need something to validate the response.  Valid transactions begin with 'FPTK'
			if ( ! strstr( $response, 'FPTK' ) ) {
		        throw new Exception( $response );
			}
			
			echo '<div id="futurepay"></div>';
			
			echo '<script src="'. $this->request_url .'cart-integration/'.$response.'"></script>';

			echo '<script>
				jQuery(window).load(function() {
					FP.CartIntegration();

					// Need to replace form html
					jQuery("#futurepay").html(FP.CartIntegration.getFormContent());
					FP.CartIntegration.displayFuturePay();
				});

				function FuturePayResponseHandler(response) {
					if(response.error) {
						// TODO: we need something better than this
						alert(response.code + " " + response.message);
					}
					else {
						window.location.replace("./?futurepay="+response.transaction_id);
					}

				}
				
			</script>';

			echo '<input type="button" class="button alt" name="place_order" id="place_order" value="Place Order" onclick="FP.CartIntegration.placeOrder();" />';
		}
		catch (Exception $e) {
			echo '<div class="jigoshop_error">'.$e->getMessage().'</div>';
			jigoshop_log('FUTURE PAY ERROR: '.$e->getMessage());
		}

	}

	/**
	 *  Process the payment and return the result
	 */
	public function process_payment( $order_id ) {

		$order = new jigoshop_order( $order_id );
		jigoshop_cart::empty_cart();
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(jigoshop_get_page_id('pay'))))
		);

	}

	/**
	 *  Receipt_page
	 */
	public function receipt_page( $order ) {

		echo '<p>'.__('Thank you for your order, please fill out the form below to pay with Future Pay.', 'jigoshop').'</p>';
		echo $this->call_futurepay( $order );
	}

	/**
	 *  Check for futurepay IPN Response
	 */
	public function check_ipn_response() {

		// Only run the following code if theres a response from futurepay
		if ( isset($_GET['futurepay'] ) ) {

			$data = array(
				'gmid'   => $this->gmid,
				'otxnid' => $_GET['futurepay']
			);

			$response = wp_remote_post( $this->request_url . 'merchant-order-verification?', array(
				'body' => http_build_query($data),
				'sslverify' => false
			) );

			$response = json_decode(wp_remote_retrieve_body($response), true);

			if ( ! empty($response['OrderReference']) ) {

				// Get the order
				$order_id = substr($response['OrderReference'], 0, strpos($response['OrderReference'], '-'));
				$order = new jigoshop_order( $order_id );

				// Response is valid but lets check it more closly
				do_action("valid-futurepay-ipn-request", $response, $order);
				
				wp_safe_redirect( get_permalink( jigoshop_get_page_id('thanks') ) );
				exit;
			}
		}

	}

	/**
	 *  Successful Payment!
	 */
	public function successful_request( $response, $order ) {

		switch ( strtoupper($response['OrderStatusCode']) ) {
			case 'ACCEPTED':
				$order->add_order_note( __('Payment Authorized', 'jigoshop') );
				jigoshop_log( "Future Pay: payment authorized for Order ID: " . $order->id );
				$order->payment_complete();
				break;

			case 'DECLINED':
				// Hold order
		        $order->update_status('on-hold', sprintf(__('Payment %s via Future Pay.', 'jigoshop'), strtolower($response['OrderStatusCode']) ) );
		        jigoshop_log( "FUTURE PAY: declined order for Order ID: " . $order->id );
				break;
			
			default:
				// Hold order
		        $order->update_status('on-hold', sprintf(__('Payment %s via Future Pay.', 'jigoshop'), strtolower($response['OrderStatusCode']) ) );
		        jigoshop_log( "FUTURE PAY: failed order for Order ID: " . $order->id );
				break;
		}

	}
	
}
