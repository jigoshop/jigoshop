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
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
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
}