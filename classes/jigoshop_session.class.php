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
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class jigoshop_session extends Jigoshop_Singleton {

	protected function __construct() {
		if ( ! session_id() && ! isset( $_SESSION['travis'] ) ) session_start();
	}

	public function __get( $key ) {
		if( isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]) )
			return $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key];

		return null;
	}

	public function __set( $key, $value ) {
		$_SESSION['jigoshop'][JIGOSHOP_VERSION][$key] = $value;
		return $value;
	}

	public function __isset( $key ) {
		return isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
	}

	public function __unset( $key ) {
		unset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
	}

} // End jigoshop_session