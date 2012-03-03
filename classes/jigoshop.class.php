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
 * @package		Jigoshop
 * @category	Core
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */

class jigoshop extends jigoshop_singleton {

	private static $_cache;

	public static $errors = array();
	public static $messages = array();

	public static $plugin_url;
	public static $plugin_path;

	const SHOP_SMALL_W = '150';
	const SHOP_SMALL_H = '150';
	const SHOP_TINY_W = '36';
	const SHOP_TINY_H = '36';
	const SHOP_THUMBNAIL_W = '90';
	const SHOP_THUMBNAIL_H = '90';
	const SHOP_LARGE_W = '300';
	const SHOP_LARGE_H = '300';

	/** constructor */
	protected function __construct() {

		if (isset(jigoshop_session::instance()->errors)) self::$errors = jigoshop_session::instance()->errors;
		if (isset(jigoshop_session::instance()->messages)) self::$messages = jigoshop_session::instance()->messages;

		unset( jigoshop_session::instance()->messages );
		unset( jigoshop_session::instance()->errors );

		// uses jigoshop_base_class to provide class address for the filter
		self::add_filter( 'wp_redirect', 'redirect', 1, 2 );
	}

	/**
	 * This is deprecated as of ver 0.9.9.2 - use jigoshop_product.class version.
	 *
	 * @deprecated
	 */
    public static function getAttributeTaxonomies() {
        return jigoshop_product::getAttributeTaxonomies();
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
		$return = '';
		switch ($var) :
			case "shop_small_w" : $return = self::SHOP_SMALL_W; break;
			case "shop_small_h" : $return = self::SHOP_SMALL_H; break;
			case "shop_tiny_w" : $return = self::SHOP_TINY_W; break;
			case "shop_tiny_h" : $return = self::SHOP_TINY_H; break;
			case "shop_thumbnail_w" : $return = self::SHOP_THUMBNAIL_W; break;
			case "shop_thumbnail_h" : $return = self::SHOP_THUMBNAIL_H; break;
			case "shop_large_w" : $return = self::SHOP_LARGE_W; break;
			case "shop_large_h" : $return = self::SHOP_LARGE_H; break;
		endswitch;
		return apply_filters( 'jigoshop_get_var_'.$var, $return );
	}

	/**
	 * Add an error
	 *
	 * @param   string	error
	 */
	public static function add_error( $error ) { self::$errors[] = $error; }

	/**
	 * Add a message
	 *
	 * @param   string	message
	 */
	public static function add_message( $message ) { self::$messages[] = $message; }

	/** Clear messages and errors from the session data */
	public static function clear_messages() {
		self::$errors = self::$messages = array();
		unset( jigoshop_session::instance()->messages );
		unset( jigoshop_session::instance()->errors );
	}

	/**
	 * Get error count
	 *
	 * @return   int
	 */
	public static function error_count() { return sizeof(self::$errors); }

	/**
	 * Get message count
	 *
	 * @return   int
	 */
	public static function message_count() { return sizeof(self::$messages); }

	/**
	 * Output the errors and messages
	 *
	 * @return   bool
	 */
	public static function show_messages() {

		if (isset(self::$errors) && sizeof(self::$errors)>0) :
			echo '<div class="jigoshop_error">'.self::$errors[0].'</div>';
			self::clear_messages();
			return true;
		elseif (isset(self::$messages) && sizeof(self::$messages)>0) :
			echo '<div class="jigoshop_message">'.self::$messages[0].'</div>';
			self::clear_messages();
			return true;
		else :
			return false;
		endif;
	}

	public static function nonce_field($action, $referer = true , $echo = true) {

		$name = '_n';
		$action = 'jigoshop-' . $action;

		return wp_nonce_field($action, $name, $referer, $echo);

	}

	public static function nonce_url($action, $url = '') {

		$name = '_n';
		$action = 'jigoshop-' . $action;

		$url = add_query_arg( $name, wp_create_nonce( $action ), $url);

		return $url;
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
	public static function verify_nonce($action, $method='_POST', $error_message = false) {

		$name = '_n';
		$action = 'jigoshop-' . $action;

		if( $error_message === false ) $error_message = __('Action failed. Please refresh the page and retry.', 'jigoshop');

		if(!in_array($method, array('_GET', '_POST', '_REQUEST'))) $method = '_POST';

		if ( isset($_REQUEST[$name]) && wp_verify_nonce($_REQUEST[$name], $action) ) return true;

		if( $error_message ) jigoshop::add_error( $error_message );

		return false;

	}

	/**
	 * Redirection hook which stores messages into session data
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

	public static function shortcode_wrapper ($function, $atts=array()) {
		if( $content = jigoshop::cache_get( $function . '-shortcode', $atts ) ) return $content;

		ob_start();
		call_user_func($function, $atts);
		return jigoshop::cache( $function . '-shortcode', ob_get_clean(), $atts);
	}

	/**
	 * Cache API
	 */

	public static function cache( $id, $data, $args=array() ) {

		if( ! isset(self::$_cache[ $id ]) ) self::$_cache[ $id ] = array();

		if( empty($args) ) self::$_cache[ $id ][0] = $data;
		else self::$_cache[ $id ][ serialize($args) ] = $data;

		return $data;

	}
	public static function cache_get( $id, $args=array() ) {

		if( ! isset(self::$_cache[ $id ]) ) return null;

		if( empty($args) && isset(self::$_cache[ $id ][0]) ) return self::$_cache[ $id ][0];
		elseif ( isset(self::$_cache[ $id ][ serialize($args) ] ) ) return self::$_cache[ $id ][ serialize($args) ];

	}
}