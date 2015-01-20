<?php

namespace Jigoshop\Helper;

use Jigoshop\Frontend\Pages;
use WPAL\Wordpress;

/**
 * Scripts helper.
 *
 * @package Jigoshop\Helper
 * @author Amadeusz Starzykiewicz
 */
class Scripts
{
	/**
	 * Enqueues script.
	 * Calls filter `jigoshop\script\add`. If the filter returns empty value the script is omitted.
	 * Available options:
	 *   * version - Wordpress script version number
	 *   * in_footer - is this script required to add to the footer?
	 *   * page - list of pages to use the script
	 * Options could be extended by plugins.
	 *
	 * @param string $handle Handle name.
	 * @param bool $src Source file.
	 * @param array $dependencies List of dependencies to the script.
	 * @param array $options List of options.
	 * @since 2.0
	 */
	public static function add($handle, $src, array $dependencies = array(), array $options = array())
	{
		$page = isset($options['page']) ? (array)$options['page'] : array('all');

		if (Pages::isOneOf($page)) {
			$handle = apply_filters('jigoshop\script\add', $handle, $src, $dependencies, $options);

			if (!empty($handle)) {
				$version = isset($options['version']) ? $options['version'] : false;
				$footer = isset($options['in_footer']) ? $options['in_footer'] : false;
				wp_enqueue_script($handle, $src, $dependencies, $version, $footer);
			}
		}
	}

	/**
	 * Localizes script.
	 * Calls filter `jigoshop\script\localize`. If the filter returns empty value the script is omitted.
	 *
	 * @param string $handle Handle name.
	 * @param string $variable Variable name.
	 * @param array $value List of values to localize.
	 */
	public static function localize($handle, $variable, array $value)
	{
		$handle = apply_filters('jigoshop\script\localize', $handle, $variable, $value);

		if (!empty($handle)) {
			wp_localize_script($handle, $variable, $value);
		}
	}

	/**
	 * Removes script from enqueued list.
	 * Calls filter `jigoshop_remove_script`. If the filter returns empty value the script is omitted.
	 * Available options:
	 *   * page - list of pages to use the script
	 * Options could be extended by plugins.
	 *
	 * @param string $handle Handle name.
	 * @param array $options List of options.
	 */
	public static function remove($handle, $options)
	{
		$page = isset($options['page']) ? (array)$options['page'] : array('all');

		if (Pages::isOneOf($page)) {
			$handle = apply_filters('jigoshop\script\remove', $handle, $options);

			if (!empty($handle)) {
				wp_deregister_script($handle);
			}
		}
	}
}
