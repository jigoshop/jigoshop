<?php

namespace Jigoshop\Helper;

use Jigoshop\Pages;

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
	 * Calls filter `jigoshop_add_script`. If the filter returns empty value the script is omitted.
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

		if (Pages::isOneOfPages($page)) {
			$handle = apply_filters('jigoshop_add_script', $handle, $src, $dependencies, $options);

			if (!empty($handle)) {
				$version = isset($options['version']) ? $options['version'] : false;
				$footer = isset($options['in_footer']) ? $options['in_footer'] : false;
				wp_enqueue_script($handle, $src, $dependencies, $version, $footer);
			}
		}
	}
}