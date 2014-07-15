<?php

namespace Jigoshop\Core;

use WPAL\Wordpress;

/**
 * Options holder.
 * Use this class instead of manually calling to WordPress options database as it will cache all retrieves and updates to speed-up.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class Options
{
	/** @var Wordpress */
	private $wp;

	// TODO: Fill default options
	private $defaults = array(
		'cache_mechanism' => 'simple',
		'catalog_per_page' => 9,
		'catalog_sort' => array(
			'order_by' => 'post_date',
			'order' => 'DESC',
			'columns' => 3,
		),
		'disable_css' => 'no',
		'disable_prettyphoto' => 'no',
		'load_frontend_css' => 'yes',
		'complete_processing_orders' => 'no',
		'reset_pending_orders' => 'no',
	);
	private $options = array();
	private $dirty = false;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$this->_loadOptions();
		$this->_addImageSizes();
		$this->wp->addAction('shutdown', array($this, 'saveOptions'));
	}

	public function getImageSizes()
	{
		return array_values($this->wp->applyFilters('jigoshop\\image\\sizes', array(
			'shop_tiny' => array(
				'crop' => $this->wp->applyFilters('jigoshop\\image\\size\\crop', false, 'shop_tiny'),
				'width' => '36',
				'height' => '36',
			),
			'shop_thumbnail' => array(
				'crop' => $this->wp->applyFilters('jigoshop\\image\\size\\crop', false, 'shop_thumbnail'),
				'width' => '90',
				'height' => '90',
			),
			'shop_small' => array(
				'crop' => $this->wp->applyFilters('jigoshop\\image\\size\\crop', false, 'shop_small'),
				'width' => '150',
				'height' => '150',
			),
			'shop_large' => array(
				'crop' => $this->wp->applyFilters('jigoshop\\image\\size\\crop', false, 'shop_large'),
				'width' => '300',
				'height' => '300',
			),
		)));
	}

	/**
	 * @param $name string Name of option to retrieve.
	 * @param $default mixed Default value (if not found).
	 * @return mixed Result.
	 */
	public function get($name, $default = null)
	{
		return $this->_get(explode('.', $name), $this->options, $default);
	}

	private function _get(array $names, array $options, $default = null)
	{
		$name = array_shift($names);

		if (!isset($options[$name])) {
			return $default;
		}

		if (empty($names)) {
			return $options[$name];
		}

		return $this->_get($names, $options[$name], $default);
	}

	/**
	 * @param $name string Name of option to update.
	 * @param $value mixed Value to set.
	 */
	public function update($name, $value)
	{
		$this->options[$name] = $value;
		$this->dirty = true;
	}

	private function _addImageSizes()
	{
		$sizes = $this->getImageSizes();

		foreach ($sizes as $size => $options) {
			$this->wp->addImageSize($size, $options['width'], $options['height'], $options['crop']);
		}

//		add_image_size('admin_product_list', 32, 32, $this->get('jigoshop_use_wordpress_tiny_crop', 'no') == 'yes' ? true : false); // TODO: Is this needed?
	}

	/**
	 * Saves current option values (if needed).
	 */
	public function saveOptions()
	{
		if ($this->dirty) {
			$this->wp->updateOption('jigoshop', $this->options);
		}
	}

	/**
	 * Loads stored options and merges them with default ones.
	 */
	private function _loadOptions()
	{
		$options = (array)$this->wp->getOption('jigoshop');
		$this->options = array_merge($this->defaults, $options);
	}

	/**
	 * Retrieves id of specified Jigoshop page.
	 *
	 * @param $page string Page slug.
	 * @return mixed Page ID.
	 */
	public function getPageId($page)
	{
		return $this->wp->getOption('jigoshop_'.$page.'_id');
	}

	/**
	 * Sets id of specified Jigoshop page.
	 *
	 * @param $page string Page slug.
	 * @param $id int Page ID.
	 */
	public function setPageId($page, $id)
	{
		$this->wp->updateOption('jigoshop_'.$page.'_id', $id);
	}

	/**
	 * @return array List of names of enabled product types.
	 */
	public function getEnabledProductTypes()
	{
		return $this->wp->applyFilters('jigoshop\\product\\types', $this->get('enabled_product_types'));
	}
}