<?php

namespace Integration;

use Integration\Admin\Settings\Tab;

class Options implements \Jigoshop_Options_Interface
{
	private static $_transformations = array();
	private static $_basicTransformations = false;

	public function __construct()
	{
		if (!self::$_basicTransformations) {
			self::$_basicTransformations = true;

			self::__addTransformation('jigoshop_currency', 'general.currency');
		}
	}

	public static function __addTransformation($from, $to)
	{
		self::$_transformations[$from] = $to;
	}

	public function update_options()
	{
		// Empty
	}

	public function add($name, $value)
	{
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}

		\Integration::getOptions()->update($name, $value);
	}

	public function add_option($name, $value)
	{
		$this->add($name, $value);
	}

	public function get($name, $default = null)
	{
//		echo '<pre>'; var_dump(\Integration::getOptions()->getAll()); exit; var_dump($name, isset(self::$_transformations[$name])); echo '</pre>';
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}
//		echo '<pre>'; var_dump($name, \Integration::getOptions()->get($name, $default)); echo '</pre>';

		return \Integration::getOptions()->get($name, $default);
	}

	public function get_option($name, $default = null)
	{
		return $this->get($name, $default);
	}

	public function set($name, $value)
	{
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}

		\Integration::getOptions()->update($name, $value);
	}

	public function set_option($name, $value)
	{
		$this->set($name, $value);
	}

	public function delete($name)
	{
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}

		return \Integration::getOptions()->remove($name);
	}

	public function delete_option($name)
	{
		return $this->delete($name);
	}

	public function exists($name)
	{
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}

		return \Integration::getOptions()->exists($name);
	}

	public function exists_option($name)
	{
		return $this->exists($name);
	}

	public function install_external_options_tab($tab, $options)
	{
		\Integration::getAdminSettings()->addTab(new Tab($tab, $options));
	}

	/**
	 * Install additional default options for parsing onto a specific Tab
	 * Shipping methods, Payment gateways and Extensions would use this
	 *
	 * @param  string  The name of the Tab ('tab') to install onto
	 * @param  array  The array of options to install
	 * @since  1.3
	 */
	public function install_external_options_onto_tab($tab, $options)
	{
		// TODO: Implement
	}

	/**
	 * Install additional default options for parsing after a specific option ID
	 * Extensions would use this
	 *
	 * @param  string  The name of the ID  to install -after-
	 * @param  array  The array of options to install
	 * @since  1.3
	 */
	public function install_external_options_after_id($insert_after_id, $options)
	{
		// TODO: Implement
	}

	public function get_current_options()
	{
		return \Integration::getOptions()->getAll();
	}

	public function get_default_options()
	{
		return \Integration::getOptions()->getDefaults();
	}
}
