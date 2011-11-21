<?php

/**
 * Inspire Gateway
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
class inspire extends jigoshop_payment_gateway
{

    public function __construct()
    {
        $this->id = 'inspire';
        $this->icon = jigoshop::plugin_url() . '/assets/images/icons/inspire.png';
        $this->has_fields = true;
        $this->enabled = get_option('jigoshop_inspire_enabled');
        $this->title = get_option('jigoshop_inspire_title');
        $this->description = get_option('jigoshop_inspire_description');
        $this->username = get_option('jigoshop_inspire_username');
        $this->password = get_option('jigoshop_inspire_password');
        $this->sale_method = get_option('jigoshop_inspire_sale_method');
        $this->gateway_url = get_option('jigoshop_inspire_gateway_url');
        $this->card_types = get_option('jigoshop_inspire_card_types');
        $this->cvv = get_option('jigoshop_inspire_cvv');
        $this->test_mode = get_option('jigoshop_inspire_test_mode');
        
        // Hooks
        add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
        add_action('receipt_inspire', array(&$this, 'receipt_page'));
        
        add_option('jigoshop_inspire_enabled', 'yes');
        add_option('jigoshop_inspire_title', __('Inspire Commerce', 'jigoshop'));
        add_option('jigoshop_inspire_description', __('Pay via Inspire Commerce.', 'jigoshop'));
        add_option('jigoshop_inspire_sale_method', 'auth_capture');
        add_option('jigoshop_inspire_gateway_url', 'https://secure.inspiregateway.net/gateway/transact.dll');
        add_option('jigoshop_inspire_cvv', 'yes');
        add_option('jigoshop_inspire_test_mode', 'no');
	add_option('jigoshop_inspire_card_types', array(
		'MasterCard' => 'MasterCard',
		'Visa' => 'Visa',
		'Discover' => 'Discover',
		'American Express' => 'American Express'
	));
    }

    /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     * 
     */
    public function admin_options()
    {
        ?>
        <thead><tr><th scope="col" width="200px"><?php _e('Inspire Commerce', 'jigoshop'); ?></th><th scope="col" class="desc"><?php _e('Inspire Commerce works by adding credit card fields on the checkout page, and then sending the details to Inspire Commerce for verification.', 'jigoshop'); ?></th></tr></thead>
        <tr>
            <td class="titledesc"><?php _e('Enable Inspire Commerce', 'jigoshop') ?>:</td>
            <td class="forminp">
                <select name="jigoshop_inspire_enabled" id="jigoshop_inspire_enabled" style="min-width:100px;">
                    <option value="yes" <?php if (get_option('jigoshop_inspire_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
                    <option value="no" <?php if (get_option('jigoshop_inspire_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
            <td class="forminp">
                <input class="input-text" type="text" name="jigoshop_inspire_title" id="jigoshop_inspire_title" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_inspire_title')) echo $value; ?>" />
            </td>
        </tr>
        <tr>
            <td class="titledesc"><a href="#" tip="<?php _e('This controls the description which the user sees during checkout.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Description', 'jigoshop') ?>:</td>
            <td class="forminp">
                <input class="input-text wide-input" type="text" name="jigoshop_inspire_description" id="jigoshop_inspire_description" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_inspire_description')) echo $value; ?>" />
            </td>
        </tr>
        <tr>
            <td class="titledesc"><a href="#" tip="<?php _e('This is the API username generated within the Inspire Commerce gateway.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Username', 'jigoshop') ?>:</td>
            <td class="forminp">
                <input class="input-text" type="text" name="jigoshop_inspire_username" id="jigoshop_inspire_username" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_inspire_username')) echo $value; ?>" />
            </td>
        </tr>
        <tr>
            <td class="titledesc"><a href="#" tip="<?php _e('This is the API user password generated within the Inspire Commerce gateway.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Password', 'jigoshop') ?>:</td>
            <td class="forminp">
                <input class="input-text" type="text" name="jigoshop_inspire_password" id="jigoshop_inspire_password" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_inspire_password')) echo $value; ?>" />
            </td>
        </tr>
        <tr>
            <td class="titledesc"><a href="#" tip="<?php _e('Select which sale method to use. Authorize Only will authorize the customers card for the purchase amount only.  Authorize &amp; Capture will authorize the customer\'s card and collect funds.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Sale Method', 'jigoshop') ?>:</td>
            <td class="forminp">
                <select name="jigoshop_inspire_sale_method" id="jigoshop_inspire_sale_method" style="min-width:100px;">
                    <option value="auth_capture" <?php if (get_option('jigoshop_inspire_sale_method') == 'yes') echo 'selected="selected"'; ?>><?php _e('Authorize &amp; Capture', 'jigoshop'); ?></option>
                    <option value="auth_only" <?php if (get_option('jigoshop_inspire_sale_method') == 'no') echo 'selected="selected"'; ?>><?php _e('Authorize Only', 'jigoshop'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="titledesc"><a href="#" tip="<?php _e('URL for Inspire Commerce gateway processor.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Gateway URL', 'jigoshop') ?>:</td>
            <td class="forminp">
                <input class="input-text" type="text" name="jigoshop_inspire_gateway_url" id="jigoshop_inspire_gateway_url" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_inspire_gateway_url')) echo $value; ?>" />
            </td>
        </tr>
        <tr>
            <td class="titledesc"><a href="#" tip="<?php _e('Select which card types to accept. Hold CTRL for multiple select.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e("Accepted Cards", 'jigoshop') ?></td>
            <td>
                <select multiple="multiple" name="jigoshop_inspire_card_types[]" id="jigoshop_inspire_card_types" style="min-width:100px; height: 100%">
                    <option value="MasterCard" <?php if (in_array('MasterCard', get_option('jigoshop_inspire_card_types'))) echo 'selected="selected"'; ?>><?php _e('MasterCard', 'jigoshop') ?></option>
                    <option value="Visa" <?php if (in_array('Visa', get_option('jigoshop_inspire_card_types'))) echo 'selected="selected"'; ?>><?php _e('Visa', 'jigoshop') ?></option>
                    <option value="Discover" <?php if (in_array('Discover', get_option('jigoshop_inspire_card_types'))) echo 'selected="selected"'; ?>><?php _e('Discover', 'jigoshop') ?></option>
                    <option value="American Express" <?php if (in_array('American Express', get_option('jigoshop_inspire_card_types'))) echo 'selected="selected"'; ?>><?php _e('American Express', 'jigoshop') ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="titledesc"><a href="#" tip="<?php _e('Require customer to enter credit card CVV code', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('CVV', 'jigoshop') ?>:</td>
            <td class="forminp">
                <select name="jigoshop_inspire_cvv" id="jigoshop_inspire_cvv" style="min-width:100px;">
                    <option value="yes" <?php if (get_option('jigoshop_inspire_cvv') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
                    <option value="no" <?php if (get_option('jigoshop_inspire_cvv') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="titledesc"><a href="#" tip="<?php _e('Place the payment gateway in development mode.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Inspire Commerce Test Mode', 'jigoshop') ?>:</td>
            <td class="forminp">
                <select name="jigoshop_inspire_test_mode" id="jigoshop_inspire_test_mode" style="min-width:100px;">
                    <option value="no" <?php if (get_option('jigoshop_inspire_test_mode') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
                    <option value="yes" <?php if (get_option('jigoshop_inspire_test_mode') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
                </select>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Admin Panel Options Processing
     * - Saves the options to the DB
     */
    public function process_admin_options()
    {
        if(!empty($_POST['jigoshop_inspire_enabled'])) {
            update_option('jigoshop_inspire_enabled', jigowatt_clean($_POST['jigoshop_inspire_enabled']));
        } else {
            @delete_option('jigoshop_paypal_enabled');
        }
        
        if(!empty($_POST['jigoshop_inspire_title'])) {
            update_option('jigoshop_inspire_title', jigowatt_clean($_POST['jigoshop_inspire_title']));
        } else {
            @delete_option('jigoshop_inspire_title');
        }
        
        if(!empty($_POST['jigoshop_inspire_description'])) {
            update_option('jigoshop_inspire_description', jigowatt_clean($_POST['jigoshop_inspire_description']));
        } else {
            @delete_option('jigoshop_inspire_description');
        }
        
        if(!empty($_POST['jigoshop_inspire_username'])) {
            update_option('jigoshop_inspire_username', jigowatt_clean($_POST['jigoshop_inspire_username']));
        } else {
            @delete_option('jigoshop_inspire_username');
        }
        
        if(!empty($_POST['jigoshop_inspire_password'])) {
            update_option('jigoshop_inspire_password', jigowatt_clean($_POST['jigoshop_inspire_password']));
        } else {
            @delete_option('jigoshop_inspire_password');
        }
        
        if(!empty($_POST['jigoshop_inspire_sale_method'])) {
            update_option('jigoshop_inspire_sale_method', jigowatt_clean($_POST['jigoshop_inspire_sale_method']));
        } else {
            @delete_option('jigoshop_inspire_sale_method');
        }
        
        if(!empty($_POST['jigoshop_inspire_gateway_url'])) {
            update_option('jigoshop_inspire_gateway_url', jigowatt_clean($_POST['jigoshop_inspire_gateway_url']));
        } else {
            @delete_option('jigoshop_inspire_gateway_url');
        }
        
        if(!empty($_POST['jigoshop_inspire_card_types'])) {
            update_option('jigoshop_inspire_card_types', jigowatt_clean($_POST['jigoshop_inspire_card_types']));
        } else {
            @delete_option('jigoshop_inspire_card_types');
        }
        
        if(!empty($_POST['jigoshop_inspire_cvv'])) {
            update_option('jigoshop_inspire_cvv', jigowatt_clean($_POST['jigoshop_inspire_cvv']));
        } else {
            @delete_option('jigoshop_inspire_cvv');
        }
        
        if(!empty($_POST['jigoshop_inspire_test_mode'])) {
            update_option('jigoshop_inspire_test_mode', jigowatt_clean($_POST['jigoshop_inspire_test_mode']));
        } else {
            @delete_option('jigoshop_inspire_test_mode');
        }
    }
    
    /**
     * Payment fields for Inspire Commerce.
     */
    function payment_fields()
    {
        ?>
        <?php if ($this->test_mode == 'yes') : ?>
            <p><?php _e('Test mode is enabled.', 'jigoshop'); ?></p>
        <?php endif; ?>
            
        <?php if ($this->description) : ?>
            <p><?php echo $this->description; ?></p>
        <?php endif; ?>
            
            <fieldset>
                <p class="form-row form-row-first">
                    <label for="ccnum"><?php echo __("Credit Card number", 'jigoshop') ?> <span class="required">*</span></label>
                    <input type="text" class="input-text" id="ccnum" name="ccnum" />
                </p>
                <p class="form-row form-row-last">
                    <label for="card_type"><?php echo __("Card type", 'jigoshop') ?> <span class="required">*</span></label>
                    <select name="card_type" id="card_type">
                        
                    <?php foreach ($this->card_types as $type) : ?>
                        <option value="<?php echo $type ?>"><?php _e($type, 'jigoshop'); ?></option>
                    <?php endforeach; ?>
                        
                    </select>
                </p>
                <div class="clear"></div>

                <p class="form-row form-row-first">
                    <label for="cc-expire-month"><?php echo __("Expiration date", 'jigoshop') ?> <span class="required">*</span></label>
                    <select name="exp_month" id="exp_month">
                        <option value=""><?php _e('Month', 'jigoshop') ?></option>
                        <?php echo $this->_get_months_options() ?>
                    </select>
                    <select name="exp_year" id="exp_year">
                        <option value=""><?php _e('Year', 'jigoshop') ?></option>
                        <?php echo $this->_get_years_options() ?>
                    </select>
                </p>
                
                <?php if ($this->cvv == 'yes') : ?>
                <p class="form-row form-row-last">
                    <label for="cvv"><?php _e("Card security code", 'jigoshop') ?> <span class="required">*</span></label>
                    <input type="text" class="input-text" id="cvv" name="cvv" maxlength="4" style="width:45px" />
                    <span class="help"><?php _e('3 or 4 digits usually found on the signature strip.', 'jigoshop') ?></span>
                </p>
                <?php endif ?>

                <div class="clear"></div>
            </fieldset>
        <?php
    }
    
    private function _get_months_options()
    {
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $timestamp = mktime(0, 0, 0, $i, 1);
            $months[date('n', $timestamp)] = date('F', $timestamp);
        }
        
        $options = '';
        foreach ($months as $num => $name) {
            $options .= sprintf('<option value="%u">%s</option>\n', $num, $name);
        }
        
        return $options;
    }
    
    private function _get_years_options($max_years = 15)
    {
        $year = (int) date('y');
        $years = '';
        
        for ($i = $year; $i <= $year + $max_years; $i++) {
            $years .= sprintf('<option value="20%u">20%u</option>\n', $i, $i);
        }
        
        return $years;
    }

    /**
     * Process the payment and return the result
     * 
     */
    public function process_payment($order_id)
    {
        global $jigoshop;

        $order = &new jigoshop_order($order_id);
        
        if ($this->test_mode == 'yes') {
            $test_mode = 'TRUE';
            $this->username = 'demo';
            $this->password = 'password';
        } else {
            $test_mode = 'FALSE';
        }

        // Create request
        $inspire_request = array(
            'x_login' => $this->username,
            'x_tran_key' => $this->password,
            'x_amount' => $order->order_total,
            'x_card_num' => (int) str_replace(array(' ', '-'), '', $_POST['ccnum']),
            'x_card_code' => (int) $_POST['cvv'],
            'x_exp_date' => (int) $_POST['exp_month'] . '-' . (int) $_POST['exp_year'],
            'x_type' => $this->sale_method,
            'x_version' => '3.1',
            'x_delim_data' => 'TRUE',
            'x_relay_response' => 'FALSE',
            'x_method' => 'CC',
            'x_first_name' => $order->billing_first_name,
            'x_last_name' => $order->billing_last_name,
            'x_address' => $order->billing_address_1,
            'x_city' => $order->billing_city,
            'x_state' => $order->billing_state,
            'x_zip' => $order->billing_postcode,
            'x_country' => $order->billing_country,
            'x_phone' => $order->billing_phone,
            'x_cust_id' => $order->user_id,
            'x_customer_ip' => $_SERVER['REMOTE_ADDR'],
            'x_invoice_num' => $order->id,
            'x_test_request' => $test_mode,
            'x_delim_char' => '|',
            'x_encap_char' => '',
        );

        // Send request
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        foreach ($inspire_request AS $key => $val) {
            $post .= urlencode($key) . "=" . urlencode($val) . "&";
        }
        $post = substr($post, 0, -1);
        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gateway_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        // prep response
        foreach (preg_split("/\r?\n/", $content) as $line) {
            if (preg_match("/^1|2|3\|/", $line)) {
                $data = explode("|", $line);
            }
        }

        // store response
        $response['response_code'] = $data[0];
        $response['response_sub_code'] = $data[1];
        $response['response_reason_code'] = $data[2];
        $response['response_reason_text'] = $data[3];
        $response['approval_code'] = $data[4];
        $response['avs_code'] = $data[5];
        $response['transaction_id'] = $data[6];
        $response['invoice_number_echo'] = $data[7];
        $response['description_echo'] = $data[8];
        $response['amount_echo'] = $data[9];
        $response['method_echo'] = $data[10];
        $response['transaction_type_echo'] = $data[11];
        $response['customer_id_echo'] = $data[12];
        $response['first_name_echo'] = $data[13];
        $response['last_name_echo'] = $data[14];
        $response['company_echo'] = $data[15];
        $response['billing_address_echo'] = $data[16];
        $response['city_echo'] = $data[17];
        $response['state_echo'] = $data[18];
        $response['zip_echo'] = $data[19];
        $response['country_echo'] = $data[20];
        $response['phone_echo'] = $data[21];
        $response['fax_echo'] = $data[22];
        $response['email_echo'] = $data[23];
        $response['ship_first_name_echo'] = $data[24];
        $response['ship_last_name_echo'] = $data[25];
        $response['ship_company_echo'] = $data[26];
        $response['ship_billing_address_echo'] = $data[27];
        $response['ship_city_echo'] = $data[28];
        $response['ship_state_echo'] = $data[29];
        $response['ship_zip_echo'] = $data[30];
        $response['ship_country_echo'] = $data[31];
        $response['tax_echo'] = $data[32];
        $response['duty_echo'] = $data[33];
        $response['freight_echo'] = $data[34];
        $response['tax_exempt_echo'] = $data[35];
        $response['po_number_echo'] = $data[36];

        $response['md5_hash'] = $data[37];
        $response['cvv_response_code'] = $data[38];
        $response['cavv_response_code'] = $data[39];

        // Retreive response
        if ($response['response_code'] == 1) {
            // Successful payment

            $order->add_order_note(__('Inspire Commerce payment completed', 'jigoshop') . ' (Transaction ID: ' . $response['transaction_id'] . ')');
            $order->payment_complete();

            // Empty awaiting payment session
            unset($_SESSION['order_awaiting_payment']);

            // Return thank you redirect
            return array(
			'result' => 'success',
			'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(get_option('jigoshop_pay_page_id'))))
            );
        } else {
            $cancelNote = __('Inspire Commerce payment failed', 'jigoshop') . ' (Response Code: ' . $response['response_code'] . '). ' . __('Payment wast rejected due to an error', 'jigoshop') . ': "' . $response['response_reason_text'] . '". ';

            $order->add_order_note($cancelNote);

            $jigoshop->add_error(__('Payment error', 'jigoshop') . ': ' . $response['response_reason_text'] . '');
        }
    }

    /**
      Validate payment form fields
     * */
    public function validate_fields() {
        global $jigoshop;

        $cardType = (string) $_POST['card_type'];
        $cardNumber = (string) $_POST['ccnum'];
        $cardCSC = (int) $_POST['cvv'];
        $cardExpirationMonth = (int) $_POST['exp_month'];
        $cardExpirationYear = (int) $_POST['exp_year'];

        if ($this->cvv == 'yes') {
            //check security code
            if ((strlen((string) $cardCSC) != 3 && in_array($cardType, array('Visa', 'MasterCard', 'Discover'))) || (strlen((string) $cardCSC) != 4 && $cardType == 'American Express')) {
                $jigoshop->add_error(__('Card security code is invalid (wrong length)', 'jigoshop'));
                return false;
            }
        }

        //check expiration data
        $currentYear = (int) date('Y');

        if ($cardExpirationMonth > 12
            || $cardExpirationMonth < 1
            || $cardExpirationYear < $currentYear
            || $cardExpirationYear > $currentYear + 20)
        {
            $jigoshop->add_error(__('Card expiration date is invalid', 'jigoshop'));
            return false;
        }

        //check card number
        $cardNumber = str_replace(array(' ', '-'), '', $cardNumber);

        if (empty($cardNumber)) {
            $jigoshop->add_error(__('Card number is invalid', 'jigoshop'));
            return false;
        }

        return true;
    }

    /**
     * receipt_page
     */
    function receipt_page($order) {

        echo '<p>'.__('Thank you for your order.', 'jigoshop').'</p>';
    }

}

/**
 * Add the gateway to JigoShop
 * 
 **/
function add_inspire_gateway($methods)
{
    $methods[] = 'inspire';

    return $methods;
}

add_filter('jigoshop_payment_gateways', 'add_inspire_gateway' );
