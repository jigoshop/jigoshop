<?php

namespace Jigoshop\Helper;

use Jigoshop\Frontend\Pages;
use WPAL\Wordpress;

/**
 * Styles helper.
 *
 * @package Jigoshop\Helper
 * @author Amadeusz Starzykiewicz
 */
class Styles
{
	/**
	 * Registers stylesheet.
	 * Calls filter `jigoshop\style\register`. If the filter returns empty value the style is omitted.
	 * Available options:
	 *   * version - Wordpress script version number
	 *   * media - CSS media this script represents
	 *   * page - list of pages to use the style
	 * Options could be extended by plugins.
	 *
	 * @param string $handle Handle name.
	 * @param bool $src Source file.
	 * @param array $dependencies List of dependencies to the stylesheet.
	 * @param array $options List of options.
	 * @since 2.0
	 */
	public static function register($handle, $src, array $dependencies = array(), array $options = array())
	{
		$page = isset($options['page']) ? (array)$options['page'] : array('all');

		if (Pages::isOneOf($page)) {
			$handle = apply_filters('jigoshop\style\register', $handle, $src, $dependencies, $options);

			if (!empty($handle)) {
				$version = isset($options['version']) ? $options['version'] : false;
				$media = isset($options['media']) ? $options['media'] : 'all';
				wp_register_style($handle, $src, $dependencies, $version, $media);
			}
		}
	}

	/**
	 * Enqueues stylesheet.
	 * Calls filter `jigoshop\style\add`. If the filter returns empty value the style is omitted.
	 * Available options:
	 *   * version - Wordpress script version number
	 *   * media - CSS media this script represents
	 *   * page - list of pages to use the style
	 * Options could be extended by plugins.
	 *
	 * @param string $handle Handle name.
	 * @param bool $src Source file.
	 * @param array $dependencies List of dependencies to the stylesheet.
	 * @param array $options List of options.
	 * @since 2.0
	 */
	public static function add($handle, $src = false, array $dependencies = array(), array $options = array())
	{
		$page = isset($options['page']) ? (array)$options['page'] : array('all');

		if (Pages::isOneOf($page)) {
			$handle = apply_filters('jigoshop\style\add', $handle, $src, $dependencies, $options);

			if (!empty($handle)) {
				$version = isset($options['version']) ? $options['version'] : false;
				$media = isset($options['media']) ? $options['media'] : 'all';
				wp_enqueue_style($handle, $src, $dependencies, $version, $media);
			}
		}
	}

	/**
	 * Removes style from enqueued list.
	 * Calls filter `jigoshop_remove_style`. If the filter returns empty value the style is omitted.
	 * Available options:
	 *   * page - list of pages to use the style
	 * Options could be extended by plugins.
	 *
	 * @param string $handle Handle name.
	 * @param array $options List of options.
	 */
	public static function remove($handle, $options)
	{
		$page = isset($options['page']) ? (array)$options['page'] : array('all');

		if (Pages::isOneOf($page)) {
			$handle = apply_filters('jigoshop\style\remove', $handle, $options);

			if (!empty($handle)) {
				wp_deregister_style($handle);
			}
		}
	}
}
