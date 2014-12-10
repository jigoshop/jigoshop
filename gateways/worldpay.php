<?php
/**
 * WorldPay Standard Gateway
 *
 * DISCLAIMER
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
 * Add the gateway to Jigoshop
 */
add_filter('jigoshop_payment_gateways', function($methods) {
	$methods[] = 'jigoshop_worldpay';

	return $methods;
}, 3);

class jigoshop_worldpay extends jigoshop_payment_gateway
{
	private static $allowed_currencies = array(
		'BGN' => 'BGL',
		'EUR' => 'EUR',
		'USD' => 'USD',
		'GBP' => 'GBP',
		'CYP' => 'CYP',
		'JPY' => 'JPY',
		'AUD' => 'AUD',
		'BHD' => 'BHD',
		'THB' => 'THB',
		'CAD' => 'CAD',
		'CLP' => 'CLP',
		'CHF' => 'CHF',
		'COP' => 'COP',
		'CZK' => 'CZK',
		'VND' => 'VND',
		'AED' => 'AED',
		'DKK' => 'DKK',
		'HUF' => 'HUF',
		'HKD' => 'HKD',
		'HRK' => 'HRK',
		'JOD' => 'JOD',
		'KES' => 'KES',
		'KWD' => 'KWD',
		'EEK' => 'EEK',
		'ROL' => 'ROL',
		'MTL' => 'MTL',
		'LVL' => 'LVL',
		'LTL' => 'LTL',
		'MXN' => 'MXN',
		'MXP' => 'MXP',
		'ANG' => 'ANG',
		'ILS' => 'ILS',
		'NOK' => 'NOK',
		'TWD' => 'TWD',
		'NZD' => 'NZD',
		'PHP' => 'PHP',
		'PLN' => 'PLN',
		'QAR' => 'QAR',
		'BRL' => 'BRL',
		'RUB' => 'RUB',
		'ZAR' => 'ZAR',
		'MYR' => 'MYR',
		'OMR' => 'OMR',
		'RON' => 'RON',
		'IDR' => 'IDR',
		'INR' => 'INR',
		'PKR' => 'PKR',
		'SGD' => 'SGD',
		'SAR' => 'SAR',
		'SKK' => 'SKK',
		'SEK' => 'SEK',
		'LKR' => 'LKR',
		'UAH' => 'UAH',
		'KRW' => 'KRW',
		'CNY' => 'CNY',
	);

	public function __construct()
	{
		parent::__construct();

		$options = Jigoshop_Base::get_options();

		$this->id = 'jigoshop_worldpay';
		$this->icon = jigoshop::assets_url().'/assets/images/icons/worldpay.png';
		$this->has_fields = false;
		$this->enabled = $options->get('jigoshop_worldpay_is_enabled');
		$this->title = $options->get('jigoshop_worldpay_method_title');
		$this->description = $options->get('jigoshop_worldpay_checkout_description');
		$this->testmode = $options->get('jigoshop_worldpay_test_mode');
		$this->installation_id = $options->get('jigoshop_worldpay_install_id');
		$this->fixed_currency = $options->get('jigoshop_worldpay_fixed_currency');
		$this->md5_encrypt = $options->get('jigoshop_worldpay_md5');
		$this->secret_word = $options->get('jigoshop_worldpay_md5_secret_word');
		$this->response_pass = $options->get('jigoshop_worldpay_response_password');

		$this->receive_err_log = $options->get('jigoshop_worldpay_receive_security_logs');
		$this->emailto_err_log = $options->get('jigoshop_worldpay_security_logs_emailto');

		$this->currency = $options->get('jigoshop_currency');

		$this->notify_url = jigoshop_request_api::query_request('?js-api=JS_Gateway_WorldPay', false);
		add_action('jigoshop_api_js_gateway_worldpay', array($this, 'check_worldpay_response'));

		add_action('admin_notices', array($this, 'worldpay_notices'));
		add_action('receipt_jigoshop_worldpay', array($this, 'receipt_page'));

		add_action('wp_footer', array($this, 'worldpay_script'));
	}

	public function worldpay_script()
	{
		if (!is_checkout()) return;
		?>
		<script type="text/javascript">
			/*<![CDATA[*/
			jQuery(document).ready(function($){
				$(document.body).on('click', '.payment_methods input.input-radio', function(){
					var label = $('#payment_method_jigoshop_worldpay').next();
					var image = $(label).find('img');
					if($(this).attr('ID') == 'payment_method_jigoshop_worldpay'){
						$(image).css('display', 'none');
					} else {
						$(image).css('display', '');
					}
				});
			});
			/*]]>*/
		</script>
	<?php
	}

	/**
	 *  Admin Notices for conditions under which WorldPay is available on a Shop
	 */
	public function worldpay_notices()
	{
		$options = Jigoshop_Base::get_options();

		if ($this->enabled == 'no') {
			return;
		}

		if (!$this->installation_id) {
			echo '<div class="error"><p>'.__('The WorldPay gateway does not have values entered for <strong>Installation ID</strong> and the gateway is set to enabled.  Please enter your credentials for this or the gateway <strong>will not</strong> be available on the Checkout.  Disable the gateway to remove this warning.', 'jigoshop').'</p></div>';
		}

		if (!in_array($this->currency, self::$allowed_currencies)) {
			echo '<div class="error"><p>'.sprintf(__('The WorldPay gateway accepts payments in currencies of %s.  Your current currency is %s.  WorldPay won\'t work until you change the Jigoshop currency to an accepted one.  WorldPay is <strong>currently disabled</strong> on the Payment Gateways settings tab.', 'jigoshop'), implode(', ', self::$allowed_currencies), $this->currency).'</p></div>';
			$options->set('jigoshop_worldpay_is_enabled', 'no');
		}

		if ($this->md5_encrypt == 'yes' && empty($this->secret_word)) {
			echo '<div class="error"><p>'.__('The WorldPay gateway <strong>Use MD5 Signature</strong> setting is enabled, but you have not entered a <strong>Secret Word</strong>.  The WorldPay gateway will not be available on the Checkout until you resolve this.', 'jigoshop').'</p></div>';
		}
	}

	/**
	 *  Determine conditions for which WorldPay is available on the Shop Checkout
	 */
	public function is_available()
	{
		if ($this->enabled == 'no') {
			return false;
		}

		if (!$this->installation_id) {
			return false;
		}

		if (!in_array($this->currency, self::$allowed_currencies)) {
			return false;
		}

		if ($this->md5_encrypt == 'yes' && empty($this->secret_word)) {
			return false;
		}

		return true;
	}

	/**
	 * WorldPay description that is shown on the Checkout when the gateway is selected
	 */
	function payment_fields()
	{
		echo '<script language="javascript" src="https://secure.worldpay.com/wcc/logo?instId='.$this->installation_id.'"></script>';
		if ($this->description) echo wpautop(wptexturize($this->description));
	}

	/**
	 * Process the payment and return the result
	 */
	function process_payment($order_id)
	{
		$order = new jigoshop_order($order_id);

		return array(
			'result' => 'success',
			'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(jigoshop_get_page_id('pay'))))
		);
	}

	/**
	 * Receipt Page - uses the 'pay' page, called from action hook
	 */
	function receipt_page($order_id)
	{
		echo '<p>'.__('Thank you for your order, please click the button below to pay with WorldPay.', 'jigoshop').'</p>';
		echo $this->display_worldpay_redirect_form($order_id);
	}

	/**
	 * Generates WorldPay form and redirects to WordPay with Order information
	 */
	public function display_worldpay_redirect_form($order_id)
	{
		$order = new jigoshop_order($order_id);

		if ($this->testmode == 'yes') {
			$worldpay_url = "https://secure-test.worldpay.com/wcc/purchase";
			$testmode = (int)100;
		} else {
			$worldpay_url = "https://secure.worldpay.com/wcc/purchase";
			$testmode = (int)0;
		}

		$order_total = number_format($order->order_total, 2, '.', '');

		$worldpay_args = array(
			'instId' => $this->installation_id,
			'cartId' => $order_id,
			'amount' => $order_total,
			'currency' => $this->currency,
			'testMode' => $testmode,
			'email' => $order->billing_email,
			'MC_invoice' => $order->order_key
		);

		// Send 'canned' name if testmode or full name if not testmode.
		if ($this->testmode == 'yes') {
			$worldpay_args['name'] = 'AUTHORISED';
		} else {
			$worldpay_args['name'] = $order->billing_first_name.' '.$order->billing_last_name;
		}

		// Address info
		$worldpay_args['address1'] = $order->billing_address_1;
		$worldpay_args['address2'] = $order->billing_address_2;
		$worldpay_args['town'] = $order->billing_city;
		$worldpay_args['region'] = $order->billing_state;
		$worldpay_args['postcode'] = $order->billing_postcode;
		$worldpay_args['country'] = $order->billing_country;
		$worldpay_args['fixContact'] = ''; /* no address editing */

		// Setup the Dymanic URL properties using Jigoshop Request API
		$worldpay_args['MC_callback'] = $this->notify_url;
		$worldpay_args['MC_cancel_return'] = $order->get_cancel_order_url();

		// Cart Contents - Generate cart description
		$desc = '';

		if (sizeof($order->items) > 0) {
			foreach ($order->items as $item) {
				$_product = $order->get_product_from_item($item);

				if ($_product->exists() && $item['qty']) {
					$title = $_product->get_title();

					// if variation, insert variation details into product title
					if ($_product instanceof jigoshop_product_variation) {
						$title .= ' ('.jigoshop_get_formatted_variation($_product, $item['variation'], true).')';
					}

					$desc .= $item['qty'].' x '.$title.'<br/>';
				}
			}

			// Add the description
			$worldpay_args['desc'] = $desc;
		}

		if ($this->fixed_currency == 'yes') {
			$worldpay_args['hideCurrency'] = '';
		}

		// MD5 hash the main parameters we are sending to WorldPay
		if ($this->md5_encrypt == 'yes' && !empty($this->secret_word)) {
			// Add the fields you are hashing
			$hash_fields = 'instId:cartId:amount:currency';
			$worldpay_args['signatureFields'] = $hash_fields;
			// Add the hash signature
			$hash_message = $this->secret_word.':'.$this->installation_id.':'.$order_id.':'.$order_total.':'.$this->currency;
			$worldpay_args['signature'] = md5($hash_message);
		}

		$worldpay_form_array = array();

		foreach ($worldpay_args as $key => $value) {
			if ($key == 'hideCurrency') {
				$worldpay_form_array[] = '<input type="hidden" name="'.$key.'" />';
				continue;
			}

			if ($key == 'fixContact') {
				$worldpay_form_array[] = '<input type="hidden" name="'.$key.'" />';
				continue;
			}

			$worldpay_form_array[] = '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
		}

		return jigoshop_render_result('gateways/worldpay', array(
			'url' => $worldpay_url,
			'fields' => $worldpay_args,
		));
	}

	/**
	 * Catch the WorldPay Response - called from Jigoshop Request API Action Hook
	 */
	public function check_worldpay_response()
	{
		if (!empty($_POST)) {
			$this->validate_response_origins_and_passwords();
			$this->process_response(stripslashes_deep($_POST));
		}
	}

	/**
	 * Check WorldPay origins and payment response passwords
	 * Used to log and send emails for possible security errors
	 */
	private function validate_response_origins_and_passwords()
	{
		$header_ip = $_SERVER['REMOTE_ADDR'];
		$header_host = gethostbyaddr($header_ip);

		$callbackPW = $this->get_post('callbackPW');
		$validated = false;
		$error = array();

		if (strpos($header_host, 'worldpay.com') !== false) {
			if (!empty($this->response_pass) && !empty($callbackPW)) {
				if ($this->response_pass == $callbackPW) {
					$validated = true; /* both passwords match */
				} else {
					$error['validate_payment_password_error'] = sprintf(__('Your shop payment response password: \'%s\', WorldPay payment response password: \'%s\'.  The passwords for Payment Response Password from your Jigoshop WorldPay gateway settings and your WorldPay Merchant account do NOT match.', 'jigoshop'), $callbackPW, $this->response_pass);
					jigoshop_log($error['validate_payment_password_error'], 'WorldPay Gateway');
				}
			} elseif (empty($this->response_pass) && empty($callbackPW)) {
				$validated = true; /* skip check if no passwords supplied */
			} else {
				$error['validate_payment_password_missing'] = sprintf(__('Your shop payment response password: \'%s\', WorldPay payment response password: \'%s\'.  If you are using a Payment Response Password, make sure it is entered in BOTH the WorldPay Gateway settings in Jigoshop AND in your WorldPay Merchant Account.', 'jigoshop'), $callbackPW, $this->response_pass);
				jigoshop_log($error['validate_payment_password_missing'], 'WorldPay Gateway');
			}
		} else {
			$error['validate_origin_error'] = sprintf(__('The Payment response came from IP: %s and Domain: %s -- and this does not appear to be a WorldPay domain.', 'jigoshop'), $header_ip, $header_host);
			jigoshop_log($error['validate_origin_error'], 'WorldPay Gateway');
		}

		if ($this->receive_err_log == 'yes' && !$validated) {
			$info = sprintf(__('Order #%s ', 'jigoshop'), $this->get_post('cartId'));
			$this->email_worldpay_error_logs($error, $_POST, $info);
		}

		return $validated; /* currently we don't actually use this */
	}

	/**
	 * Safely get POST variables
	 *
	 * @var string POST variable name
	 * @return string The variable value
	 */
	private function get_post($name)
	{
		if (isset($_POST[$name])) {
			return strip_tags(stripslashes(trim($_POST[$name])));
		}

		return null;
	}

	/**
	 * Email the error logs
	 */
	private function email_worldpay_error_logs($error = array(), $posted = array(), $info = '')
	{
		$subject = sprintf(__('[%s] Jigoshop WorldPay Error Log for %s', 'jigoshop'), html_entity_decode(get_bloginfo('name'), ENT_QUOTES), $info);
		$message = $info.PHP_EOL;
		$message .= '======================================================='.PHP_EOL;
		if (!empty($error)) {
			$message .= __('Errors logged during the Jigoshop WorldPay payment response validation:', 'jigoshop').PHP_EOL;
			foreach ($error as $key => $value) {
				$message .= $key.' = '.$value.PHP_EOL;
			}
			$message .= '======================================================='.PHP_EOL;
		}

		if (!empty($this->emailto_err_log)) $email = $this->emailto_err_log;
		else $email = Jigoshop_Base::get_options()->get('jigoshop_email');

		wp_mail($email, $subject, $message);
	}

	/**
	 * Process Response from WorldPay
	 */
	private function process_response($posted)
	{
		$installation_id = $this->get_post('instId');
		$cartId = $this->get_post('cartId');
		$transId = $this->get_post('transId');
		$processed_transID = get_post_meta($cartId, '_worldpay_processed_transID', true);
		$amount = $this->get_post('amount');
		$authAmount = $this->get_post('authAmount');
		$authCurrency = $this->get_post('authCurrency');
		$currency = $this->get_post('currency');
		$shop_currency = Jigoshop_Base::get_options()->get('jigoshop_currency');
		$testMode = $this->get_post('testMode');

		$error = array();

		$order = new jigoshop_order((int)$cartId);

		// Do all checks only if transaction was processed.
		switch ($this->get_post('transStatus')) {
			case 'Y':
				// If the currency is locked.
				if ($this->fixed_currency == 'yes') {
					// All currencies should be the same.
					if ($currency != $authCurrency || $authCurrency != $shop_currency || $currency != $shop_currency) {
						$error['Locked_Currency_Error'] = sprintf(__('The currency paid in was different than the one requested. Order #: %s. Currency paid in: %s, the amount paid: %s. You should investigate further.', 'jigoshop'), $order->id, $authCurrency, $authAmount);
					}

					// All amounts should be the same
					if ($order->order_total != $amount || $authAmount != $order->order_total || $authAmount != $amount) {
						$error['Locked_Amount_Error'] = sprintf(__('There were differences in the amounts received. Order #: %s. Submitted: %s, Paid: %s, Order Total: %s. You should investigate further.', 'jigoshop'), $order->id, $amount, $authAmount, $order->order_total);
					}
				} else {
					// If currency submitted to WorldPay is the same as your store one.
					// They should always be the same even if you accept multiple currency payments.
					if ($currency != $shop_currency) {
						$error['currency'] = sprintf(__('The currency submitted to WorldPay (%s) is different than the main currency of your shop (%s). You should investigate further.', 'jigoshop'), $currency, $shop_currency);
					}

					// If multi-currency is supported, at least the amount submitted to WorldPay should be the same as the order total.
					if ($order->order_total != $amount) {
						$error['amount'] = sprintf(__('The order total (%s) is different than the amount submitted to WorldPay (%s). You should investigate further.', 'jigoshop'), $order->order_total, $amount);
					}
				}

				// Check merchant.
				if ($installation_id != $this->installation_id) {
					$error['instId'] = sprintf(__('Order was paid to installation ID: %s, which is different than the Installation ID set in your shop: %s. You should investigate further.', 'jigoshop'), $installation_id, $this->installation_id);
				}

				if ($transId == $processed_transID) {
					$error['already_processed'] = sprintf(__('Payment with the same transaction ID (%s) was already processed for this order. You should investigate further.', 'jigoshop'), $transId);
				}

				if ($this->testmode == 'no' && $testMode > 0) {
					$error['testmode'] = sprintf(__('Your shop is in Live mode, but you received a Test mode transaction. You should investigate further.', 'jigoshop'));
				}

				if (empty($error) && $testMode == 0) {
					// Payment completed as live response
					$order->add_order_note(__('WorldPay payment completed. Transaction ID: '.$transId, 'jigoshop'));
					update_post_meta($order->id, '_worldpay_processed_transID', $transId, $processed_transID);
					$order->payment_complete();
					$args = array(
						'key' => $order->order_key,
						'order' => $order->id,
					);
					$redirect_url = add_query_arg($args, get_permalink(jigoshop_get_page_id('thanks')));
				} elseif (empty($error) && $testMode > 0) {
					// Payment completed as test response
					$order->add_order_note(__('TESTMODE: WorldPay payment completed. Transaction ID: '.$transId, 'jigoshop'));
					update_post_meta($order->id, '_worldpay_processed_transID', $transId, $processed_transID);
					$order->payment_complete();
					$args = array(
						'key' => $order->order_key,
						'order' => $order->id,
					);
					$redirect_url = add_query_arg($args, get_permalink(jigoshop_get_page_id('thanks')));
				}

				if (!empty($error) && $this->receive_err_log == 'yes') {
					$info = sprintf(__('Order #%s ', 'jigoshop'), $order->id);
					$this->email_worldpay_error_logs($error, $posted, $info);
					$redirect_url = get_permalink(jigoshop_get_page_id('checkout'));
				}

				break;
			case 'C' :
				if ($testMode == 0) {
					// Payment was canceled live.
					$order->cancel_order(__('Order was canceled by customer at WorldPay.', 'jigoshop'));
				}
				if ($testMode > 0) {
					// Payment was canceled in test mode.
					$order->cancel_order(__('TESTMODE: Order was canceled by customer at WorldPay.', 'jigoshop'));
				}
				$redirect_url = $this->get_post('MC_cancel_return');
				break;
			default:
				// No action
				$redirect_url = $this->get_post('MC_cancel_return');
				break;
		}

		echo '<html><head><meta http-equiv="refresh" content="2;url='.$redirect_url.'"></head><body><WPDISPLAY ITEM=banner></body></html>';
		exit;
	}

	/**
	 *  Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *  These will be installed on the Jigoshop_Options 'Payment Gateways' tab by the parent class 'jigoshop_payment_gateway'
	 */
	protected function get_default_options()
	{
		return array(
			array(
				'name' => sprintf(__('WorldPay %s', 'jigoshop'), '<img style="vertical-align:middle;margin-top:-4px;margin-left:10px;" src="'.jigoshop::assets_url().'/assets/images/icons/worldpay.png" alt="WorldPay">'),
				'type' => 'title',
				'desc' => sprintf(__("To ensure your <strong>Preferential Jigoshop Partner Rates</strong>, please complete your %s.  Merchants who fail to register here will be put on WorldPay standard new business accounts which carry higher rates.<br/><br/>The WorldPay gateway uses a Dynamic Response URL. You <strong>must activate</strong> this in your %s with the following:<br/>1) Go to <strong>WorldPay Merchant Interface -> Installations -> Integration Setup (TEST / PRODUCTION)</strong><br/>2) Check the <strong>Payment Response enabled?</strong> checkbox. <br/>3) Copy and Paste the full tag in bold <strong>&lt;wpdisplay item=MC_callback&gt;</strong> to the <strong>Payment Response URL</strong> input field.<br/>4) Check the <strong>Enable the Shopper Response</strong> checkbox. <br/>5) Save Changes.", 'jigoshop'),
					'<a href="https://business.worldpay.com/partner/jigoshop" target="_blank">WorldPay Merchant registration here</a>',
					'<a href="https://secure.worldpay.com/sso/public/auth/login.html?serviceIdentifier=merchantadmin" target="_blank">WorldPay Merchant Account</a>'),
			),
			array(
				'name' => __('Enable WorldPay', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_worldpay_is_enabled',
				'std' => 'no',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Method Title', 'jigoshop'),
				'desc' => '',
				'tip' => __('This controls the title which the user sees during checkout and also appears as the Payment Method on final Orders.', 'jigoshop'),
				'id' => 'jigoshop_worldpay_method_title',
				'std' => __('Credit Card via WorldPay', 'jigoshop'),
				'type' => 'text'
			),
			array(
				'name' => __('Description', 'jigoshop'),
				'desc' => '',
				'tip' => __('This controls the description which the user sees during checkout.', 'jigoshop'),
				'id' => 'jigoshop_worldpay_checkout_description',
				'std' => __("When you Place your Order, you will be directed to the secured WorldPay servers to enter your credit card information.  (Your Billing Address above must match that used on your Credit Card)", 'jigoshop'),
				'type' => 'textarea'
			),
			array(
				'name' => __('Enable WorldPay Test Mode', 'jigoshop'),
				'desc' => 'Enable to make test transactions.',
				'tip' => '',
				'id' => 'jigoshop_worldpay_test_mode',
				'std' => 'no',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Installation ID', 'jigoshop'),
				'desc' => '',
				'tip' => __('Please enter your WorldPay Installation ID.', 'jigoshop'),
				'id' => 'jigoshop_worldpay_install_id',
				'std' => '',
				'type' => 'text'
			),
			array(
				'name' => __('Payment Response Password', 'jigoshop'),
				'desc' => '',
				'tip' => __("This option adds an additional security check to the Payment Response sent from WorldPay to Jigoshop, to help validate that the Payment has been sent from WorldPay<br/>1. Add a <strong>'Payment Response Password'</strong> here <br/>2. Add the password in <strong>Merchant Interface->Installations->Integration Setup (TEST or PRODUCTION)-><em>Payment Response Password</em></strong> field.<br/>Leave both empty to skip this check.", 'jigoshop'),
				'id' => 'jigoshop_worldpay_response_password',
				'std' => '',
				'type' => 'text'
			),
			array(
				'name' => __('Use MD5 SignatureFields', 'jigoshop'),
				'desc' => 'The phrase to copy: <strong>instId:cartId:amount:currency</strong>',
				'tip' => __("This option enables you to use a 'MD5 + secret word' encrypted signature before sending order details to WorldPay as a measure against unauthorized tampering.<br/>The signature will encrypt the mandatory parameters (Installation Id, Order Id, amount, currency).<br/>To enable:<br/>1) Copy the exact phrase (case sensitive, in bold) shown under the setting checkbox.<br>2) Paste into your <strong>WorldPay Merchant Interface -> Installations -> Integration Setup (TEST or PRODUCTION) -><em>SignatureFields</em></strong> input box and also enable this setting checkbox.<br>2) You must also enter a <strong>Secret Word</strong> in the next setting and save the settings.", 'jigoshop'),
				'id' => 'jigoshop_worldpay_md5',
				'std' => 'yes',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Secret Word', 'jigoshop'),
				'desc' => '',
				'tip' => __("<strong>(REQUIRED IF MD5 SIGNATURE IS ENABLED)</strong> Enter here the Secret Word you will use to hash the MD5 signature. The word needs to be up to 16 characters, known only to yourself and to WorldPay.<br/> This secret must also be entered into the <strong>WorldPay Merchant Interface -> Installations -> Integration Setup (TEST or PRODUCTION) -><em>MD5 secret for transactions</em></strong> field.", 'jigoshop'),
				'id' => 'jigoshop_worldpay_md5_secret_word',
				'std' => '',
				'type' => 'text'
			),
			array(
				'name' => __('Fixed Payment Currency', 'jigoshop'),
				'desc' => '',
				'tip' => __('This option hides the currency menu on the WorldPay page, which will fix the currency that the shopper must purchase in.<br/><b>The currency submitted is the main currency of your Shop.</b>  With this option disabled, customers will be able to select their currency and see the corrected price for the Order.', 'jigoshop'),
				'id' => 'jigoshop_worldpay_fixed_currency',
				'std' => 'no',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Receive Error Logs', 'jigoshop'),
				'desc' => '',
				'tip' => __("Do you want to receive emails for the error logs from Jigoshop security/fraud checks.", 'jigoshop'),
				'id' => 'jigoshop_worldpay_receive_security_logs',
				'std' => 'yes',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Email error logs to', 'jigoshop'),
				'desc' => '',
				'tip' => __('Email address you want to receive all error logs to. If email field is empty, the Jigoshop email address will be used.', 'jigoshop'),
				'id' => 'jigoshop_worldpay_security_logs_emailto',
				'std' => '',
				'type' => 'email'
			),
		);
	}
}
