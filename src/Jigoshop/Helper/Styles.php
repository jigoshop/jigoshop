<?php

namespace Jigoshop\Helper;

/**
 * Styles helper.
 *
 * @package Jigoshop\Helper
 * @author Jigoshop
 */
class Styles
{
	/**
	 * Enqueues stylesheet.
	 *
	 * Calls filter `jigoshop_add_style`. If the filter returns empty value the style is omitted.
	 *
	 * Available options:
	 *   * version - Wordpress script version number
	 *   * media - CSS media this script represents
	 *   * page - list of pages to use the style
	 *
	 * Options could be extended by plugins.
	 *
	 * @param string $handle Handle name.
	 * @param bool $src Source file.
	 * @param array $dependencies List of dependencies to the stylesheet.
	 * @param array $options List of options.
	 * @since 2.0
	 */
	public static function add($handle, $src, array $dependencies = array(), array $options = array())
	{
		$page = isset($options['page']) ? (array)$options['page'] : array('all');

		if(is_jigoshop_page($page))
		{
			$handle = apply_filters('jigoshop_add_style', $handle, $src, $dependencies, $options);

			if(!empty($handle))
			{
				$version = isset($options['version']) ? $options['version'] : false;
				$media = isset($options['media']) ? $options['media'] : 'all';
				wp_enqueue_style($handle, $src, $dependencies, $version, $media);
			}
		}
	}
}