<?php
/**
 * Jigoshop URL Request API
 *
 * This API class handles the 'js-api' endpoint requests.
 *
 * eg: $notify_url = add_query_arg( 'js-api', 'JS_Gateway_Paypal', home_url( '/' ) );
 * eg: $notify_url = jigoshop_request_api::query_request( '?js-api=JS_Gateway_Paypal', false );
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
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */


class jigoshop_request_api extends jigoshop_singleton {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_filter( 'query_vars', array( $this, 'add_query_vars'), 0 );
		add_action( 'init', array( $this, 'add_endpoint'), 1 );
		add_action( 'parse_request', array( $this, 'api_requests'), 0 );

	}


	/**
	 * add_query_vars function.
	 *
	 * @access public
	 * @return void
	 */
	public static function add_query_vars( $vars ) {

		$vars[] = 'js-api';
		return $vars;

	}


	/**
	 * add_endpoint function.
	 *
	 * @access public
	 * @return void
	 */
	public static function add_endpoint() {

		add_rewrite_endpoint( 'js-api', EP_ALL );

	}


	/**
	 * API request - Trigger any API requests (handy for third party plugins/gateways).
	 *
	 * @access public
	 * @return void
	 */
	public static function api_requests() {

		global $wp;

		if ( ! empty( $_GET['js-api'] ) )
			$wp->query_vars['js-api'] = $_GET['js-api'];

		if ( ! empty( $wp->query_vars['js-api'] ) ) {
			// Buffer, we won't want any output here
			ob_start();

			// Get API trigger
			$api = strtolower( esc_attr( $wp->query_vars['js-api'] ) );

			// Load class if exists
			if ( class_exists( $api ) )
				$api_class = new $api();

			// Trigger actions
			do_action( 'jigoshop_api_' . $api );

			// Done, clear buffer and exit
			ob_end_clean();
			die('1');
		}

	}


	/**
	 * Return the cleaned and schemed Jigoshop API URL for a given request
	 *
	 * eg: $this->notify_url = jigoshop_request_api::query_request( '?js-api=JS_Gateway_Paypal', false );
	 *
	 * @access public
	 * @param mixed $request
	 * @param mixed $ssl (default: null)
	 * @return string
	 */
	public static function query_request( $request, $ssl = null ) {

		if ( is_null( $ssl ) ) {
			$scheme = parse_url( get_option( 'home' ), PHP_URL_SCHEME );
		} elseif ( $ssl ) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}

		return esc_url_raw( home_url( '/', $scheme ) . $request );

	}

}