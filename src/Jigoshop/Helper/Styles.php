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
	/** @var \Jigoshop\Frontend\Pages */
	private $pages;
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp, Pages $pages)
	{
		$this->wp = $wp;
		$this->pages = $pages;
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
	public function add($handle, $src, array $dependencies = array(), array $options = array())
	{
		$page = isset($options['page']) ? (array)$options['page'] : array('all');

		if ($this->pages->isOneOf($page)) {
			$handle = $this->wp->applyFilters('jigoshop\style\add', $handle, $src, $dependencies, $options);

			if (!empty($handle)) {
				$version = isset($options['version']) ? $options['version'] : false;
				$media = isset($options['media']) ? $options['media'] : 'all';
				$this->wp->wpEnqueueStyle($handle, $src, $dependencies, $version, $media);
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
	public function remove($handle, $options)
	{
		$page = isset($options['page']) ? (array)$options['page'] : array('all');

		if ($this->pages->isOneOf($page)) {
			$handle = $this->wp->applyFilters('jigoshop\style\remove', $handle, $options);

			if (!empty($handle)) {
				$this->wp->wpDeregisterStyle($handle);
			}
		}
	}
}
