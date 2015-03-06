<?php
/**
 * PayPal Standard Gateway
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
add_filter('jigoshop_payment_gateways', function ($methods){
	$methods[] = 'paypal';

	return $methods;
});


class paypal extends jigoshop_payment_gateway
{
	// based on PayPal currency rule: https://developer.paypal.com/docs/classic/api/currency_codes/
	private static $no_decimal_currencies = array('HUF', 'JPY', 'TWD');

	public function __construct()
	{
		parent::__construct();

		$options = Jigoshop_Base::get_options();

		$this->id = 'paypal';
		$this->icon = JIGOSHOP_URL.'/assets/images/icons/paypal.png';
		$this->has_fields = false;
		$this->enabled = $options->get('jigoshop_paypal_enabled');
		$this->title = $options->get('jigoshop_paypal_title');
		$this->email = $options->get('jigoshop_paypal_email');
		$this->description = $options->get('jigoshop_paypal_description');
		$this->force_payment = $options->get('jigoshop_paypal_force_payment');
		$this->testmode = $options->get('jigoshop_paypal_testmode');
		$this->testemail = $options->get('jigoshop_sandbox_email');
		$this->send_shipping = $options->get('jigoshop_paypal_send_shipping');
		$this->decimals = min($options->get('jigoshop_price_num_decimals'), (in_array($options->get('jigoshop_currency'), self::$no_decimal_currencies) ? 0 : 2));

		$this->liveurl = 'https://www.paypal.com/webscr';
		$this->testurl = 'https://www.sandbox.paypal.com/webscr';
		$this->notify_url = jigoshop_request_api::query_request('?js-api=JS_Gateway_Paypal', false);

		add_action('jigoshop_settings_scripts', array($this, 'admin_scripts'));
		add_action('receipt_paypal', array($this, 'receipt_page'));

		add_action('jigoshop_api_js_gateway_paypal', array($this, 'check_ipn_response'));
		add_action('init', array($this, 'legacy_ipn_response'));
	}

	public function admin_scripts()
	{
		?>
		<script type="text/javascript">
			/*<![CDATA[*/
			jQuery(function($){
				$('input#jigoshop_paypal_testmode').click(function(){
					if($(this).is(':checked')){
						$(this).parent().parent().next('tr').show();
					} else {
						$(this).parent().parent().next('tr').hide();
					}
				});
			});
			/*]]>*/
		</script>
	<?php
	}

	/**
	 * There are no payment fields for paypal, but we want to show the description if set.
	 **/
	function payment_fields()
	{
		if ($this->description) {
			echo wpautop(wptexturize($this->description));
		}
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id
	 * @return array
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
	 * Receipt_page
	 *
	 * @param int $order
	 */
	function receipt_page($order)
	{
		echo '<p>'.__('Thank you for your order, please click the button below to pay with PayPal.', 'jigoshop').'</p>';
		echo $this->generate_paypal_form($order);
	}

	/**
	 * Generate the paypal button link
	 *
	 * @param int $order_id
	 * @return string
	 */
	public function generate_paypal_form($order_id)
	{
		$order = new jigoshop_order($order_id);

		if ($this->testmode == 'yes') {
			$url = $this->testurl.'?test_ipn=1&';
		} else {
			$url = $this->liveurl.'?';
		}

		if (in_array($order->billing_country, array('US', 'CA'))) {
			$order->billing_phone = str_replace(array('(', '-', ' ', ')'), '', $order->billing_phone);
			$phone_args = array(
				'night_phone_a' => substr($order->billing_phone, 0, 3),
				'night_phone_b' => substr($order->billing_phone, 3, 3),
				'night_phone_c' => substr($order->billing_phone, 6, 4),
				'day_phone_a' => substr($order->billing_phone, 0, 3),
				'day_phone_b' => substr($order->billing_phone, 3, 3),
				'day_phone_c' => substr($order->billing_phone, 6, 4)
			);
		} else {
			$phone_args = array(
				'night_phone_b' => $order->billing_phone,
				'day_phone_b' => $order->billing_phone
			);
		}

		// filter redirect page
		$checkout_redirect = apply_filters('jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks'));

		$paypal_args = array_merge(
			array(
				'cmd' => '_cart',
				'business' => $this->testmode == 'yes' ? $this->testemail : $this->email,
				'no_note' => 1,
				'currency_code' => Jigoshop_Base::get_options()->get('jigoshop_currency'),
				'charset' => 'UTF-8',
				'rm' => 2,
				'upload' => 1,
				'return' => add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink($checkout_redirect))),
				'cancel_return' => $order->get_cancel_order_url(),
				// Order key
				'custom' => $order_id,
				// IPN
				'notify_url' => $this->notify_url,
				// Address info
				'first_name' => $order->billing_first_name,
				'last_name' => $order->billing_last_name,
				'company' => $order->billing_company,
				'address1' => $order->billing_address_1,
				'address2' => $order->billing_address_2,
				'city' => $order->billing_city,
				'state' => $order->billing_state,
				'zip' => $order->billing_postcode,
				'country' => $order->billing_country,
				'email' => $order->billing_email,
				// Payment Info
				'invoice' => $order->get_order_number(),
				'amount' => number_format((float)$order->order_total, $this->decimals),
				//BN code
				'bn' => 'Jigoshop_SP'
			),
			$phone_args
		);

		if ($this->send_shipping == 'yes') {
			$paypal_args['no_shipping'] = 1;
			$paypal_args['address_override'] = 1;
			$paypal_args['first_name'] = $order->shipping_first_name;
			$paypal_args['last_name'] = $order->shipping_last_name;
			$paypal_args['address1'] = $order->shipping_address_1;
			$paypal_args['address2'] = $order->shipping_address_2;
			$paypal_args['city'] = $order->shipping_city;
			$paypal_args['state'] = $order->shipping_state;
			$paypal_args['zip'] = $order->shipping_postcode;
			$paypal_args['country'] = $order->shipping_country;
			// PayPal counts Puerto Rico as a US Territory, won't allow payment without it
			if ($paypal_args['country'] == 'PR') {
				$paypal_args['country'] = 'US';
				$paypal_args['state'] = 'PR';
			}
		} else {
			$paypal_args['no_shipping'] = 1;
			$paypal_args['address_override'] = 0;
		}

		// If prices include tax, send the whole order as a single item
		if (Jigoshop_Base::get_options()->get('jigoshop_prices_include_tax') == 'yes') {
			// Discount
			$paypal_args['discount_amount_cart'] = number_format((float)$order->order_discount, $this->decimals);

			// Don't pass items - PayPal breaks tax due to catalog prices include tax.
			// PayPal has no option for tax inclusive pricing.
			// Pass 1 item for the order items overall
			$item_names = array();

			foreach ($order->items as $item) {
				$_product = $order->get_product_from_item($item);
				$title = $_product->get_title();

				//if variation, insert variation details into product title
				if ($_product instanceof jigoshop_product_variation) {
					$title .= ' ('.jigoshop_get_formatted_variation($_product, $item['variation'], true).')';
				}

				$item_names[] = $title.' x '.$item['qty'];
			}

			$paypal_args['item_name_1'] = sprintf(__('Order %s', 'jigoshop'), $order->get_order_number()).' - '.implode(', ', $item_names);
			$paypal_args['quantity_1'] = 1;
			$paypal_args['amount_1'] = number_format($order->order_total - $order->order_shipping - $order->order_shipping_tax + $order->order_discount, $this->decimals, '.', '');

			if (($order->order_shipping + $order->order_shipping_tax) > 0) {
				$paypal_args['item_name_2'] = __('Shipping cost', 'jigoshop');
				$paypal_args['quantity_2'] = '1';
				$paypal_args['amount_2'] = number_format($order->order_shipping + $order->order_shipping_tax, $this->decimals, '.', '');
			}
		} else {
			// Cart Contents
			$item_loop = 0;
			foreach ($order->items as $item) {
				$_product = $order->get_product_from_item($item);

				if ($_product->exists() && $item['qty']) {
					$item_loop++;
					$title = $_product->get_title();

					//if variation, insert variation details into product title
					if ($_product instanceof jigoshop_product_variation) {
						$title .= ' ('.jigoshop_get_formatted_variation($_product, $item['variation'], true).')';
					}

					$paypal_args['item_name_'.$item_loop] = $title;
					$paypal_args['quantity_'.$item_loop] = $item['qty'];
					$paypal_args['amount_'.$item_loop] = number_format(apply_filters('jigoshop_paypal_adjust_item_price', $item['cost'], $item, 10, 2), $this->decimals); //Apparently, Paypal did not like "28.4525" as the amount. Changing that to "28.45" fixed the issue.
				}
			}

			// Shipping Cost
			if (jigoshop_shipping::is_enabled() && $order->order_shipping > 0) {
				$item_loop++;
				$paypal_args['item_name_'.$item_loop] = __('Shipping cost', 'jigoshop');
				$paypal_args['quantity_'.$item_loop] = '1';
				$paypal_args['amount_'.$item_loop] = number_format((float)$order->order_shipping, $this->decimals);
			}

			$paypal_args['tax'] = $order->get_total_tax(false, false); // no currency sign or pricing options for separators
			$paypal_args['tax_cart'] = $order->get_total_tax(false, false); // no currency sign or pricing options for separators
			$paypal_args['discount_amount_cart'] = $order->order_discount;

			if ($this->force_payment == 'yes') {
				$sum = 0;
				for ($i = 1; $i < $item_loop; $i++) {
					$sum += $paypal_args['amount_'.$i];
				}

				$item_loop++;
				if ($sum == 0 || ($order->order_discount && $sum - $order->order_discount == 0)) {
					$paypal_args['item_name_'.$item_loop] = __('Force payment on free', 'jigoshop');
					$paypal_args['quantity_'.$item_loop] = '1';
					$paypal_args['amount_'.$item_loop] = 0.01; // force payment
				}
			}
		}

		$paypal_args = apply_filters('jigoshop_paypal_args', $paypal_args);

		return jigoshop_render_result('gateways/paypal', array(
			'url' => $url,
			'fields' => $paypal_args,
		));
	}

	/**
	 * Check for Legacy PayPal IPN Response
	 */
	function legacy_ipn_response()
	{
		if (!empty($_GET['paypalListener']) && $_GET['paypalListener'] == 'paypal_standard_IPN') {
			do_action('jigoshop_api_js_gateway_paypal');
		}
	}

	/**
	 * Check for PayPal IPN Response
	 */
	function check_ipn_response()
	{
		@ob_clean();

		if (!empty($_POST) && $this->check_ipn_request_is_valid()) {
			header('HTTP/1.1 200 OK');
			$this->successful_request($_POST);
		} else {
			wp_die('PayPal IPN Request Failure');
		}
	}

	/**
	 * Check PayPal IPN validity
	 */
	function check_ipn_request_is_valid()
	{
		$values = (array)stripslashes_deep($_POST);
		$values['cmd'] = '_notify-validate';

		// Send back post vars to PayPal
		$params = array(
			'body' => $values,
			'sslverify' => false,
			'timeout' => 30,
			'user-agent' => 'Jigoshop/'.JIGOSHOP_VERSION,
		);

		// Get url
		if ($this->testmode == 'yes') {
			$url = $this->testurl;
		} else {
			$url = $this->liveurl;
		}

		// Post back to get a response
		$response = wp_remote_post($url, $params);

		// check to see if the request was valid
		if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && (strcmp($response['body'], "VERIFIED") == 0)) {
			return true;
		}

		jigoshop_log('Received invalid response from PayPal!');
		jigoshop_log('IPN Response: '.print_r($response, true));

		if (is_wp_error($response)) {
			jigoshop_log('PayPal IPN WordPress Error message: '.$response->get_error_message());
		}

		return false;
	}

	/**
	 * Successful payment processing
	 *
	 * @param array $posted
	 */
	function successful_request($posted)
	{
		$posted = stripslashes_deep($posted);

		// 'custom' holds post ID (Order ID)
		if (!empty($posted['custom']) && !empty($posted['txn_type']) && !empty($posted['invoice'])) {
			$accepted_types = array(
				'cart',
				'instant',
				'express_checkout',
				'web_accept',
				'masspay',
				'send_money',
				'subscr_payment'
			);
			$order = new jigoshop_order((int)$posted['custom']);

			// Sandbox fix
			if (isset($posted['test_ipn']) && $posted['test_ipn'] == 1 && strtolower($posted['payment_status']) == 'pending') {
				$posted['payment_status'] = 'completed';
			}

			$merchant = ($this->testmode == 'no') ? $this->email : $this->testemail;

			if ($order->status !== 'completed') {
				// We are here so lets check status and do actions
				switch (strtolower($posted['payment_status'])) {
					case 'completed' :
						if (!in_array(strtolower($posted['txn_type']), $accepted_types)) {
							// Put this order on-hold for manual checking
							$order->update_status('on-hold', sprintf(__('PayPal Validation Error: Unknown "txn_type" of "%s" for Order ID: %s.', 'jigoshop'), $posted['txn_type'], $posted['custom']));
							exit;
						}

						if ($order->get_order_number() !== $posted['invoice']) {
							// Put this order on-hold for manual checking
							$order->update_status('on-hold', sprintf(__('PayPal Validation Error: Order Invoice Number does NOT match PayPal posted invoice (%s) for Order ID: .', 'jigoshop'), $posted['invoice'], $posted['custom']));
							exit;
						}

						// Validate Amount
						if (number_format((float)$order->order_total, $this->decimals, '.', '') != $posted['mc_gross']) {
							// Put this order on-hold for manual checking
							$order->update_status('on-hold', sprintf(__('PayPal Validation Error: Payment amounts do not match initial order (gross %s).', 'jigoshop'), $posted['mc_gross']));
							exit;
						}

						if (strcasecmp(trim($posted['business']), trim($merchant)) != 0) {
							// Put this order on-hold for manual checking
							$order->update_status('on-hold', sprintf(__('PayPal Validation Error: Payment Merchant email received does not match PayPal Gateway settings. (%s)', 'jigoshop'), $posted['business']));
							exit;
						}

						if (!in_array($posted['mc_currency'], apply_filters('jigoshop_multi_currencies_available', array(Jigoshop_Base::get_options()->get('jigoshop_currency'))))) {
							// Put this order on-hold for manual checking
							$order->update_status('on-hold', sprintf(__('PayPal Validation Error: Payment currency received (%s) does not match Shop currency.', 'jigoshop'), $posted['mc_currency']));
							exit;
						}

						$order->add_order_note(__('PayPal Standard payment completed', 'jigoshop'));
						$order->payment_complete();

						jigoshop_log('PAYPAL: IPN payment completed for Order ID: '.$posted['custom']);
						break;
					case 'denied' :
					case 'expired' :
					case 'failed' :
					case 'voided' :
						// Failed order
						$order->update_status('failed', sprintf(__('Payment %s via IPN.', 'jigoshop'), strtolower($posted['payment_status'])));
						jigoshop_log("PAYPAL: failed order with status = ".strtolower($posted['payment_status'])."for Order ID: ".$posted['custom']);
						break;
					case 'refunded' :
					case 'reversed' :
					case 'chargeback' :
						jigoshop_log("PAYPAL: payment status type - '".$posted['payment_status']."' - not supported for Order ID: ".$posted['custom']);
						break;
				}
			}

			exit;
		} else {
			jigoshop_log("PAYPAL: function 'successful_request' -- empty initial required values -- EXITING!\n'posted' values = ".print_r($posted, true));
		}
	}

	public function process_gateway($subtotal, $shipping_total, $discount = 0)
	{
		if (!(isset($subtotal) && isset($shipping_total))) {
			return false;
		}

		// check for free (which is the sum of all products and shipping = 0) Tax doesn't count unless prices
		// include tax
		if (($subtotal <= 0 && $shipping_total <= 0) || (($subtotal + $shipping_total) - $discount) == 0) {
			// true when force payment = 'yes'
			return $this->force_payment === 'yes';
		} else if (($subtotal + $shipping_total) - $discount < 0) {
			// don't process PayPal if the sum of the product prices and shipping total is less than the discount
			// as it cannot handle this scenario
			return false;
		}

		return true;
	}

	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 * These will be installed on the Jigoshop_Options 'Payment Gateways' tab by the parent class 'jigoshop_payment_gateway'
	 */
	protected function get_default_options()
	{
		return array(
			array(
				'name' => sprintf(__('PayPal Standard %s', 'jigoshop'), '<img style="vertical-align:middle;margin-top:-4px;margin-left:10px;" src="'.JIGOSHOP_URL.'/assets/images/icons/paypal.png" alt="PayPal">'),
				'type' => 'title',
				'desc' => __('PayPal Standard works by sending the user to <a href="https://www.paypal.com/">PayPal</a> to enter their payment information.', 'jigoshop')
			),
			array(
				'name' => __('Enable PayPal Standard', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_paypal_enabled',
				'std' => 'yes',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Method Title', 'jigoshop'),
				'desc' => '',
				'tip' => __('This controls the title which the user sees during checkout.', 'jigoshop'),
				'id' => 'jigoshop_paypal_title',
				'std' => __('PayPal', 'jigoshop'),
				'type' => 'text'
			),
			array(
				'name' => __('Customer Message', 'jigoshop'),
				'desc' => '',
				'tip' => __('This controls the description which the user sees during checkout.', 'jigoshop'),
				'id' => 'jigoshop_paypal_description',
				'std' => __("Pay via PayPal; you can pay with your credit card if you don't have a PayPal account", 'jigoshop'),
				'type' => 'longtext'
			),
			array(
				'name' => __('PayPal email address', 'jigoshop'),
				'desc' => '',
				'tip' => __('Please enter your PayPal email address; this is needed in order to take payment!', 'jigoshop'),
				'id' => 'jigoshop_paypal_email',
				'std' => '',
				'type' => 'email'
			),
			array(
				'name' => __('Send shipping details to PayPal', 'jigoshop'),
				'desc' => '',
				'tip' => __('If your checkout page does not ask for shipping details, or if you do not want to send shipping information to PayPal, set this option to no. If you enable this option PayPal may restrict where things can be sent, and will prevent some orders going through for your protection.', 'jigoshop'),
				'id' => 'jigoshop_paypal_send_shipping',
				'std' => 'no',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Force payment when free', 'jigoshop'),
				'desc' => '',
				'tip' => __('If product totals are free and shipping is also free (excluding taxes), this will force 0.01 to allow paypal to process payment. Shop owner is responsible for refunding customer.', 'jigoshop'),
				'id' => 'jigoshop_paypal_force_payment',
				'std' => 'no',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Enable PayPal sandbox', 'jigoshop'),
				'desc' => __('Turn on to enable the PayPal sandbox for testing.  Visit <a href="http://developer.paypal.com/">http://developer.paypal.com/</a> for more information and to register a merchant and customer testing account.', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_paypal_testmode',
				'std' => 'no',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Sandbox email address', 'jigoshop'),
				'desc' => '',
				'tip' => __('Please enter your Sandbox Merchant email address for use as your sandbox storefront if you have enabled the PayPal sandbox.', 'jigoshop'),
				'id' => 'jigoshop_sandbox_email',
				'std' => '',
				'type' => 'midtext'
			),
		);
	}
}
