<?php
/**
 * Session Class
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
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
class jigoshop_session extends Jigoshop_Singleton {

	static $cart_transient_prefix = "jigo_usercart_";

	protected function __construct() {
		if ( ! session_id()) session_start();
		if(!isset($_SESSION['jigoshop'])){
			$_SESSION['jigoshop'] = array(
				JIGOSHOP_VERSION => array(),
			);
		}
		add_action('shutdown', array($this, 'close_session'));
	}

	public function close_session(){
		session_write_close();
	}

	public function __get($key)
	{
		// Intercept requests to the cart session variable and use the WordPress Transients API
		// for cart persistence among authenticated users. If we're not logged in or can't find
		// a cart there, fall back to the session. This also ensures customers don't lose their
		// carts the first time this file is upgraded to use transients.
		if ($key == 'cart') {

			if (is_checkout() && isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key])) {
				$this->__set($key, $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
				$cart =  $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key];
				unset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
				return $cart;
			}
			$current_user_id = get_current_user_id();
			if ($current_user_id > 0) {
				switch (self::get_options()->get('jigoshop_cart_after_login')) {
					case 'load_saved':
						if ($cart = get_transient(self::$cart_transient_prefix.$current_user_id)) {
							return $cart;
						}
						break;
					case 'merge':
						$cart = get_transient(self::$cart_transient_prefix.$current_user_id);
						if ($cart && isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key])) {
							$cart = array_merge($cart, $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
							$this->__set($key, $cart);
							unset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
							return $cart;
						} else if (!$cart && isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key])) {
							$this->__set($key, $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
							$cart =  $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key];
							unset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
							return $cart;
						} else if ($cart) {
							return $cart;
						}
						break;
					default:
						if (isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key])) {
							$this->__set($key, $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
							$cart =  $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key];
							unset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
							return $cart;
						} else if ($cart = get_transient(self::$cart_transient_prefix.$current_user_id)) {
							return $cart;
						}
						break;
				}
			}
		}
		if (isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key])) {
			return $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key];
		}

		return null;
	}

	public function __set( $key, $value ) {
		if ($key=='cart') {
			global $current_user;
			if (@$current_user->ID) {
				set_transient(self::$cart_transient_prefix.$current_user->ID, $value, 31536000); // 1 year
				return $value;
			}
		}
		$_SESSION['jigoshop'][JIGOSHOP_VERSION][$key] = $value;
		return $value;
	}

	public function __isset( $key ) {
		if ($key=='cart') {
			global $current_user;
			if (@$current_user->ID) {
				return !!get_transient(self::$cart_transient_prefix.$current_user->ID);
			}
		}
		return isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
	}

	public function __unset( $key ) {
		if ($key=='cart') {
			global $current_user;
			if (@$current_user->ID) {
				delete_transient(self::$cart_transient_prefix.$current_user->ID);
			}
		}
		unset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
	}

} // End jigoshop_session