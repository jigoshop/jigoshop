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
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/** Add the gateway to JigoShop **/
function add_skrill_gateway( $methods ) {
	$methods[] = 'skrill';
	return $methods;
}
add_filter( 'jigoshop_payment_gateways', 'add_skrill_gateway', 40 );


class skrill extends jigoshop_payment_gateway {

	protected $_supportedLocales = array('cn', 'cz', 'da', 'en', 'es', 'fi', 'de', 'fr', 'gr', 'it', 'nl', 'ro', 'ru', 'pl', 'sv', 'tr');

	public function __construct() {

		parent::__construct();

		$this->id        = 'skrill';
		$this->title     = 'Skrill';

		$this->has_fields= false;
		$this->enabled   = Jigoshop_Base::get_options()->get_option('jigoshop_skrill_enabled');
		$this->title     = Jigoshop_Base::get_options()->get_option('jigoshop_skrill_title');
		$this->email     = Jigoshop_Base::get_options()->get_option('jigoshop_skrill_email');
		$this->locale    = $this->getLocale();
		
		$skrillIcon = Jigoshop_Base::get_options()->get_option('jigoshop_skrill_icon');
		if ( !filter_var( $skrillIcon, FILTER_VALIDATE_URL )) {
			$this->icon	= jigoshop::assets_url() . '/assets/images/icons/skrill.png';
		} else {
			$this->icon = $skrillIcon;
		}
		
		$pMeth = (array)Jigoshop_Base::get_options()->get_option('jigoshop_skrill_payment_methods_multicheck');
		$cList = '';
		foreach ( $pMeth as $key => $value ) {	
			if ( $value ) {
				$cList = $cList . $key . ','; 
			}
		}
		$cList = rtrim( $cList, "," );
		$this->payment_methods = $cList;
	 	
		add_action( 'init', array( $this, 'check_status_response') );

		if ( isset($_GET['skrillPayment']) && $_GET['skrillPayment'] == true ) :
			add_action( 'init', array( $this, 'generate_skrill_form' ) );
		endif;

		add_action('valid-skrill-status-report', array( $this, 'successful_request' ) );
		add_action('receipt_skrill', array( $this, 'receipt_skrill' ));

    }

	public function getLocale() {

		$locale = explode('_', get_locale());
		if (is_array($locale) && !empty($locale) && in_array($locale[0], $this->_supportedLocales)) {
			return $locale[0];
		}

		return false;

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
		$defaults[] = array( 	
			'name' => __('Skrill (Moneybookers)', 'jigoshop'), 
			'type' => 'title', 
			'desc' => __('Skrill works by using an iFrame to submit payment information securely to Moneybookers.', 'jigoshop') 
		);

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
				'yes'			=> __('Yes', 'jigoshop') )
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

		$defaults[] = array (
			'name'      => __('Skrill payment icon','jigoshop'),
			'desc'      => __('Use the full URL to the image including http://','jigoshop'),
			'tip'		=> __('The URL to an icon image to display on the Checkout','jigoshop'),
			'id'		=> 'jigoshop_skrill_icon',
			'std' 		=> $this->icon,
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Skrill payment methods','jigoshop'),
			'desc' 		=> __('Select a max of 5. See page 40 for more info <br>https://www.moneybookers.com/merchant/en/moneybookers_gateway_manual.pdf. <br>Leave empty for default.'),
			'tip' 		=> __('The type of payments that should be allowed via Skrill.'),
			'id' 			=> 'jigoshop_skrill_payment_methods_multicheck',
			'type' 		=> 'multicheck',
			'std'       => 'ACC',
			'choices'	=> array(
				'ACC'           => __('All credit card types','jigoshop'),
				'VSA'           => __('Visa','jigoshop'),
				'MSC'           => __('MasterCard','jigoshop'),
				'VSE'           => __('Visa Electron','jigoshop')
			),
			'extra'		=> array( 'vertical' )
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
			'partner'              => 'Jigoshop',
			'pay_to_email'         => $this->email,
			'recipient_description'=> get_bloginfo('name'),
			'transaction_id'       => $order_id,
			'return_url'           => get_permalink( $checkout_redirect ),
			'return_url_text'      => 'Return to Merchant',
			'new_window_redirect'  => 0,
			'prepare_only'         => 0,
			'return_url_target'    => 1,
			'cancel_url'           => trailingslashit(get_bloginfo('url')).'?skrillListener=skrill_cancel',
			'cancel_url_target'    => 1,
			'status_url'           => trailingslashit(get_bloginfo('url')).'?skrillListener=skrill_status',
			'language'             => $this->getLocale(),
			'hide_login'           => 1,
			'confirmation_note'    => __('Thank you for shopping','jigoshop'),
						
			'pay_from_email'       => $order->billing_email,

			'firstname'            => $order->billing_first_name,
			'lastname'             => $order->billing_last_name,
			'address'              => $order->billing_address_1,
			'address2'             => $order->billing_address_2,
			'phone_number'         => $order->billing_phone,
			'postal_code'          => $order->billing_postcode,
			'city'                 => $order->billing_city,
			'state'                => $order->billing_state,
			'country'              => $this->retrieveIOC($this->getLocale()),

			'amount'               => $order_total,
			'currency'             => Jigoshop_Base::get_options()->get_option('jigoshop_currency'),
			'detail1_description'  => 'Order ID',
			'detail1_text'         => $order_id,

			'payment_methods'				=> $this->payment_methods
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
				$skrill_args['amount_'.$item_loop]   = $_product->get_price_with_tax();

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

		$skrill_md = Jigoshop_Base::get_options()->get_option('jigoshop_skrill_customer_id') . $skrill_args['transaction_id'] . strtoupper(md5(Jigoshop_Base::get_options()->get_option('jigoshop_skrill_secret_word'))) . $order_total . Jigoshop_Base::get_options()->get_option('jigoshop_currency') . '2';
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

	function retrieveIOC($code) {

		$countries = array(
			'AD' => 'AND',
			'AE' => 'UAE',
			'AF' => 'AFG',
			'AG' => 'ANT',
			'AL' => 'ALB',
			'AM' => 'ARM',
			'AN' => 'AHO',
			'AO' => 'ANG',
			'AR' => 'ARG',
			'AS' => 'ASA',
			'AT' => 'AUT',
			'AU' => 'AUS',
			'AW' => 'ARU',
			'AZ' => 'AZE',
			'BA' => 'BIH',
			'BB' => 'BAR',
			'BD' => 'BAN',
			'BE' => 'BEL',
			'BF' => 'BUR',
			'BG' => 'BUL',
			'BH' => 'BRN',
			'BI' => 'BDI',
			'BJ' => 'BEN',
			'BM' => 'BER',
			'BN' => 'BRU',
			'BO' => 'BOL',
			'BR' => 'BRA',
			'BS' => 'BAH',
			'BT' => 'BHU',
			'BW' => 'BOT',
			'BY' => 'BLR',
			'BZ' => 'BIZ',
			'CA' => 'CAN',
			'CD' => 'COD',
			'CF' => 'CAF',
			'CG' => 'CGO',
			'CH' => 'SUI',
			'CI' => 'CIV',
			'CK' => 'COK',
			'CL' => 'CHI',
			'CM' => 'CMR',
			'CN' => 'CHN',
			'CO' => 'COL',
			'CR' => 'CRC',
			'CU' => 'CUB',
			'CV' => 'CPV',
			'CY' => 'CYP',
			'CZ' => 'CZE',
			'DE' => 'GER',
			'DJ' => 'DJI',
			'DK' => 'DEN',
			'DM' => 'DMA',
			'DO' => 'DOM',
			'DZ' => 'ALG',
			'EC' => 'ECU',
			'EE' => 'EST',
			'EG' => 'EGY',
			'ER' => 'ERI',
			'ES' => 'ESP',
			'ET' => 'ETH',
			'FI' => 'FIN',
			'FJ' => 'FIJ',
			'FM' => 'FSM',
			'FO' => 'FRO',
			'FR' => 'FRA',
			'GA' => 'GAB',
			'GB' => 'GBR',
			'GD' => 'GRN',
			'GE' => 'GEO',
			'GH' => 'GHA',
			'GM' => 'GAM',
			'GN' => 'GUI',
			'GQ' => 'GEQ',
			'GR' => 'GRE',
			'GT' => 'GUA',
			'GU' => 'GUM',
			'GW' => 'GBS',
			'GY' => 'GUY',
			'HK' => 'HKG',
			'HN' => 'HON',
			'HR' => 'CRO',
			'HT' => 'HAI',
			'HU' => 'HUN',
			'ID' => 'INA',
			'IE' => 'IRL',
			'IL' => 'ISR',
			'IN' => 'IND',
			'IQ' => 'IRQ',
			'IR' => 'IRI',
			'IS' => 'ISL',
			'IT' => 'ITA',
			'JM' => 'JAM',
			'JO' => 'JOR',
			'JP' => 'JPN',
			'KE' => 'KEN',
			'KG' => 'KGZ',
			'KH' => 'CAM',
			'KI' => 'KIR',
			'KM' => 'COM',
			'KN' => 'SKN',
			'KP' => 'PRK',
			'KR' => 'KOR',
			'KW' => 'KUW',
			'KY' => 'CAY',
			'KZ' => 'KAZ',
			'LA' => 'LAO',
			'LB' => 'LIB',
			'LC' => 'LCA',
			'LI' => 'LIE',
			'LK' => 'SRI',
			'LR' => 'LBR',
			'LS' => 'LES',
			'LT' => 'LTU',
			'LU' => 'LUX',
			'LV' => 'LAT',
			'LY' => 'LBA',
			'MA' => 'MAR',
			'MC' => 'MON',
			'MD' => 'MDA',
			'ME' => 'MNE',
			'MG' => 'MAD',
			'MH' => 'MHL',
			'MK' => 'MKD',
			'ML' => 'MLI',
			'MM' => 'MYA',
			'MN' => 'MGL',
			'MR' => 'MTN',
			'MT' => 'MLT',
			'MU' => 'MRI',
			'MV' => 'MDV',
			'MW' => 'MAW',
			'MX' => 'MEX',
			'MY' => 'MAS',
			'MZ' => 'MOZ',
			'NA' => 'NAM',
			'NE' => 'NIG',
			'NG' => 'NGR',
			'NI' => 'NCA',
			'NL' => 'NED',
			'NO' => 'NOR',
			'NP' => 'NEP',
			'NR' => 'NRU',
			'NZ' => 'NZL',
			'OM' => 'OMA',
			'PA' => 'PAN',
			'PE' => 'PER',
			'PG' => 'PNG',
			'PH' => 'PHI',
			'PK' => 'PAK',
			'PL' => 'POL',
			'PR' => 'PUR',
			'PS' => 'PLE',
			'PT' => 'POR',
			'PW' => 'PLW',
			'PY' => 'PAR',
			'QA' => 'QAT',
			'RO' => 'ROU',
			'RS' => 'SRB',
			'RU' => 'RUS',
			'RW' => 'RWA',
			'SA' => 'KSA',
			'SB' => 'SOL',
			'SC' => 'SEY',
			'SD' => 'SUD',
			'SE' => 'SWE',
			'SG' => 'SIN',
			'SI' => 'SLO',
			'SK' => 'SVK',
			'SL' => 'SLE',
			'SM' => 'SMR',
			'SN' => 'SEN',
			'SO' => 'SOM',
			'SR' => 'SUR',
			'ST' => 'STP',
			'SV' => 'ESA',
			'SY' => 'SYR',
			'SZ' => 'SWZ',
			'TD' => 'CHA',
			'TG' => 'TOG',
			'TH' => 'THA',
			'TJ' => 'TJK',
			'TL' => 'TLS',
			'TM' => 'TKM',
			'TN' => 'TUN',
			'TO' => 'TGA',
			'TR' => 'TUR',
			'TT' => 'TRI',
			'TV' => 'TUV',
			'TW' => 'TPE',
			'TZ' => 'TAN',
			'UA' => 'UKR',
			'UG' => 'UGA',
			'UK' => 'GBR',
			'US' => 'USA',
			'UY' => 'URU',
			'UZ' => 'UZB',
			'VC' => 'VIN',
			'VE' => 'VEN',
			'VG' => 'IVB',
			'VI' => 'ISV',
			'VN' => 'VIE',
			'VU' => 'VAN',
			'WS' => 'SAM',
			'YE' => 'YEM',
			'ZA' => 'RSA',
			'ZM' => 'ZAM',
			'ZW' => 'ZIM',
		);

		$code = strtoupper($code);

		if ( !empty($countries[$code]) )
			return $countries[$code];

		return false;

	}

}
