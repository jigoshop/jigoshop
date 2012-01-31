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
 * @package		Jigoshop
 * @category	Checkout
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */
class dibs extends jigoshop_payment_gateway {

	public function __construct() {
		$this->id = 'dibs';
		$this->icon = '';
		$this->has_fields = false;
		$this->enabled = get_option('jigoshop_dibs_enabled');
		$this->title = get_option('jigoshop_dibs_title');
		$this->merchant = get_option('jigoshop_dibs_merchant');
		$this->description  = get_option('jigoshop_dibs_description');
		$this->testmode = get_option('jigoshop_dibs_testmode');
		$this->key1 = get_option('jigoshop_dibs_key1');
		$this->key2 = get_option('jigoshop_dibs_key2');
		$this->decorator = get_option('jigoshop_dibs_decorator');
		$this->instant = get_option('jigoshop_dibs_instant');
		
		add_action('init', array(&$this, 'check_callback') );
		add_action('valid-dibs-callback', array(&$this, 'successful_request') );
		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
		add_action('receipt_dibs', array(&$this, 'receipt_page'));
		add_action('gateway_parse_thankyou', array(&$this, 'parse_thankyou'));
		add_action('gateway_parse_cancelorder', array(&$this, 'parse_cancelorder'));

		add_option('jigoshop_dibs_enabled', 'yes');
		add_option('jigoshop_dibs_merchant', '');
		add_option('jigoshop_dibs_key1', '');
		add_option('jigoshop_dibs_key2', '');
		add_option('jigoshop_dibs_title', __('DIBS', 'jigoshop') );
		add_option('jigoshop_dibs_description', __("Pay via DIBS using credit card or bank transfer.", 'jigoshop') );
		add_option('jigoshop_dibs_testmode', 'no');
		add_option('jigoshop_dibs_instant', 'no');
		add_option('jigoshop_dibs_decorator', '');
	}

	/**
	* Admin Panel Options
	* - Options for bits like 'title' and availability on a country-by-country basis
	**/
	public function admin_options() {
		?>
		<thead><tr><th scope="col" width="200px"><?php _e('DIBS FlexWin', 'jigoshop'); ?></th><th scope="col" class="desc"><?php _e('DIBS FlexWin works by sending the user to Dibs to enter their payment information. FlexWin can be themed by adding decorators in your Dibs admin interface.', 'jigoshop'); ?></th></tr></thead>
		<tr>
			<td class="titledesc"><?php _e('Enable DIBS FlexWin', 'jigoshop') ?>:</td>
			<td class="forminp">
				<select name="jigoshop_dibs_enabled" id="jigoshop_dibs_enabled" style="min-width:100px;">
					<option value="yes" <?php if (get_option('jigoshop_dibs_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
					<option value="no" <?php if (get_option('jigoshop_dibs_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
			<td class="forminp">
				<input class="input-text" type="text" name="jigoshop_dibs_title" id="jigoshop_dibs_title" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_dibs_title')) echo $value; else echo 'DIBS'; ?>" />
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('This controls the description which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Description', 'jigoshop') ?>:</td>
			<td class="forminp">
				<input class="input-text wide-input" type="text" name="jigoshop_dibs_description" id="jigoshop_dibs_description" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_dibs_description')) echo $value; ?>" />
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('Please enter your DIBS merchant id; this is needed in order to take payment!','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('DIBS Merchant id', 'jigoshop') ?>:</td>
			<td class="forminp">
				<input class="input-text" type="text" name="jigoshop_dibs_merchant" id="jigoshop_dibs_merchant" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_dibs_merchant')) echo $value; ?>" />
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('Please enter your DIBS MD5 key #1; this is needed in order to take payment!','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('DIBS MD5 Key 1', 'jigoshop') ?>:</td>
			<td class="forminp">
				<input class="input-text" type="text" name="jigoshop_dibs_key1" id="jigoshop_dibs_key1" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_dibs_key1')) echo $value; ?>" />
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('Please enter your DIBS MD5 key #2; this is needed in order to take payment!','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('DIBS MD5 Key 2', 'jigoshop') ?>:</td>
			<td class="forminp">
				<input class="input-text" type="text" name="jigoshop_dibs_key2" id="jigoshop_dibs_key2" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_dibs_key2')) echo $value; ?>" />
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('The theme for Dibs FlexWin to use.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Decorator', 'jigoshop') ?>:</td>
			<td class="forminp">
				<select name="jigoshop_dibs_decorator" id="jigoshop_dibs_decorator" style="min-width:100px;">
					<option value="" <?php if (get_option('jigoshop_dibs_decorator') == '') echo 'selected="selected"'; ?>><?php _e('Custom', 'jigoshop'); ?></option>
					<option value="default" <?php if (get_option('jigoshop_dibs_decorator') == 'default') echo 'selected="selected"'; ?>><?php echo 'Default'; ?></option>
					<option value="basal" <?php if (get_option('jigoshop_dibs_decorator') == 'basal') echo 'selected="selected"'; ?>><?php echo 'Basal'; ?></option>
					<option value="rich" <?php if (get_option('jigoshop_dibs_decorator') == 'rich') echo 'selected="selected"'; ?>><?php echo 'Rich'; ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('Contact DIBS before enabling this feature.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Enable instant capture', 'jigoshop') ?>:</td>
			<td class="forminp">
				<select name="jigoshop_dibs_instant" id="jigoshop_dibs_instant" style="min-width:100px;">
					<option value="yes" <?php if (get_option('jigoshop_dibs_instant') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
					<option value="no" <?php if (get_option('jigoshop_dibs_instant') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('When test mode is enabled only DIBS specific test-cards are accepted.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Enable test mode', 'jigoshop') ?>:</td>
			<td class="forminp">
				<select name="jigoshop_dibs_testmode" id="jigoshop_dibs_testmode" style="min-width:100px;">
					<option value="yes" <?php if (get_option('jigoshop_dibs_testmode') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
					<option value="no" <?php if (get_option('jigoshop_dibs_testmode') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	* There are no payment fields for dibs, but we want to show the description if set.
	**/
	function payment_fields() {
		if ($jigoshop_dibs_description = get_option('jigoshop_dibs_description')) echo wpautop(wptexturize($jigoshop_dibs_description));
	}

	/**
	* Admin Panel Options Processing
	* - Saves the options to the DB
	**/
	public function process_admin_options() {
		if(isset($_POST['jigoshop_dibs_enabled'])) update_option('jigoshop_dibs_enabled', jigowatt_clean($_POST['jigoshop_dibs_enabled'])); else @delete_option('jigoshop_dibs_enabled');
		if(isset($_POST['jigoshop_dibs_title'])) update_option('jigoshop_dibs_title', jigowatt_clean($_POST['jigoshop_dibs_title'])); else @delete_option('jigoshop_dibs_title');
		if(isset($_POST['jigoshop_dibs_merchant'])) update_option('jigoshop_dibs_merchant', jigowatt_clean($_POST['jigoshop_dibs_merchant'])); else @delete_option('jigoshop_dibs_merchant');
		if(isset($_POST['jigoshop_dibs_key1'])) update_option('jigoshop_dibs_key1', jigowatt_clean($_POST['jigoshop_dibs_key1'])); else @delete_option('jigoshop_dibs_key1');
		if(isset($_POST['jigoshop_dibs_key2'])) update_option('jigoshop_dibs_key2', jigowatt_clean($_POST['jigoshop_dibs_key2'])); else @delete_option('jigoshop_dibs_key2');
		if(isset($_POST['jigoshop_dibs_description'])) update_option('jigoshop_dibs_description', jigowatt_clean($_POST['jigoshop_dibs_description'])); else @delete_option('jigoshop_dibs_description');
		if(isset($_POST['jigoshop_dibs_testmode'])) update_option('jigoshop_dibs_testmode', jigowatt_clean($_POST['jigoshop_dibs_testmode'])); else @delete_option('jigoshop_dibs_testmode');
		if(isset($_POST['jigoshop_dibs_decorator'])) update_option('jigoshop_dibs_decorator', jigowatt_clean($_POST['jigoshop_dibs_decorator'])); else @delete_option('jigoshop_dibs_decorator');
		if(isset($_POST['jigoshop_dibs_instant'])) update_option('jigoshop_dibs_instant', jigowatt_clean($_POST['jigoshop_dibs_instant'])); else @delete_option('jigoshop_dibs_instant');
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
				'merchant' => $this->merchant,
				'decorator' => $this->decorator,
				
				// Session
				'lang' => 'sv', //TODO Language should probably not be hardcoded here
				
				// Order
				'amount' => $order->order_total * 100,
				'orderid' => $order_id,
				'uniqueoid' => $order->order_key,
				'currency' => $dibs_currency[get_option('jigoshop_currency')],
				'capturenow' => $this->instant ? 'true' : 'false',
				//'ordertext' => 'TEST', // TODO Print som order info here
				
				// URLs
				'callbackurl' => site_url('/jigoshop/dibscallback.php'),
				'accepturl' =>  get_permalink($checkout_redirect),
				'cancelurl' => $order->get_cancel_order_post_url(),
		);


		// Calculate key
		// http://tech.dibs.dk/dibs_api/other_features/md5-key_control/
		$args['md5key'] = MD5($this->key2 . MD5($this->key1 . 'merchant=' . $args['merchant'] . '&orderid=' . $args['orderid'] . '&currency=' . $args['currency'] . '&amount=' . $args['amount']));
		
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
				<input type="submit" class="button-alt" id="submit_dibs_payment_form" value="'.__('Pay via DIBS', 'jigoshop').'" /> <a class="button cancel" href="'.esc_url($order->get_cancel_order_post_url()).'">'.__('Cancel order &amp; restore cart', 'jigoshop').'</a>
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
		if ( strpos($_SERVER["REQUEST_URI"], '/jigoshop/dibscallback.php') !== false ) {
			
			do_action("valid-dibs-callback", stripslashes_deep($_POST));
			
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
			$vars = 'transact='. $posted['transact'] . '&amount=' . $posted['amount'] . '&currency=' . $posted['currency'];
			$md5 = MD5($this->key2 . MD5($this->key1 . $vars));
			
			if($posted['authkey'] != $md5) {
				error_log('MD5 check failed for Dibs callback with order_id:'.$posted['orderid']);
				exit();
			}

			$order = new jigoshop_order( (int) $posted['orderid'] );

			if ($order->order_key !== $posted['uniqueoid']) {
				$log = sprintf( __('DIBS transaction %s failed security check. Key from dibs was %s and stored key was %s.', 'jigoshop'), $posted['transact'], $posted['uniqueoid'], $order->order_key ) ;
				error_log($log);
				$order->add_order_note( $log );
				exit;
			}

			if ($order->status !== 'completed') {
				
				$order->add_order_note( sprintf( __('DIBS payment completed with transaction id %s.', 'jigoshop'), $posted['transact'] ) );
				$order->payment_complete();

			}

			exit;

		}

	}
	
	function parse_thankyou( $not_used ) {
		
		// For some dubious reason success url uses GET and cancel uses POST
		if(isset($_GET['orderid']) && is_numeric($_GET['orderid']) && isset($_GET['uniqueoid']) ) {
			$data = array();
			$data['order_id'] = $_GET['orderid'];
			$data['order_key'] = $_GET['uniqueoid'];
			return $data;
		}else{
			return false;
		}
		
	}
	
	function parse_cancelorder( $not_used ) {
		
		// For some dubious reason success url uses GET and cancel uses POST
		if( isset($_POST['orderid']) && is_numeric($_POST['orderid']) && isset($_POST['uniqueoid']) ) {
			$data = array();
			$data['order_id'] = $_POST['orderid'];
			$data['order_key'] = $_POST['uniqueoid'];
			return $data;
		}else{
			return false;
		}
		
	}
	
	

}

/**
 * Add the gateway to JigoShop
 **/
function add_dibs_gateway( $methods ) {
	$methods[] = 'dibs'; return $methods;
}

add_filter('jigoshop_payment_gateways', 'add_dibs_gateway' );
