<?php
/**
 * DIBS FlexWin Gateway
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

/**
 * Add the gateway to JigoShop
 **/
function add_dibs_gateway( $methods ) {
	$methods[] = 'dibs';
	return $methods;
}
add_filter( 'jigoshop_payment_gateways', 'add_dibs_gateway', 50 );


class dibs extends jigoshop_payment_gateway {

	public function __construct() {
		
        parent::__construct();
		
		$this->id = 'dibs';
		$this->icon = '';
		$this->has_fields = false;
		$this->enabled = $this->jigoshop_options->get_option('jigoshop_dibs_enabled');
		$this->title = $this->jigoshop_options->get_option('jigoshop_dibs_title');
		$this->merchant = $this->jigoshop_options->get_option('jigoshop_dibs_merchant');
		$this->description  = $this->jigoshop_options->get_option('jigoshop_dibs_description');
		$this->testmode = $this->jigoshop_options->get_option('jigoshop_dibs_testmode');
		$this->key1 = $this->jigoshop_options->get_option('jigoshop_dibs_key1');
		$this->key2 = $this->jigoshop_options->get_option('jigoshop_dibs_key2');

		add_action('init', array(&$this, 'check_callback') );
		add_action('valid-dibs-callback', array(&$this, 'successful_request') );
		add_action('receipt_dibs', array(&$this, 'receipt_page'));

	}


	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *
	 * These should be installed on the Jigoshop_Options 'Payment Gateways' tab
	 *
	 */	
	protected function get_default_options() {
	
		$defaults = array();
		
		// Define the Section name for the Jigoshop_Options
		$defaults[] = array( 'name' => __('DIBS FlexWin', 'jigoshop'), 'type' => 'title', 'desc' => __('DIBS FlexWin works by sending the user to <a href="http://www.dibspayment.com/">DIBS</a> to enter their payment information.', 'jigoshop') );
		
		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable DIBS FlexWin','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_dibs_enabled',
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
			'id' 		=> 'jigoshop_dibs_title',
			'std' 		=> __('DIBS','jigoshop'),
			'type' 		=> 'text'
		);
		
		$defaults[] = array(
			'name'		=> __('Description','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the description which the user sees during checkout.','jigoshop'),
			'id' 		=> 'jigoshop_dibs_description',
			'std' 		=> __("Pay via DIBS using credit card or bank transfer.", 'jigoshop'),
			'type' 		=> 'longtext'
		);

		$defaults[] = array(
			'name'		=> __('DIBS Merchant id','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Please enter your DIBS merchant id; this is needed in order to take payment!','jigoshop'),
			'id' 		=> 'jigoshop_dibs_merchant',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('DIBS MD5 Key 1','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Please enter your DIBS MD5 key #1; this is needed in order to take payment!','jigoshop'),
			'id' 		=> 'jigoshop_dibs_key1',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('DIBS MD5 Key 2','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Please enter your DIBS MD5 key #2; this is needed in order to take payment!','jigoshop'),
			'id' 		=> 'jigoshop_dibs_key2',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Enable test mode','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('When test mode is enabled only DIBS specific test-cards are accepted.','jigoshop'),
			'id' 		=> 'jigoshop_dibs_testmode',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		return $defaults;
	}


	/**
	* There are no payment fields for dibs, but we want to show the description if set.
	**/
	function payment_fields() {
		if ($jigoshop_dibs_description = $this->jigoshop_options->get_option('jigoshop_dibs_description')) echo wpautop(wptexturize($jigoshop_dibs_description));
	}

	/**
	* Generate the dibs button link
	**/
	public function generate_form( $order_id ) {
		
		$order = new jigoshop_order( $order_id );

		$action_adr = 'https://payment.architrade.com/paymentweb/start.action';

		// Dibs currency codes http://tech.dibs.dk/toolbox/currency_codes/
		$dibs_currency = array(
			'DKK' => '208', // Danish Kroner
			'EUR' => '978', // Euro
			'USD' => '840', // US Dollar $
			'GBP' => '826', // English Pound £
			'SEK' => '752', // Swedish Kroner
			'AUD' => '036', // Australian Dollar
			'CAD' => '124', // Canadian Dollar
			'ISK' => '352', // Icelandic Kroner
			'JPY' => '392', // Japanese Yen
			'NZD' => '554', // New Zealand Dollar
			'NOK' => '578', // Norwegian Kroner
			'CHF' => '756', // Swiss Franc
			'TRY' => '949', // Turkish Lire
		);
		// filter redirect page
		$checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks') );

		$args =
			array(
				// Merchant
				'merchant'   => $this->merchant,
				'decorator'  => 'default',

				// Session
				'lang'       => 'sv',

				// Order
				'amount'     => $order->order_total * 100,
				'orderid'    => $order_id,
				'uniqueoid'  => $order->order_key,
				'currency'   => $dibs_currency[$this->jigoshop_options->get_option('jigoshop_currency')],
				'ordertext'  => 'TEST',

				// URLs
				'callbackurl'=> site_url('/jigoshop/dibscallback.php'),

				// TODO these urls will not work correctly since DIBS ignores the querystring
				'accepturl'  => add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink($checkout_redirect))),
				'cancelurl'  => $order->get_cancel_order_url(),

		);


		// Calculate key
		// http://tech.dibs.dk/dibs_api/other_features/md5-key_control/
		$args['md5key'] = MD5($this->jigoshop_options->get_option('jigoshop_dibs_key2') . MD5($this->jigoshop_options->get_option('jigoshop_dibs_key1') . 'merchant=' . $args['merchant'] . '&orderid=' . $args['orderid'] . '&currency=' . $args['currency'] . '&amount=' . $args['amount']));

		if( !empty($_SERVER['HTTP_CLIENT_IP']) ) {
			$args['ip'] = $_SERVER['HTTP_CLIENT_IP'];
		}

		if ( $this->testmode == 'yes' ) {
			$args['test'] = 'yes';
		}

		$fields = '';
		foreach ($args as $key => $value) {
			$fields .= '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'" />';
		}

		return '<form action="'.$action_adr.'" method="post" id="dibs_payment_form">
				' . $fields . '
				<input type="submit" class="button-alt" id="submit_dibs_payment_form" value="'.__('Pay via DIBS', 'jigoshop').'" /> <a class="button cancel" href="'.esc_url($order->get_cancel_order_url()).'">'.__('Cancel order &amp; restore cart', 'jigoshop').'</a>
				<script type="text/javascript">
					jQuery(function(){
						jQuery("body").block(
							{
								message: "<img src=\"'.jigoshop::assets_url().'/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" />'.__('Thank you for your order. We are now redirecting you to DIBS to make payment.', 'jigoshop').'",
								overlayCSS:
								{
									background: "#fff",
									opacity: 0.6
								},
								css: {
							        padding:        20,
							        textAlign:      "center",
							        color:          "#555",
							        border:         "3px solid #aaa",
							        backgroundColor:"#fff",
							        cursor:         "wait"
							    }
							});
						jQuery("#submit_dibs_payment_form").click();
					});
				</script>
			</form>';

	}

	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {

		$order = new jigoshop_order( $order_id );

		return array(
			'result' => 'success',
			'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, apply_filters('jigoshop_get_return_url', get_permalink(jigoshop_get_page_id('pay')))))
		);

	}

	/**
	* receipt_page
	**/
	function receipt_page( $order ) {

		echo '<p>'.__('Thank you for your order, please click the button below to pay with DIBS.', 'jigoshop').'</p>';

		echo $this->generate_form( $order );

	}

	/**
	* Check for DIBS Response
	**/
	function check_callback() {
		if ( strpos($_SERVER["REQUEST_URI"], '/jigoshop/dibscallback.php') ) {

			error_log('Dibs callback!');

			$_POST = stripslashes_deep($_POST);

			do_action("valid-dibs-callback", $_POST);
		}
	}

	/**
	* Successful Payment!
	**/
	function successful_request( $posted ) {
	
		
		// Custom holds post ID
		if ( !empty($posted['transact']) && !empty($posted['orderid']) && is_numeric($posted['orderid']) ) {

			// Verify MD5 checksum
			// http://tech.dibs.dk/dibs_api/other_features/md5-key_control/
			$key1 = $this->jigoshop_options->get_option('jigoshop_dibs_key1');
			$key2 = $this->jigoshop_options->get_option('jigoshop_dibs_key2');
			$vars = 'transact='. $posted['transact'] . '&amount=' . $posted['amount'] . '&currency=' . $posted['currency'];
			$md5 = MD5($key2 . MD5($key1 . $vars));

			if($posted['authkey'] != $md5) {
				error_log('MD5 check failed for Dibs callback with order_id:'.$posted['orderid']);
				exit();
			}

			$order = new jigoshop_order( (int) $posted['orderid'] );

			if ($order->order_key !== $posted['uniqueoid']) {
				error_log('Unique ID check failed for Dibs callback with order_id:'.$posted['orderid']);
				exit;
			}

			if ($order->status !== 'completed') {

				$order->add_order_note( __('Callback payment completed', 'jigoshop') );
				$order->payment_complete();

			}

			exit;

		}

	}

}
