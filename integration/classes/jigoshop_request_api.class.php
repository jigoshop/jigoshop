<?php

class jigoshop_request_api
{
	private static $instance;

	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function reset()
	{
		self::$instance = null;
	}

	/**
	 * Return the cleaned and schemed Jigoshop API URL for a given request
	 * eg: $this->notify_url = jigoshop_request_api::query_request( '?js-api=JS_Gateway_Paypal', false );
	 *
	 * @access public
	 * @param mixed $request
	 * @param mixed $ssl (default: null)
	 * @return string
	 */
	public static function query_request($request, $ssl = null)
	{
		if (is_null($ssl)) {
			$scheme = parse_url(get_option('home'), PHP_URL_SCHEME);
		} elseif ($ssl) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}

		return esc_url_raw(home_url('/', $scheme).$request);

	}

	public function __clone()
	{
		trigger_error("Cloning Singleton's is not allowed.", E_USER_ERROR);
	}

	public function __wakeup()
	{
		trigger_error("Unserializing Singleton's is not allowed.", E_USER_ERROR);
	}
}
