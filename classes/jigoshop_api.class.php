<?php
/**
 * Jigoshop API
 *
 * This API class handles the JS-API endpoint requests.
 *
 * @class 		JS_API
 * @version		1.8.0
 * @package		Jigoshop/Classes
 * @category	Class
 * @author 		Jigoshop
 */
class JS_API {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'add_query_vars'), 0 );
		add_action( 'init', array( $this, 'add_endpoint'), 0 );
		add_action( 'parse_request', array( $this, 'api_requests'), 0 );
	}

	/**
	 * add_query_vars function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'js-api';
		return $vars;
	}

	/**
	 * add_endpoint function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_endpoint() {
		add_rewrite_endpoint( 'js-api', EP_ALL );
	}

	/**
	 * API request - Trigger any API requests (handy for third party plugins/gateways).
	 *
	 * @access public
	 * @return void
	 */
	public function api_requests() {
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
}