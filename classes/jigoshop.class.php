<?php
/**
 * Contains the most low level methods & helpers in Jigoshop
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

class jigoshop extends Jigoshop_Singleton {

	public static $errors   = array();
	public static $messages = array();

	public static $plugin_url;
	public static $plugin_path;

	const SHOP_LARGE_W     = '300';
	const SHOP_LARGE_H     = '300';
	const SHOP_SMALL_W     = '150';
	const SHOP_SMALL_H     = '150';
	const SHOP_TINY_W      = '36';
	const SHOP_TINY_H      = '36';
	const SHOP_THUMBNAIL_W = '90';
	const SHOP_THUMBNAIL_H = '90';

	/** constructor */
	protected function __construct() {

		self::$errors   = (array) jigoshop_session::instance()->errors;
		self::$messages = (array) jigoshop_session::instance()->messages;

		// uses jigoshop_base_class to provide class address for the filter
		self::add_filter( 'wp_redirect', 'redirect', 1, 2 );
	}

	/**
	 * Get the current path to Jigoshop
	 *
	 * @return  string	local filesystem path with trailing slash
	 */
	public static function jigoshop_path() {
		return plugin_dir_path( dirname( __FILE__ ) );
	}
	
	/**
	 * Get the current version of Jigoshop
	 *
	 * @return  string	current Jigoshop version
	 */
	public static function jigoshop_version() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( self::jigoshop_path() . 'jigoshop.php' );
		return $plugin_data['Version'];
	}

	/**
	 * Get the assets url
	 * Provide a filter to allow asset location elsewhere such as on a CDN
	 *
	 * @return  string	url
	 */
	public static function assets_url( $file = NULL ) {
		return apply_filters( 'jigoshop_assets_url', self::plugin_url( $file ) );
	}

	/**
	 * Get the plugin url
	 * @todo perhaps we should add a trailing slash? -1 Character then!
	 * @note plugin_dir_url() does this
	 *
	 * @return  string	url
	 */
	public static function plugin_url( $file = NULL ) {
		if ( ! empty( self::$plugin_url) )
			return self::$plugin_url;

		return self::$plugin_url = plugins_url( $file, dirname(__FILE__));
	}

	/**
	 * Get the plugin path
	 * @todo perhaps we should add a trailing slash? -1 Character then!
	 * @note plugin_dir_path() does this
	 *
	 * @return  string	url
	 */
	public static function plugin_path() {
		if( ! empty(self::$plugin_path) )
			return self::$plugin_path;

		return self::$plugin_path = dirname(dirname(__FILE__));
	 }

	/**
	 * Return the URL with https if SSL is on
	 *
	 * @return  string	url
	 */
	public static function force_ssl( $url ) {
		if (is_ssl()) $url = str_replace('http:', 'https:', $url);
		return $url;
	 }

	/**
	 * Get a var
	 *
	 * Variable is filtered by jigoshop_get_var_{var name}
	 *
	 * @param   string	var
	 * @return  string	variable
	 */
	public static function get_var($var) {
		switch ( $var ) {
			case "shop_large_w" :     $return = self::SHOP_LARGE_W; break;
			case "shop_large_h" :     $return = self::SHOP_LARGE_H; break;
			case "shop_small_w" :     $return = self::SHOP_SMALL_W; break;
			case "shop_small_h" :     $return = self::SHOP_SMALL_H; break;
			case "shop_tiny_w" :      $return = self::SHOP_TINY_W; break;
			case "shop_tiny_h" :      $return = self::SHOP_TINY_H; break;
			case "shop_thumbnail_w" : $return = self::SHOP_THUMBNAIL_W; break;
			case "shop_thumbnail_h" : $return = self::SHOP_THUMBNAIL_H; break;
		}

		return apply_filters( 'jigoshop_get_var_'.$var, $return );
	}

	/**
	 * Add an error
	 * @todo should be set_error
	 *
	 * @param   string	error
	 */
	public static function add_error( $error ) {
		self::$errors[] = $error;
	}

	/**
	 * Add a message
	 * @todo should be set_message
	 *
	 * @param   string	message
	 */
	public static function add_message( $message ) {
		self::$messages[] = $message;
	}

	/** Clear messages and errors from the session data */
	public static function clear_messages() {
		self::$errors = self::$messages = array();
		unset( jigoshop_session::instance()->messages );
		unset( jigoshop_session::instance()->errors );
	}

	public static function has_errors() {
		return !empty(self::$errors);
	}

	public static function has_messages() { 
		return !empty(self::$messages);
	}

	/**
	 * Output the errors and messages
	 */
	public static function show_messages() {
		if ( self::has_errors() ) {
			echo '<div class="jigoshop_error">'.self::$errors[0].'</div>';
		}

		if ( self::has_messages() ) {
			echo '<div class="jigoshop_message">'.self::$messages[0].'</div>';
		}

		self::clear_messages();
	}

	public static function nonce_field($action, $referer = true , $echo = true) {
		$name = '_n';
		$action = 'jigoshop-' . $action;

		return wp_nonce_field($action, $name, $referer, $echo);
	}

	public static function nonce_url($action, $url = '') {
		$name = '_n';
		$action = 'jigoshop-' . $action;

		return add_query_arg( $name, wp_create_nonce( $action ), $url);
	}
	
	/**
	 * Check a nonce and sets jigoshop error in case it is invalid
	 * To fail silently, set the error_message to an empty string
	 *
	 * @param 	string $name the nonce name
	 * @param	string $action then nonce action
	 * @param   string $method the http request method _POST, _GET or _REQUEST
	 * @param   string $error_message custom error message, or false for default message, or an empty string to fail silently
	 *
	 * @return   bool
	 */
	public static function verify_nonce( $action ) {
		$name    = '_n';
		$action  = 'jigoshop-' . $action;
		$request = array_merge($_GET, $_POST);

		if ( ! wp_verify_nonce($request[$name], $action) ) {
			jigoshop::add_error( __('Action failed. Please refresh the page and retry.', 'jigoshop') );
			return false;
		}

		return true;
	}

	/**
	 * Redirection hook which stores messages into session data
	 * @deprecated do we actually use this anywhere?
	 *
	 * @param   location
	 * @param   status
	 * @return  location
	 */
	public static function redirect( $location, $status = NULL ) {
		jigoshop_session::instance()->errors = self::$errors;
		jigoshop_session::instance()->messages = self::$messages;
		return apply_filters('jigoshop_session_location_filter', $location);
	}
	
	// http://www.xe.com/symbols.php
	public static function currency_symbols() {
		$symbols = array(
			'AED' => '&#1583;&#46;&#1573;',     /*'United Arab Emirates dirham'*/
			'AFN' => '&#1547;',                 /*'Afghanistan Afghani'*/
			'ALL' => 'Lek',                     /*'Albania Lek'*/
			'ANG' => '&fnof;',                  /*'Netherlands Antilles Guilder'*/
			'ARS' => '$',                       /*'Argentina Peso'*/
			'AUD' => '$',                       /*'Australia Dollar'*/
			'AWG' => '&fnof;',                  /*'Aruba Guilder'*/
			'AZN' => '&#1084;&#1072;&#1085;',   /*'Azerbaijan New Manat'*/
			'BAM' => 'KM',                      /*'Bosnia and Herzegovina Convertible Marka'*/
			'BBD' => '$',                       /*'Barbados Dollar'*/
			'BGN' => '&#1083;&#1074;',          /*'Bulgaria Lev'*/
			'BMD' => '$',                       /*'Bermuda Dollar'*/
			'BND' => '$',                       /*'Brunei Darussalam Dollar'*/
			'BOB' => '$b',                      /*'Bolivia Boliviano'*/
			'BRL' => '&#82;&#36;',              /*'Brazil Real'*/
			'BSD' => '$',                       /*'Bahamas Dollar'*/
			'BWP' => 'P',                       /*'Botswana Pula'*/
			'BYR' => 'p.',                      /*'Belarus Ruble'*/
			'BZD' => 'BZ$',                     /*'Belize Dollar'*/
			'CAD' => '$',                       /*'Canada Dollar'*/
			'CHF' => 'CHF',                     /*'Switzerland Franc'*/
			'CLP' => '$',                       /*'Chile Peso'*/
			'CNY' => '&yen;',                   /*'China Yuan Renminbi'*/
			'COP' => '$',                       /*'Colombia Peso'*/
			'CRC' => '&#8353;',                 /*'Costa Rica Colon'*/
			'CUP' => '&#8369;',                 /*'Cuba Peso'*/
			'CZK' => 'K&#269;',                 /*'Czech Republic Koruna'*/
			'DKK' => 'kr',                      /*'Denmark Krone'*/
			'DOP' => 'RD$',                     /*'Dominican Republic Peso'*/
			'EEK' => 'kr',                      /*'Estonia Kroon'*/
			'EGP' => '&pound;',                 /*'Egypt Pound'*/
			'EUR' => '&euro;',                  /*'Euro Member Countries'*/
			'FJD' => '$',                       /*'Fiji Dollar'*/
			'FKP' => '&pound;',                 /*'Falkland Islands'*/
			'GBP' => '&pound;',                 /*'United Kingdom Pound'*/
			'GGP' => '&pound;',                 /*'Guernsey Pound'*/
			'GHC' => '&cent;',                  /*'Ghana Cedis'*/
			'GIP' => '&cent;',                  /*'Gibraltar Pound'*/
			'GTQ' => 'Q',                       /*'Guatemala Quetzal'*/
			'GYD' => '$',                       /*'Guyana Dollar'*/
			'HKD' => '$',                       /*'Hong Kong Dollar'*/
			'HNL' => 'L',                       /*'Honduras Lempira'*/
			'HRK' => 'kn',                      /*'Croatia Kuna'*/
			'HUF' => '&#70;&#116;',             /*'Hungary Forint'*/
			'IDR' => '&#82;&#112;',             /*'Indonesia Rupiah'*/
			'ILS' => '&#8362;',                 /*'Israel Shekel'*/
			'IMP' => '&pound;',                 /*'Isle of Man Pound'*/
			'INR' => '&#8360;',                 /*'India Rupee'*/
			'IRR' => '&#65020;',                /*'Iran Rial'*/
			'ISK' => 'kr',                      /*'Iceland Krona'*/
			'JEP' => '&pound;',                 /*'Jersey Pound'*/
			'JMD' => 'J$',                      /*'Jamaica Dollar'*/
			'JPY' => '&yen;',                   /*'Japan Yen'*/
			'KGS' => '&#1083;&#1074;',          /*'Kyrgyzstan Som'*/
			'KHR' => '&#6107;',                 /*'Cambodia Riel'*/
			'KPW' => '&#8361;',                 /*'North Korea Won'*/
			'KRW' => '&#8361;',                 /*'South Korea Won'*/
			'KYD' => '$',                       /*'Cayman Islands Dollar'*/
			'KZT' => '&#1083;&#1074;',          /*'Kazakhstan Tenge'*/
			'LAK' => '&#8365;',                 /*'Laos Kip'*/
			'LBP' => '&pound;',                 /*'Lebanon Pound'*/
			'LKR' => '&#8360;',                 /*'Sri Lanka Rupee'*/
			'LRD' => '$',                       /*'Liberia Dollar'*/
			'LTL' => 'Lt',                      /*'Lithuania Litas'*/
			'LVL' => 'Ls',                      /*'Latvia Lat'*/
			'MKD' => '&#1076;&#1077;&#1085;',   /*'Macedonia Denar'*/
			'MNT' => '&#8366;',                 /*'Mongolia Tughrik'*/
			'MUR' => '&#8360;',                 /*'Mauritius Rupee'*/
			'MXN' => '&#36;',                   /*'Mexico Peso'*/
			'MYR' => 'RM',                      /*'Malaysia Ringgit'*/
			'MZN' => 'MT',                      /*'Mozambique Metical'*/
			'NAD' => '$',                       /*'Namibia Dollar'*/
			'NGN' => '&#8358;',                 /*'Nigeria Naira'*/
			'NIO' => 'C$',                      /*'Nicaragua Cordoba'*/
			'NOK' => 'kr',                      /*'Norway Krone'*/
			'NPR' => '&#8360;',                 /*'Nepal Rupee'*/
			'NZD' => '$',                       /*'New Zealand Dollar'*/
			'OMR' => '&#65020;',                /*'Oman Rial'*/
			'PAB' => 'B/.',                     /*'Panama Balboa'*/
			'PEN' => 'S/.',                     /*'Peru Nuevo Sol'*/
			'PHP' => '&#8369;',                 /*'Philippines Peso'*/
			'PKR' => '&#8360;',                 /*'Pakistan Rupee'*/
			'PLN' => '&#122;&#322;',            /*'Poland Zloty'*/
			'PYG' => 'Gs',                      /*'Paraguay Guarani'*/
			'QAR' => '&#65020;',                /*'Qatar Riyal'*/
			'RON' => '&#108;&#101;&#105;',      /*'Romania New Leu'*/
			'RSD' => 'РСД',                     /*'Serbia Dinar'*/
			'RUB' => '&#1088;&#1091;&#1073;',   /*'Russia Ruble'*/
			'SAR' => '&#65020;',                /*'Saudi Arabia Riyal'*/
			'SBD' => '$',                       /*'Solomon Islands Dollar'*/
			'SCR' => '&#8360;',                 /*'Seychelles Rupee'*/
			'SEK' => 'kr',                      /*'Sweden Krona'*/
			'SGD' => '$',                       /*'Singapore Dollar'*/
			'SHP' => '&pound;',                 /*'Saint Helena Pound'*/
			'SOS' => 'S',                       /*'Somalia Shilling'*/
			'SRD' => '$',                       /*'Suriname Dollar'*/
			'SVC' => '$',                       /*'El Salvador Colon'*/
			'SYP' => '&pound;',                 /*'Syria Pound'*/
			'THB' => '&#3647;',                 /*'Thailand Baht'*/
			'TRL' => '&#8356;',                 /*'Turkey Lira'*/
			'TRY' => 'TL',                      /*'Turkey Lira'*/
			'TTD' => 'TT$',                     /*'Trinidad and Tobago Dollar'*/
			'TVD' => '$',                       /*'Tuvalu Dollar'*/
			'TWD' => 'NT$',                     /*'Taiwan New Dollar'*/
			'UAH' => '&#8372;',                 /*'Ukraine Hryvna'*/
			'USD' => '$',                       /*'United States Dollar'*/
			'UYU' => '$U',                      /*'Uruguay Peso'*/
			'UZS' => '&#1083;&#1074;',          /*'Uzbekistan Som'*/
			'VEF' => 'Bs',                      /*'Venezuela Bolivar Fuerte'*/
			'VND' => '&#8363;',                 /*'Viet Nam Dong'*/
			'XCD' => '$',                       /*'East Caribbean Dollar'*/
			'YER' => '&#65020;',                /*'Yemen Rial'*/
			'ZAR' => 'R',                       /*'South Africa Rand'*/
			'ZWD' => 'Z$',                      /*'Zimbabwe Dollar'*/
		);
		
		ksort( $symbols );
		return $symbols;
	}
	
	public static function currency_countries() {
		$countries = array(
			'AED' => __('United Arab Emirates dirham', 'jigoshop'),
			'AFN' => __('Afghanistan Afghani', 'jigoshop'),
			'ALL' => __('Albania Lek', 'jigoshop'),
			'ANG' => __('Netherlands Antilles Guilder', 'jigoshop'),
			'ARS' => __('Argentina Peso', 'jigoshop'),
			'AUD' => __('Australia Dollar', 'jigoshop'),
			'AWG' => __('Aruba Guilder', 'jigoshop'),
			'AZN' => __('Azerbaijan New Manat', 'jigoshop'),
			'BAM' => __('Bosnia and Herzegovina Convertible Marka', 'jigoshop'),
			'BBD' => __('Barbados Dollar', 'jigoshop'),
			'BGN' => __('Bulgaria Lev', 'jigoshop'),
			'BMD' => __('Bermuda Dollar', 'jigoshop'),
			'BND' => __('Brunei Darussalam Dollar', 'jigoshop'),
			'BOB' => __('Bolivia Boliviano', 'jigoshop'),
			'BRL' => __('Brazil Real', 'jigoshop'),
			'BSD' => __('Bahamas Dollar', 'jigoshop'),
			'BWP' => __('Botswana Pula', 'jigoshop'),
			'BYR' => __('Belarus Ruble', 'jigoshop'),
			'BZD' => __('Belize Dollar', 'jigoshop'),
			'CAD' => __('Canada Dollar', 'jigoshop'),
			'CHF' => __('Switzerland Franc', 'jigoshop'),
			'CLP' => __('Chile Peso', 'jigoshop'),
			'CNY' => __('China Yuan Renminbi', 'jigoshop'),
			'COP' => __('Colombia Peso', 'jigoshop'),
			'CRC' => __('Costa Rica Colon', 'jigoshop'),
			'CUP' => __('Cuba Peso', 'jigoshop'),
			'CZK' => __('Czech Republic Koruna', 'jigoshop'),
			'DKK' => __('Denmark Krone', 'jigoshop'),
			'DOP' => __('Dominican Republic Peso', 'jigoshop'),
			'EEK' => __('Estonia Kroon', 'jigoshop'),
			'EGP' => __('Egypt Pound', 'jigoshop'),
			'EUR' => __('Euro Member Countries', 'jigoshop'),
			'FJD' => __('Fiji Dollar', 'jigoshop'),
			'FKP' => __('Falkland Islands', 'jigoshop'),
			'GBP' => __('United Kingdom Pound', 'jigoshop'),
			'GGP' => __('Guernsey Pound', 'jigoshop'),
			'GHC' => __('Ghana Cedis', 'jigoshop'),
			'GIP' => __('Gibraltar Pound', 'jigoshop'),
			'GTQ' => __('Guatemala Quetzal', 'jigoshop'),
			'GYD' => __('Guyana Dollar', 'jigoshop'),
			'HKD' => __('Hong Kong Dollar', 'jigoshop'),
			'HNL' => __('Honduras Lempira', 'jigoshop'),
			'HRK' => __('Croatia Kuna', 'jigoshop'),
			'HUF' => __('Hungary Forint', 'jigoshop'),
			'IDR' => __('Indonesia Rupiah', 'jigoshop'),
			'ILS' => __('Israel Shekel', 'jigoshop'),
			'IMP' => __('Isle of Man Pound', 'jigoshop'),
			'INR' => __('India Rupee', 'jigoshop'),
			'IRR' => __('Iran Rial', 'jigoshop'),
			'ISK' => __('Iceland Krona', 'jigoshop'),
			'JEP' => __('Jersey Pound', 'jigoshop'),
			'JMD' => __('Jamaica Dollar', 'jigoshop'),
			'JPY' => __('Japan Yen', 'jigoshop'),
			'KGS' => __('Kyrgyzstan Som', 'jigoshop'),
			'KHR' => __('Cambodia Riel', 'jigoshop'),
			'KPW' => __('North Korea Won', 'jigoshop'),
			'KRW' => __('South Korea Won', 'jigoshop'),
			'KYD' => __('Cayman Islands Dollar', 'jigoshop'),
			'KZT' => __('Kazakhstan Tenge', 'jigoshop'),
			'LAK' => __('Laos Kip', 'jigoshop'),
			'LBP' => __('Lebanon Pound', 'jigoshop'),
			'LKR' => __('Sri Lanka Rupee', 'jigoshop'),
			'LRD' => __('Liberia Dollar', 'jigoshop'),
			'LTL' => __('Lithuania Litas', 'jigoshop'),
			'LVL' => __('Latvia Lat', 'jigoshop'),
			'MKD' => __('Macedonia Denar', 'jigoshop'),
			'MNT' => __('Mongolia Tughrik', 'jigoshop'),
			'MUR' => __('Mauritius Rupee', 'jigoshop'),
			'MXN' => __('Mexico Peso', 'jigoshop'),
			'MYR' => __('Malaysia Ringgit', 'jigoshop'),
			'MZN' => __('Mozambique Metical', 'jigoshop'),
			'NAD' => __('Namibia Dollar', 'jigoshop'),
			'NGN' => __('Nigeria Naira', 'jigoshop'),
			'NIO' => __('Nicaragua Cordoba', 'jigoshop'),
			'NOK' => __('Norway Krone', 'jigoshop'),
			'NPR' => __('Nepal Rupee', 'jigoshop'),
			'NZD' => __('New Zealand Dollar', 'jigoshop'),
			'OMR' => __('Oman Rial', 'jigoshop'),
			'PAB' => __('Panama Balboa', 'jigoshop'),
			'PEN' => __('Peru Nuevo Sol', 'jigoshop'),
			'PHP' => __('Philippines Peso', 'jigoshop'),
			'PKR' => __('Pakistan Rupee', 'jigoshop'),
			'PLN' => __('Poland Zloty &#122;&#322;)', 'jigoshop'),
			'PYG' => __('Paraguay Guarani', 'jigoshop'),
			'QAR' => __('Qatar Riyal', 'jigoshop'),
			'RON' => __('Romania New Leu', 'jigoshop'),
			'RSD' => __('Serbia Dinar', 'jigoshop'),
			'RUB' => __('Russia Ruble', 'jigoshop'),
			'SAR' => __('Saudi Arabia Riyal', 'jigoshop'),
			'SBD' => __('Solomon Islands Dollar', 'jigoshop'),
			'SCR' => __('Seychelles Rupee', 'jigoshop'),
			'SEK' => __('Sweden Krona', 'jigoshop'),
			'SGD' => __('Singapore Dollar', 'jigoshop'),
			'SHP' => __('Saint Helena Pound', 'jigoshop'),
			'SOS' => __('Somalia Shilling', 'jigoshop'),
			'SRD' => __('Suriname Dollar', 'jigoshop'),
			'SVC' => __('El Salvador Colon', 'jigoshop'),
			'SYP' => __('Syria Pound', 'jigoshop'),
			'THB' => __('Thailand Baht', 'jigoshop'),
			'TRL' => __('Turkey Lira', 'jigoshop'),
			'TRY' => __('Turkey Lira', 'jigoshop'),
			'TTD' => __('Trinidad and Tobago Dollar', 'jigoshop'),
			'TVD' => __('Tuvalu Dollar', 'jigoshop'),
			'TWD' => __('Taiwan New Dollar', 'jigoshop'),
			'UAH' => __('Ukraine Hryvna', 'jigoshop'),
			'USD' => __('United States Dollar', 'jigoshop'),
			'UYU' => __('Uruguay Peso', 'jigoshop'),
			'UZS' => __('Uzbekistan Som', 'jigoshop'),
			'VEF' => __('Venezuela Bolivar Fuerte', 'jigoshop'),
			'VND' => __('Viet Nam Dong', 'jigoshop'),
			'XCD' => __('East Caribbean Dollar', 'jigoshop'),
			'YER' => __('Yemen Rial', 'jigoshop'),
			'ZAR' => __('South Africa Rand', 'jigoshop'),
			'ZWD' => __('Zimbabwe Dollar', 'jigoshop'),
		);
		
		asort( $countries );
		return $countries;
	}
	
}