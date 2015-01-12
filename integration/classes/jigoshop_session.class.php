<?php

class jigoshop_session extends Jigoshop_Base
{
	static $cart_transient_prefix = "jigo_usercart_";
	private static $instance;

	protected function __construct()
	{
		if (!isset($_SESSION['jigoshop'])) {
			$_SESSION['jigoshop'] = array(
				JIGOSHOP_VERSION => array(),
			);
		}
	}

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

	public function __get($key)
	{
		if (isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key])) {
			return $_SESSION['jigoshop'][JIGOSHOP_VERSION][$key];
		}

		return null;
	}

	public function __set($key, $value)
	{
		$_SESSION['jigoshop'][JIGOSHOP_VERSION][$key] = $value;

		return $value;
	}

	public function __isset($key)
	{
		return isset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
	}

	public function __unset($key)
	{
		unset($_SESSION['jigoshop'][JIGOSHOP_VERSION][$key]);
	}
}
