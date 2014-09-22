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
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * Add the gateway to JigoShop
 */
function add_futurepay_gateway( $methods ) {
	$methods[] = 'futurepay';
	return $methods;
}
add_filter( 'jigoshop_payment_gateways', 'add_futurepay_gateway', 5 );


class futurepay extends jigoshop_payment_gateway {

	private static $credit_limit = '500.00';

	private $shop_base_country;
	private $merchant_countries = array( 'US' );
	private $allowed_currency = array( 'USD' );
	private $currency_symbol;
	private static $request_url;

	const FUTUREPAY_LIVE_URL = 'https://api.futurepay.com/remote/';
	const FUTUREPAY_SANDBOX_URL = 'https://demo.futurepay.com/remote/';


	public function __construct() {

		parent::__construct();      /* installs our gateway options in the settings */

		$this->id           = 'futurepay';
		$this->icon         = jigoshop::assets_url() . '/assets/images/icons/futurepay.png';
		$this->has_fields 	= false;
		$this->enabled      = Jigoshop_Base::get_options()->get('jigoshop_futurepay_enabled');
		$this->title        = Jigoshop_Base::get_options()->get('jigoshop_futurepay_title');
		$this->description  = Jigoshop_Base::get_options()->get('jigoshop_futurepay_description');
		$this->gmid         = Jigoshop_Base::get_options()->get('jigoshop_futurepay_gmid');
		self::$request_url  =
			Jigoshop_Base::get_options()->get('jigoshop_futurepay_mode') == 'no'
			? self::FUTUREPAY_LIVE_URL
			: self::FUTUREPAY_SANDBOX_URL;

		add_action( 'init', array( $this, 'check_response' ) );
		add_action( 'valid-futurepay-request', array( $this, 'successful_request' ), 10, 2 );
		add_action( 'receipt_futurepay', array( $this, 'receipt_page' ) );
		add_action( 'admin_notices', array( $this, 'futurepay_notices' ) );
		add_action( 'wp_footer', array( $this, 'futurepay_script' ) );

		$this->currency_symbol = get_jigoshop_currency_symbol();
		$this->shop_base_country = jigoshop_countries::get_base_country();
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
			'name' => sprintf(__('FuturePay %s', 'jigoshop'), '<img style="vertical-align:middle;margin-top:-4px;margin-left:10px;" src="'.jigoshop::assets_url() .'/assets/images/icons/futurepay.png" alt="FuturePay">'),
			'type' => 'title',
			'desc' => sprintf(__('This module allows you to accept online payments via %s allowing customers to buy now and pay later without a credit card.  FuturePay is a safe, convenient and secure way for US customers to buy online in one-step.  %s', 'jigoshop'), '<a href="http://www.futurepay.com/" target="_blank">'.__('FuturePay','jigoshop').'</a>', '<a href="https://www.futurepay.com/main/merchant-signup?platform=77_FPM495845-1" target="_blank">'.__('Signup for a Merchant Account','jigoshop').'</a>' )
		);

		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable FuturePay','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_futurepay_enabled',
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
			'id' 		=> 'jigoshop_futurepay_title',
			'std' 		=> __('FuturePay','jigoshop'),
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Customer Message','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the description which the user sees during checkout.','jigoshop'),
			'id' 		=> 'jigoshop_futurepay_description',
			'std' 		=> __('Pay with FuturePay. Buy now and pay later. No credit card needed.  You will be asked to enter your FuturePay username and password, or create an account when you Place your Order.', 'jigoshop'),
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

		if ( ! in_array( Jigoshop_Base::get_options()->get( 'jigoshop_currency' ), $this->allowed_currency )) {
			echo '<div class="error"><p>'.sprintf(__('The FuturePay gateway accepts payments in currencies of %s.  Your current currency is %s.  FuturePay won\'t work until you change the Jigoshop currency to an accepted one.  FuturePay is currently disabled on the Payment Gateways settings tab.','jigoshop'), implode( ', ', $this->allowed_currency ), Jigoshop_Base::get_options()->get( 'jigoshop_currency' ) ).'</p></div>';
			Jigoshop_Base::get_options()->set( 'jigoshop_futurepay_enabled', 'no' );
		}

		if ( ! in_array( $this->shop_base_country, $this->merchant_countries )) {
			$country_list = array();
			foreach ( $this->merchant_countries as $this_country ) {
				$country_list[] = jigoshop_countries::get_country($this_country);
			}
			echo '<div class="error"><p>'.sprintf(__('The FuturePay gateway is available to merchants from: %s.  Your country is: %s.  FuturePay won\'t work until you change the Jigoshop Shop Base country to an accepted one.  FuturePay is currently disabled on the Payment Gateways settings tab.','jigoshop'), implode( ', ', $country_list ), jigoshop_countries::get_base_country() ).'</p></div>';
			Jigoshop_Base::get_options()->set( 'jigoshop_futurepay_enabled', 'no' );
		}

	}


	/**
	 *  Determine conditions for which FuturePay is available on a Shop
	 */
	public function is_available() {

		if ( $this->enabled != 'yes' ) {
			return false;
		}

		if ( ! in_array( Jigoshop_Base::get_options()->get( 'jigoshop_currency' ), $this->allowed_currency ) ) {
			return false;
		}

		if ( ! in_array( $this->shop_base_country, $this->merchant_countries )) {
			return false;
		}

		return true;
	}


	/**
	 *
	 */
	public function futurepay_script() {
		if ( ! is_page( jigoshop_get_page_id( 'checkout' )) ) return;
    	?>
		<script type="text/javascript">
			/*<![CDATA[*/
				jQuery(document).ready( function($) {
					var credit_limit = '<?php echo self::$credit_limit; ?>';
					var currency_symbol = '<?php echo $this->currency_symbol; ?>';
					var fp_label = $('#payment_method_futurepay').next().html();
					var totalstr = $('.shop_table tfoot td:last()').find('strong').html().split(currency_symbol);
					var total = parseFloat(totalstr[1]);
					if ( total > credit_limit ) {
						$('#payment_method_futurepay').attr('disabled', 'disabled');
						$('#payment_method_futurepay').next().html('FuturePay -- unavailable for Orders over '+currency_symbol+credit_limit);
						$('#payment input[name=payment_method]:not(:disabled):first').attr('checked',true).trigger('click');
					} else {
						$('#payment_method_futurepay').removeAttr('disabled');
						$('#payment_method_futurepay').next().html(fp_label);
					}
					$(document).ajaxStop( function(event,request,settings) {
						if ( event.isTrigger ) {
							totalstr = $('.shop_table tfoot td:last()').find('strong').html().split(currency_symbol);
							total = parseFloat(totalstr[1]);
							if ( total > credit_limit ) {
								$('#payment_method_futurepay').next().html('FuturePay -- unavailable for Orders over $500.00');
								$('#payment_method_futurepay').attr('disabled', 'disabled');
								$('#payment input[name=payment_method]:not(:disabled):first').attr('checked',true).trigger('click');
							} else {
								$('#payment_method_futurepay').next().html(fp_label);
								$('#payment_method_futurepay').removeAttr('disabled');
							}
						}
					});
				});
			/*]]>*/
		</script>
    	<?php
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
				$title .= ' (' . jigoshop_get_formatted_variation( $_product, $item['variation'], true) . ')';
			}

			$item_names[] = $item['qty'] . ' x ' . $title;

		}
		// now add the one line item to the necessary product field arrays
		$data['sku'][] = "Products";
		$data['price'][] = $order->order_total; // futurepay only needs final order amount
		$data['tax_amount'][]  = 0;
		$data['description'][] = sprintf( __('Order %s' , 'jigoshop'), $order->get_order_number() ) . ' = ' . implode(', ', $item_names);
		$data['quantity'][]    = 1;

		try {

			$response = wp_remote_post( self::$request_url . 'merchant-request-order-token', array(
				'body' => http_build_query($data),
				'sslverify' => false
			) );

			// Convert error to exception
			if ( is_wp_error( $response ) ) {
				if ( class_exists('WP_Exception') && $response instanceof WP_Exception ) {
		            throw $response;
		        }
		        else {
		        	jigoshop_log( $response->get_error_message() );
		            throw new Exception( $response->get_error_message() );
		        }
			}

			// Fetch the body from the result, any errors should be caught before proceeding
			$response = trim( wp_remote_retrieve_body( $response ) );

			// we need something to validate the response.  Valid transactions begin with 'FPTK'
			if ( ! strstr( $response, 'FPTK' ) ) {
				$error_message = isset( self::$futurepay_errorcodes[$response] )
					? self::$futurepay_errorcodes[$response]
					: __('An unknown error has occured with code = ', 'jigoshop') . $response;
				$order->add_order_note( sprintf(__('FUTUREPAY: %s', 'jigoshop'), $error_message ) );
				jigoshop::add_error( sprintf(__('FUTUREPAY: %s.  Please try again or select another gateway for your Order.', 'jigoshop'), $error_message ) );
				wp_safe_redirect( get_permalink( jigoshop_get_page_id( 'checkout' ) ) );
				exit;
			}

			/**
			 *  If we're good to go, haul in FuturePay's javascript and display the payment form
			 *  so that the customer can enter his ID and password
			 */

			echo '<div id="futurepay"></div>';

			echo '<script src="'. self::$request_url .'cart-integration/'.$response.'"></script>';

			echo '<script type="text/javascript">
				/*<![CDATA[*/
				jQuery(window).load( function() {
					FP.CartIntegration();

					// Need to replace form html
					jQuery("#futurepay").html(FP.CartIntegration.getFormContent());
					FP.CartIntegration.displayFuturePay();
				});

				function FuturePayResponseHandler(response) {
					if (response.error) {
						// TODO: we need something better than this
						alert(response.code + " " + response.message);
					}
					else {
						window.location.replace("./?futurepay="+response.transaction_id);
					}

				}
				/*]]>*/
			</script>';

			echo '<input type="button" class="button alt" name="place_order" id="place_order" value="Place Order" onclick="FP.CartIntegration.placeOrder();" />';
		}
		catch ( Exception $e ) {

			echo '<div class="jigoshop_error">'.$e->getMessage().'</div>';
			jigoshop_log( 'FUTUREPAY ERROR: '.$e->getMessage() );

		}

	}


	/**
	 *  Process the payment and return the result
	 */
	public function process_payment( $order_id ) {

		$order = new jigoshop_order( $order_id );
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink(jigoshop_get_page_id('pay')) ))
		);

	}


	/**
	 *  Receipt_page
	 */
	public function receipt_page( $order ) {

		echo '<p>'.__('Thank you for your order, please fill out the form below to pay with FuturePay.', 'jigoshop').'</p>';
		echo $this->call_futurepay( $order );
	}


	/**
	 *  Check for Futurepay Response
	 */
	public function check_response() {

		// Only run the following code if theres a response from futurepay
		if ( isset( $_GET['futurepay'] ) ) {

			$data = array(
				'gmid'   => $this->gmid,
				'otxnid' => $_GET['futurepay']
			);

			$response = wp_remote_post( self::$request_url . 'merchant-order-verification?', array(
				'body' => http_build_query($data),
				'sslverify' => false
			) );

			$response = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! empty( $response['OrderReference'] ) ) {

				// Get the order
				$order_id = substr( $response['OrderReference'], 0, strpos( $response['OrderReference'], '-' ));
				$order = new jigoshop_order( $order_id );

				// Response is valid but lets check it more closly
				do_action( "valid-futurepay-request", $response, $order );

				// set the $_GET query vars for the thankyou page, this empties the Cart
				wp_safe_redirect( add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( jigoshop_get_page_id('thanks') ) ) ) );
				exit;
			}
		}

	}


	/**
	 *  Successful Payment!
	 */
	public function successful_request( $response, $order ) {

		switch ( strtoupper( $response['OrderStatusCode'] ) ) {
			case 'ACCEPTED':
				$order->add_order_note( __('Payment Authorized', 'jigoshop') );
				jigoshop_log( "FuturePay: payment authorized for Order ID: " . $order->id );
				$order->payment_complete();
				break;

			case 'DECLINED':
				// Hold order
		        $order->update_status( 'on-hold', sprintf(__('Payment %s via FuturePay.', 'jigoshop'), strtolower( $response['OrderStatusCode'] ) ) );
		        jigoshop_log( "FUTUREPAY: declined order for Order ID: " . $order->id );
				break;

			default:
				// Hold order
		        $order->update_status( 'on-hold', sprintf(__('Payment %s via FuturePay.', 'jigoshop'), strtolower( $response['OrderStatusCode'] ) ) );
		        jigoshop_log( "FUTUREPAY: failed order for Order ID: " . $order->id );
				break;
		}

	}


	private static $futurepay_errorcodes = array(
		'FP_EXISTING_INVALID_CUSTOMER_STATUS'
		=> 'Invalid Customer Status, the customer exists in FuturePay and is in an Active or Accepted Status', 'jigoshop',
		'FP_INVALID_ID_REQUEST'
		=> 'Error: The GMID could not be validated – either missing or not valid format – Contact FuturePay', 'jigoshop',
		'FP_INVALID_SERVER_REQUEST'
		=> 'Error: Either the Merchant Server is not on our IP Whitelist or the Order Reference was Missing', 'jigoshop',
		'FP_PRE_ORDER_EXCEEDS_MAXIMUM'
		=> 'The Maximum Amount for a FuturePay order has been exceeded: Currently $500.00', 'jigoshop',
		'FP_MISSING_REFERENCE' =>
		'Reference was not detected in the Query String', 'jigoshop',
		'FP_INVALID_REFERENCE'
		=> 'Reference was invalid', 'jigoshop',
		'FP_ORDER_EXISTS'
		=> 'The reference exists with an order that has completed sales attached', 'jigoshop',
		'FP_MISSING_REQUIRED_FIRST_NAME'
		=> 'First Name was not detected in the Query String', 'jigoshop',
		'FP_MISSING_REQUIRED_LAST_NAME'
		=> 'Last Name was not detected in the Query String', 'jigoshop',
		'FP_MISSING_REQUIRED_PHONE'
		=> 'Phone Name was not detected in the Query String', 'jigoshop',
		'FP_MISSING_REQUIRED_CITY'
		=> 'City was not detected in the Query String', 'jigoshop',
		'FP_MISSING_REQUIRED_STATE'
		=> 'State was not detected in the Query String', 'jigoshop',
		'FP_MISSING_REQUIRED_ADDRESS'
		=> 'Address was not detected in the Query String', 'jigoshop',
		'FP_MISSING_REQUIRED_COUNTRY'
		=> 'Country was not detected in the Query String', 'jigoshop',
		'FP_COUNTRY_US_ONLY'
		=> 'The Country was not USA', 'jigoshop',
		'FP_MISSING_EMAIL'
		=> 'Email was not detected in the Query String', 'jigoshop',
		'FP_INVALID_EMAIL_SIZE'
		=> 'Email Size was greater than 85', 'jigoshop',
		'FP_INVALID_EMAIL_FORMAT'
		=> 'Email Format was not valid', 'jigoshop',
		'FP_MISSING_REQUIRED_ZIP'
		=> 'Zip was not detected in the Query String', 'jigoshop',
		'FP_NO_ZIP_FOUND'
		=> 'The Zip Code could not be found in the FuturePay lookup, may be a PO Box or Military Address which are not Accepted', 'jigoshop',
		'FP_FAILED_ZIP_LOOKUP'
		=> 'FuturePay failed to lookup the Zip Code – FuturePay needs to investigate the cause', 'jigoshop',
		'FP_MISSING_ORDER_ITEM_FIELDS'
		=> 'At least one order item must exist and for each order item all of the fields must exist for price, quantity, sku, description, tax_amount', 'jigoshop',
		'FP_INVALID_PRICE'
		=> 'Price must be a non-negative float value', 'jigoshop',
		'FP_INVALID_TAX'
		=> 'Tax must be a non-negative float value', 'jigoshop',
		'FP_INVALID_QUANTITY'
		=> 'Quantity must be an integer', 'jigoshop',
		'FP_INVALID_SHIPPING_DATE'
		=> 'The Shipping date could not be parsed', 'jigoshop',
		'FP_SHIPPING_IN_PAST'
		=> 'The Shipping date must be today or in the Future', 'jigoshop',
		'FP_PRE_ORDER_FAILED'
		=> 'An Error occurred in trying to save the Order – FuturePay needs to investigate the cause', 'jigoshop'
	);

}
