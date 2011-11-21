<?php

/**
 * PayFast Payment Gateway
 * 
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Checkout
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
class payfast extends jigoshop_payment_gateway {

    public function __construct() {
        global $jigoshop;

        $this->id = 'payfast';
        $this->method_title = __('PayFast', 'jigoshop');
        $this->icon = jigoshop::plugin_url() . '/assets/images/icons/payfast.png';
        $this->has_fields = false;
        $this->enabled = get_option('jigoshop_payfast_enabled');
        $this->title = get_option('jigoshop_payfast_title');
        $this->description = get_option('jigoshop_payfast_description');
        $this->merchant_id = get_option('jigoshop_payfast_merchant_id');
        $this->merchant_key = get_option('jigoshop_payfast_merchant_key');
        $this->auth_token = get_option('jigoshop_payfast_auth_token');
        $this->test_mode = get_option('jigoshop_payfast_test_mode');
        $this->url = 'https://www.payfast.co.za/eng/process';
        $this->validate_url = 'https://www.payfast.co.za/eng/query/validate';

        // Setup available countries.
        $this->available_countries = array('ZA');

        // Setup available currency codes.
        $this->available_currencies = array('ZAR');

        // Load the settings.
        // Setup constants.
        $this->setup_constants();

        // Setup the test data, if in test mode.
        if (get_option('jigoshop_payfast_test_mode') == 'yes') {
            $this->url = 'https://sandbox.payfast.co.za/eng/process';
            $this->validate_url = 'https://sandbox.payfast.co.za/eng/query/validate';
        }

        add_action('init', array(&$this, 'check_pdt_response'));
        add_action('valid-payfast-pdt-request', array(&$this, 'successful_request'));
        add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
        add_action('receipt_payfast', array(&$this, 'receipt_page'));

        add_filter('jigoshop_currencies', array(&$this, 'add_currency'));
        add_filter('jigoshop_currency_symbol', array(&$this, 'add_currency_symbol'));

        add_option('jigoshop_payfast_enabled', 'yes');
        add_option('jigoshop_payfast_title', 'PayFast');
        add_option('jigoshop_payfast_description', 'Pay via PayFast.');
        add_option('jigoshop_payfast_test_mode', 'no');

        // Check if the base currency supports this gateway.
        if (!$this->is_valid_for_use()) {
            $this->enabled = false;
        }
    }

// End init_form_fields()

    /**
     * Get the plugin URL
     *
     * @since 1.0.0
     */

    /**
     * add_currency()
     *
     * Add the custom currencies to WooCommerce.
     *
     * @since 1.0.0
     */
    function add_currency($currencies) {
        $currencies['ZAR'] = __('South African Rand (R)', 'jigoshop');
        return $currencies;
    }

// End add_currency()

    /**
     * add_currency_symbol()
     *
     * Add the custom currency symbols to WooCommerce.
     *
     * @since 1.0.0
     */
    function add_currency_symbol($symbol) {
        $currency = get_option('jigoshop_currency');
        switch ($currency) {
            case 'ZAR': $symbol = 'R';
                break;
        }
        return $symbol;
    }

// End add_currency_symbol()

    /**
     * is_valid_for_use()
     *
     * Check if this gateway is enabled and available in the base currency being traded with.
     *
     * @since 1.0.0
     */
    function is_valid_for_use() {
        $is_available = false;
        $user_currency = get_option('jigoshop_currency');
        $is_available_currency = in_array($user_currency, $this->available_currencies);

        if ($is_available_currency && $this->enabled == 'yes') {
            if (get_option('jigoshop_payfast_merchant_id') != '' && get_option('jigoshop_payfast_merchant_key') != '') {
                $is_available = true;
            } elseif (get_option('jigoshop_payfast_test_mode') == 'yes') {
                $is_available = true;
            }
        }

        return $is_available;
    }

// End is_valid_for_use()

    /**
     * get_country_code()
     *
     * Get the users country either from their order, or from their customer data
     *
     * @since 1.0.0
     */
    function get_country_code() {
        global $jigoshop;

        $base_country = $jigoshop->countries->get_option('jigoshop_default_country');

        return $base_country;
    }

// End get_country_code()

    /**
     * Admin Panel Options 
     * - Options for bits like 'title' and availability on a country-by-country basis
     *
     * @since 1.0.0
     */
    public function admin_options() {
        // Make sure to empty the log file if not in test mode.
//        if (get_option('jigoshop_payfast_test_mode') != 'yes') {
//            $this->log('');
//            $this->log('', true);
//        }

        if ('ZA' == get_option('jigoshop_default_country')) :
            ?>
            <thead><tr><th scope="col" width="200px"><?php _e('PayFast', 'jigoshop'); ?></th><th scope="col" class="desc"><?php printf(__('PayFast works by sending the user to %sPayFast%s to enter their payment information.', 'jigoshop'), '<a href="http://payfast.co.za/">', '</a>'); ?></th></tr></thead>
            <tr>
                <td class="titledesc"><?php _e('Enable PayFast', 'jigoshop') ?>:</td>
                <td class="forminp">
                    <select name="jigoshop_payfast_enabled" id="jigoshop_payfast_enabled" style="min-width:100px;">
                        <option value="yes" <?php if (get_option('jigoshop_payfast_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
                        <option value="no" <?php if (get_option('jigoshop_payfast_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
                <td class="forminp">
                    <input class="input-text" type="text" name="jigoshop_payfast_title" id="jigoshop_payfast_title" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_payfast_title')) echo $value; ?>" />
                </td>
            </tr>
            <tr>
                <td class="titledesc"><a href="#" tip="<?php _e('This controls the description which the user sees during checkout.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Description', 'jigoshop') ?>:</td>
                <td class="forminp">
                    <input class="input-text wide-input" type="text" name="jigoshop_payfast_description" id="jigoshop_payfast_description" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_payfast_description')) echo $value; ?>" />
                </td>
            </tr>
            <tr>
                <td class="titledesc"><a href="#" tip="<?php _e('This is the merchant ID, received from PayFast.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Merchant ID', 'jigoshop') ?>:</td>
                <td class="forminp">
                    <input class="input-text" type="text" name="jigoshop_payfast_merchant_id" id="jigoshop_payfast_merchant_id" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_payfast_merchant_id')) echo $value; ?>" />
                </td>
            </tr>
            <tr>
                <td class="titledesc"><a href="#" tip="<?php _e('This is the merchant key, received from PayFast.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Merchant Key', 'jigoshop') ?>:</td>
                <td class="forminp">
                    <input class="input-text" type="text" name="jigoshop_payfast_merchant_key" id="jigoshop_payfast_merchant_key" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_payfast_merchant_key')) echo $value; ?>" />
                </td>
            </tr>
            <tr>
                <td class="titledesc"><a href="#" tip="<?php _e('This is the authentication token, received from PayFast.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Authentication Token', 'jigoshop') ?>:</td>
                <td class="forminp">
                    <input class="input-text" type="text" name="jigoshop_payfast_auth_token" id="jigoshop_payfast_auth_token" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_payfast_auth_token')) echo $value; ?>" />
                </td>
            </tr>
            <tr>
                <td class="titledesc"><a href="#" tip="<?php _e('Place the payment gateway in development mode.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('PayFast Sandbox', 'jigoshop') ?>:</td>
                <td class="forminp">
                    <select name="jigoshop_payfast_test_mode" id="jigoshop_payfast_test_mode" style="min-width:100px;">
                        <option value="yes" <?php if (get_option('jigoshop_payfast_test_mode') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
                        <option value="no" <?php if (get_option('jigoshop_payfast_test_mode') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
                    </select>
                </td>
            </tr>
        <?php else : ?>
            <div class="inline error"><p><strong><?php _e('Gateway Disabled', 'jigoshop'); ?></strong> <?php echo sprintf(__('Choose South African Rands as your store currency in <a href="%s">Pricing Options</a> to enable the PayFast Gateway.', 'jigoshop'), '#tab3'); ?></p></div>
        <?php
        endif; // End check currency
    }

// End admin_options()

    public function process_admin_options() {
        if (!empty($_POST['jigoshop_payfast_enabled'])) {
            update_option('jigoshop_payfast_enabled', jigowatt_clean($_POST['jigoshop_payfast_enabled']));
        } else {
            @delete_option('jigoshop_payfast_enabled');
        }

        if (!empty($_POST['jigoshop_payfast_title'])) {
            update_option('jigoshop_payfast_title', jigowatt_clean($_POST['jigoshop_payfast_title']));
        } else {
            @delete_option('jigoshop_payfast_title');
        }

        if (!empty($_POST['jigoshop_payfast_description'])) {
            update_option('jigoshop_payfast_description', jigowatt_clean($_POST['jigoshop_payfast_description']));
        } else {
            @delete_option('jigoshop_payfast_description');
        }

        if (!empty($_POST['jigoshop_payfast_merchant_id'])) {
            update_option('jigoshop_payfast_merchant_id', jigowatt_clean($_POST['jigoshop_payfast_merchant_id']));
        } else {
            @delete_option('jigoshop_payfast_merchant_id');
        }

        if (!empty($_POST['jigoshop_payfast_merchant_key'])) {
            update_option('jigoshop_payfast_merchant_key', jigowatt_clean($_POST['jigoshop_payfast_merchant_key']));
        } else {
            @delete_option('jigoshop_payfast_merchant_key');
        }
        
        if (!empty($_POST['jigoshop_payfast_auth_token'])) {
            update_option('jigoshop_payfast_auth_token', jigowatt_clean($_POST['jigoshop_payfast_auth_token']));
        } else {
            @delete_option('jigoshop_payfast_auth_token');
        }

        if (!empty($_POST['jigoshop_payfast_test_mode'])) {
            update_option('jigoshop_payfast_test_mode', jigowatt_clean($_POST['jigoshop_payfast_test_mode']));
        } else {
            @delete_option('jigoshop_payfast_test_mode');
        }
    }

    /**
     * There are no payment fields for PayFast, but we want to show the description if set.
     *
     * @since 1.0.0
     */
    function payment_fields() {
        $desc = get_option('jigoshop_payfast_description');
        if (!empty($desc)) {
            echo wpautop(wptexturize(get_option('jigoshop_payfast_description')));
        }
    }

// End payment_fields()

    /**
     * Generate the PayFast button link.
     *
     * @since 1.0.0
     */
    public function generate_payfast_form($order_id) {
        $order = &new jigoshop_order($order_id);

        $shipping_name = explode(' ', $order->shipping_method);

        // Construct variables for post
        $this->data_to_send = array(
            // Merchant details
            'merchant_id' => get_option('jigoshop_payfast_merchant_id'),
            'merchant_key' => get_option('jigoshop_payfast_merchant_key'),
            'return_url' => add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(get_option('jigoshop_thanks_page_id')))),
            'cancel_url' => jigoshop::nonce_url('cancel_order', add_query_arg('cancel_order', 'true', add_query_arg('order', $order->order_key, add_query_arg('order_id', $order->id, trailingslashit(get_bloginfo('wpurl')))))),
            // Billing details
            'name_first' => $order->billing_first_name,
            'name_last' => $order->billing_last_name,
            // 'email_address' => $order->billing_email, 
            // Item details
            'm_payment_id' => $order->id,
            'amount' => $order->order_total,
            'item_name' => get_bloginfo('name') . ' purchase, Order #' . $order->id,
            'item_description' => sprintf(__('New order from %s', 'jigoshop'), get_bloginfo('name')),
            // Custom strings
            'custom_str1' => $order->order_key
        );

        // Override merchant_id and merchant_key if the gateway is in test mode.
        if (get_option('jigoshop_payfast_test_mode') == 'yes') {
            $this->data_to_send['merchant_id'] = '10000100';
            $this->data_to_send['merchant_key'] = '46f0cd694581a';
        }

        $payfast_args_array = array();

        foreach ($this->data_to_send as $key => $value) {
            $payfast_args_array[] = '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
        }

        return '<form action="' . $this->url . '" method="post" id="payfast_payment_form">
				' . implode('', $payfast_args_array) . '
				<input type="submit" class="button-alt" id="submit_payfast_payment_form" value="' . __('Pay via PayFast', 'jigoshop') . '" /> <a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Cancel order &amp; restore cart', 'jigoshop') . '</a>
				<script type="text/javascript">
					jQuery(function(){
						jQuery("body").block(
							{ 
								message: "<img src=\"' . jigoshop::plugin_url() . '/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" />' . __('Thank you for your order. We are now redirecting you to PayFast to make payment.', 'jigoshop') . '", 
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
						jQuery( "#submit_payfast_payment_form" ).click();
					});
				</script>
			</form>';
    }

// End generate_payfast_form()

    /**
     * Process the payment and return the result.
     *
     * @since 1.0.0
     */
    function process_payment($order_id) {

        $order = &new jigoshop_order($order_id);

        return array(
            'result' => 'success',
            'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(get_option('jigoshop_pay_page_id'))))
        );
    }

    /**
     * Reciept page.
     *
     * Display text and a button to direct the user to PayFast.
     *
     * @since 1.0.0
     */
    function receipt_page($order) {
        echo '<p>' . __('Thank you for your order, please click the button below to pay with PayFast.', 'jigoshop') . '</p>';

        echo $this->generate_payfast_form($order);
    }

// End receipt_page()

    /**
     * Check PayFast ITN response.
     *
     * @since 1.0.0
     */
    function check_pdt_response() {
        $pm_token = isset($_GET['pt']) ? $_GET['pt'] : null;

        if ($pm_token != null) {
            do_action('valid-payfast-pdt-request', $_GET);
        }
    }

// End check_pdt_response()

    function check_transaction_status($pmtToken) {
        // Variable Initialization
        $error = false;
        $authToken = ($this->test_mode) ? '0a1e2e10-03a7-4928-af8a-fbdfdfe31d43' : get_option('jigoshop_payfast_auth_token');
        $req = 'pt=' . $pmtToken . '&at=' . $authToken;
        $data = array();
        $host = ($this->test_mode) ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
        
        //// Connect to server
        if (!$error) {
            // Construct Header
            $header = "POST /eng/query/fetch HTTP/1.0\r\n";
            $header .= 'Host: ' . $host . "\r\n";
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $header .= 'Content-Length: ' . strlen($req) . "\r\n\r\n";

            // Connect to server
            $socket = fsockopen('ssl://' . $host, 443, $errno, $errstr, 10);

            if (!$socket) {
                $error = true;
                print( 'errno = ' . $errno . ', errstr = ' . $errstr);
            }
        }
        
        //// Get data from server
        if (!$error) {
            // Send command to server
            fputs($socket, $header . $req);

            // Read the response from the server
            $res = '';
            $headerDone = false;

            while (!feof($socket)) {
                $line = fgets($socket, 1024);

                // Check if we are finished reading the header yet
                if (strcmp($line, "\r\n") == 0) {
                    // read the header
                    $headerDone = true;
                }
                // If header has been processed
                else if ($headerDone) {
                    // Read the main response
                    $res .= $line;
                }
            }

            // Parse the returned data
            $lines = explode("\n", $res);
        }

        //// Interpret the response from server
        if (!$error) {
            $result = trim($lines[0]);

            // If the transaction was successful
            if (strcmp($result, 'SUCCESS') == 0) {
                // Process the reponse into an associative array of data
                for ($i = 1; $i < count($lines); $i++) {
                    list( $key, $val ) = explode("=", $lines[$i]);
                    $data[urldecode($key)] = stripslashes(urldecode($val));
                }
            }
            // If the transaction was NOT successful
            else if (strcmp($result, 'FAIL') == 0) {
                // Log for investigation
                $error = true;
                // 
            }
        }

        // Close socket if successfully opened
        if ($socket)
            fclose($socket);
        
        $data['no_error'] = (bool) $error;
        
        return $data;
    }

    /**
     * Successful Payment!
     *
     * @since 1.0.0
     */
    function successful_request($pmt_token) {
        if ($pmt_token === null) {
            return false;
        }

        $order_id = (int) $_GET['order'];
        $order_key = $_GET['key'];
        $order = new jigoshop_order($order_id);
        
        if ($order->order_key !== $order_key) {
            exit;
        }
        
        $transaction_result = $this->check_transaction_status($_GET['pt']);
        
        if ($transaction_result['no_error'] == false) {
            return false;
        }
        
        foreach ($transaction_result as $key => $value) {
            $transaction_result[$key] = trim($value);
        }
        
        if ($order->status !== 'completed') {
            // We are here so lets check status and do actions
            switch (strtolower($transaction_result['payment_status'])) {
                case 'complete' :
                    // Payment completed
                    $order->add_order_note(__('PDT payment completed', 'jigoshop'));
                    $order->payment_complete();
                    break;
                case 'denied' :
                case 'expired' :
                case 'failed' :
                case 'voided' :
                    // Failed order
                    $order->update_status('failed', sprintf(__('Payment %s via PDT.', 'jigoshop'), strtolower($transaction_result['payment_status'])));
                    break;
                default:
                    // Hold order
                    $order->update_status('on-hold', sprintf(__('Payment %s via PDT.', 'jigoshop'), strtolower($transaction_result['payment_status'])));
                    break;
            } // End SWITCH Statement

            wp_redirect(add_query_arg('key', $order->order_key, add_query_arg('order', $order->id, get_permalink(get_option('jigoshop_thanks_page_id')))));
            exit;
        } // End IF Statement

        exit;
    }

    /**
     * Setup constants.
     *
     * Setup common values and messages used by the PayFast gateway.
     *
     * @since 1.0.0
     */
    function setup_constants() {
        //// Create user agent string
        // User agent constituents (for cURL)
        define('PF_SOFTWARE_NAME', 'jigoshop');
        define('PF_SOFTWARE_VER', '1.0.0');
        define('PF_MODULE_NAME', 'jigoshop-PayFast');
        define('PF_MODULE_VER', '1.0.0');

        // Features
        // - PHP
        $pfFeatures = 'PHP ' . phpversion() . ';';

        // - cURL
        if (in_array('curl', get_loaded_extensions())) {
            define('PF_CURL', '');
            $pfVersion = curl_version();
            $pfFeatures .= ' curl ' . $pfVersion['version'] . ';';
        }
        else
            $pfFeatures .= ' nocurl;';

        // Create user agrent
        define('PF_USER_AGENT', PF_SOFTWARE_NAME . '/' . PF_SOFTWARE_VER . ' (' . trim($pfFeatures) . ') ' . PF_MODULE_NAME . '/' . PF_MODULE_VER);

        // General Defines
        define('PF_TIMEOUT', 15);
        define('PF_EPSILON', 0.01);

        // Messages
        // Error
        define('PF_ERR_AMOUNT_MISMATCH', __('Amount mismatch', 'jigoshop'));
        define('PF_ERR_BAD_ACCESS', __('Bad access of page', 'jigoshop'));
        define('PF_ERR_BAD_SOURCE_IP', __('Bad source IP address', 'jigoshop'));
        define('PF_ERR_CONNECT_FAILED', __('Failed to connect to PayFast', 'jigoshop'));
        define('PF_ERR_INVALID_SIGNATURE', __('Security signature mismatch', 'jigoshop'));
        define('PF_ERR_MERCHANT_ID_MISMATCH', __('Merchant ID mismatch', 'jigoshop'));
        define('PF_ERR_NO_SESSION', __('No saved session found for ITN transaction', 'jigoshop'));
        define('PF_ERR_ORDER_ID_MISSING_URL', __('Order ID not present in URL', 'jigoshop'));
        define('PF_ERR_ORDER_ID_MISMATCH', __('Order ID mismatch', 'jigoshop'));
        define('PF_ERR_ORDER_INVALID', __('This order ID is invalid', 'jigoshop'));
        define('PF_ERR_ORDER_NUMBER_MISMATCH', __('Order Number mismatch', 'jigoshop'));
        define('PF_ERR_ORDER_PROCESSED', __('This order has already been processed', 'jigoshop'));
        define('PF_ERR_PDT_FAIL', __('PDT query failed', 'jigoshop'));
        define('PF_ERR_PDT_TOKEN_MISSING', __('PDT token not present in URL', 'jigoshop'));
        define('PF_ERR_SESSIONID_MISMATCH', __('Session ID mismatch', 'jigoshop'));
        define('PF_ERR_UNKNOWN', __('Unkown error occurred', 'jigoshop'));

        // General
        define('PF_MSG_OK', __('Payment was successful', 'jigoshop'));
        define('PF_MSG_FAILED', __('Payment has failed', 'jigoshop'));
        define('PF_MSG_PENDING', __('The payment is pending. Please note, you will receive another Instant', 'jigoshop') .
                __(' Transaction Notification when the payment status changes to', 'jigoshop') .
                __(' "Completed", or "Failed"', 'jigoshop'));
    }

// End setup_constants()

    /**
     * log()
     *
     * Log system processes.
     *
     * @since 1.0.0
     */
    function log($message, $close = false) {
        if (( get_option('jigoshop_payfast_test_mode') != 'yes' && !is_admin())) {
            return;
        }

        static $fh = 0;

        if ($close) {
            fclose($fh);
        } else {
            // If file doesn't exist, create it
            if (!$fh) {
                $pathinfo = pathinfo(__FILE__);
                $dir = str_replace('/classes', '/logs', $pathinfo['dirname']);
                $fh = fopen($dir . '/payfast.log', 'w');
            }

            // If file was successfully created
            if ($fh) {
                $line = $message . "\n";

                fwrite($fh, $line);
            }
        }
    }

// End log()

    /**
     * validate_signature()
     *
     * Validate the signature against the returned data.
     *
     * @param array $data
     * @param string $signature
     * @since 1.0.0
     */
    function validate_signature($data, $signature) {

        $result = ( $data['signature'] == $signature );

//        $this->log('Signature = ' . ( $result ? 'valid' : 'invalid' ));

        return( $result );
    }

// End validate_signature()

    /**
     * validate_ip()
     *
     * Validate the IP address to make sure it's coming from PayFast.
     *
     * @param array $data
     * @since 1.0.0
     */
    function validate_ip($sourceIP) {
        // Variable initialization
        $validHosts = array(
            'www.payfast.co.za',
            'sandbox.payfast.co.za',
            'w1w.payfast.co.za',
            'w2w.payfast.co.za',
        );

        $validIps = array();

        foreach ($validHosts as $pfHostname) {
            $ips = gethostbynamel($pfHostname);

            if ($ips !== false)
                $validIps = array_merge($validIps, $ips);
        }

        // Remove duplicates
        $validIps = array_unique($validIps);

//        $this->log("Valid IPs:\n" . print_r($validIps, true));

        if (in_array($sourceIP, $validIps)) {
            return( true );
        } else {
            return( false );
        }
    }

// End validate_ip()

    /**
     * validate_response_data()
     * 	
     * @param $pfHost String Hostname to use 
     * @param $pfParamString String Parameter string to send
     * @param $proxy String Address of proxy to use or NULL if no proxy
     * @since 1.0.0
     */
    function validate_response_data($pfParamString, $pfProxy = null) {
//        $this->log('Host = ' . $this->validate_url);
//        $this->log('Params = ' . print_r($pfParamString, true));

        if (!is_array($pfParamString)) {
            return false;
        }

        $post_data = $pfParamString;

        $url = $this->validate_url;

        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => $post_data,
            'timeout' => 70,
            'sslverify' => true
                ));

        if (is_wp_error($response))
            throw new Exception(__('There was a problem connecting to the payment gateway.', 'jigoshop'));

        if (empty($response['body']))
            throw new Exception(__('Empty PayFast response.', 'jigoshop'));

        parse_str($response['body'], $parsed_response);

        $response = $parsed_response;

//        $this->log("Response:\n" . print_r($response, true));
        // Interpret Response
        if (is_array($response) && in_array('VALID', array_keys($response))) {
            return true;
        } else {
            return false;
        }
    }

// End validate_responses_data()

    /**
     * amounts_equal()
     * 
     * Checks to see whether the given amounts are equal using a proper floating
     * point comparison with an Epsilon which ensures that insignificant decimal
     * places are ignored in the comparison.
     * 
     * eg. 100.00 is equal to 100.0001
     *
     * @author Jonathan Smit
     * @param $amount1 Float 1st amount for comparison
     * @param $amount2 Float 2nd amount for comparison
     * @since 1.0.0
     */
    function amounts_equal($amount1, $amount2) {
        if (abs(floatval($amount1) - floatval($amount2)) > PF_EPSILON) {
            return( false );
        } else {
            return( true );
        }
    }

// End amounts_equal()
}

// End Class

function add_payfast_gateway($methods) {
    $methods[] = 'payfast';
    return $methods;
}

add_filter('jigoshop_payment_gateways', 'add_payfast_gateway');