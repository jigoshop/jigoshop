<?php
use Jigoshop\Integration;

/**
 * Contains the most low level methods & helpers in Jigoshop
 * DISCLAIMER
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
class jigoshop
{
	// TODO: Properly return sizes when Images section gets introduced
	const SHOP_LARGE_W = '300';
	const SHOP_LARGE_H = '300';
	const SHOP_SMALL_W = '150';
	const SHOP_SMALL_H = '150';
	const SHOP_TINY_W = '36';
	const SHOP_TINY_H = '36';
	const SHOP_THUMBNAIL_W = '90';
	const SHOP_THUMBNAIL_H = '90';

	public static $plugin_path;
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

	public function __clone()
	{
		trigger_error("Cloning Singleton's is not allowed.", E_USER_ERROR);
	}


	public function __wakeup()
	{
		trigger_error("Unserializing Singleton's is not allowed.", E_USER_ERROR);
	}

	protected function __construct()
	{
		add_filter('wp_redirect', array($this, 'redirect'), 1, 2);
	}

	/**
	 * Get the current path to Jigoshop
	 *
	 * @deprecated Use JIGOSHOP_DIR constant.
	 * @return  string  local filesystem path with trailing slash
	 */
	public static function jigoshop_path()
	{
		return JIGOSHOP_DIR;
	}

	/**
	 * Get the current version of Jigoshop
	 *
	 * @deprecated Please use JIGOSHOP_VERSION constant instead - it is much faster.
	 * @return  string  current Jigoshop version
	 */
	public static function jigoshop_version()
	{
		return \Jigoshop\Core::VERSION;
	}

	/**
	 * Get the assets url
	 * Provide a filter to allow asset location elsewhere such as on a CDN
	 *
	 * @deprecated Use JIGOSHOP_URL constant instead.
	 * @param null $file
	 * @return  string  url
	 */
	public static function assets_url($file = null)
	{
		return apply_filters('jigoshop_assets_url', JIGOSHOP_URL.'/'.$file);
	}

	/**
	 * Get the plugin url
	 *
	 * @deprecated Use JIGOSHOP_URL constant instead.
	 * @param null $file
	 * @return  string  url
	 */
	public static function plugin_url($file = null)
	{
		return JIGOSHOP_URL.'/'.$file;
	}

	/**
	 * Get the plugin path
	 *
	 * @note plugin_dir_path() does this
	 * @deprecated Use JIGOSHOP_DIR constant instead.
	 * @return  string  url
	 */
	public static function plugin_path()
	{
		if (!empty(self::$plugin_path)) {
			return self::$plugin_path;
		}

		return self::$plugin_path = dirname(dirname(__FILE__));
	}

	/**
	 * Return the URL with https if SSL is on
	 *
	 * @param $url
	 * @return string url
	 */
	public static function force_ssl($url)
	{
		if (is_ssl()) $url = str_replace('http:', 'https:', $url);

		return $url;
	}

	/**
	 * Get a var
	 * Variable is filtered by jigoshop_get_var_{var name}
	 *
	 * @param string $var Variable name to fetch.
	 * @return string Variable value.
	 */
	public static function get_var($var)
	{
		// TODO: Properly return sizes when Images section gets introduced
		$return = null;

		switch ($var) {
			case 'shop_large_w' :
				$return = self::SHOP_LARGE_W;
				break;
			case 'shop_large_h' :
				$return = self::SHOP_LARGE_H;
				break;
			case 'shop_small_w' :
				$return = self::SHOP_SMALL_W;
				break;
			case 'shop_small_h' :
				$return = self::SHOP_SMALL_H;
				break;
			case 'shop_tiny_w' :
				$return = self::SHOP_TINY_W;
				break;
			case 'shop_tiny_h' :
				$return = self::SHOP_TINY_H;
				break;
			case 'shop_thumbnail_w' :
				$return = self::SHOP_THUMBNAIL_W;
				break;
			case 'shop_thumbnail_h' :
				$return = self::SHOP_THUMBNAIL_H;
				break;
		}

		return apply_filters('jigoshop_get_var_'.$var, $return);
	}

	/**
	 * Add a message
	 *
	 * @param string $message A message
	 */
	public static function add_message($message)
	{
		Integration::getMessages()->addNotice($message);
	}

	/**
	 * Output the errors and messages
	 */
	public static function show_messages()
	{
		$messages = Integration::getMessages();
		if ($messages->hasErrors() || $messages->hasNotices()) {
			\Jigoshop\Helper\Render::output('shop/messages', array(
				'messages' => $messages,
			));
		}
	}

	public static function has_errors()
	{
		return Integration::getMessages()->hasErrors();
	}

	public static function has_messages()
	{
		return Integration::getMessages()->hasNotices();
	}

	/** Clear messages and errors from the session data */
	public static function clear_messages()
	{
		Integration::getMessages()->clear();
	}

	public static function nonce_field($action, $referrer = true, $echo = true)
	{
		$name = '_n';
		$action = 'jigoshop-'.$action;

		return wp_nonce_field($action, $name, $referrer, $echo);
	}

	public static function nonce_url($action, $url = '')
	{
		$name = '_n';
		$action = 'jigoshop-'.$action;

		return add_query_arg($name, wp_create_nonce($action), $url);
	}

	/**
	 * Check a nonce and sets jigoshop error in case it is invalid
	 * To fail silently, set the error_message to an empty string
	 *
	 * @param  string $action then nonce action
	 * @return   bool
	 */
	public static function verify_nonce($action)
	{
		$name = '_n';
		$action = 'jigoshop-'.$action;
		$request = array_merge($_GET, $_POST);

		if (!wp_verify_nonce($request[$name], $action)) {
			Integration::getMessages()->addError(__('Action failed. Please refresh the page and retry.', 'jigoshop'));

			return false;
		}

		return true;
	}

	/**
	 * Add an error
	 *
	 * @param string $error An error
	 */
	public static function add_error($error)
	{
		Integration::getMessages()->addError($error);
	}

	/**
	 * Redirection hook which stores messages into session data
	 *
	 * @deprecated do we actually use this anywhere?
	 * @param $location
	 * @param $status
	 * @return string Location
	 */
	public static function redirect($location, $status = null)
	{
		Integration::getMessages()->preserveMessages();

		return apply_filters('jigoshop_session_location_filter', $location);
	}

	// http://www.xe.com/symbols.php
	public static function currency_symbols()
	{
		return \Jigoshop\Helper\Currency::symbols();
	}

	public static function currency_countries()
	{
		return \Jigoshop\Helper\Currency::countries();
	}
}
