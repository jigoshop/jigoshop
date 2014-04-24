<?php

namespace Jigoshop\Core;

/**
 * Options holder.
 * Use this class instead of manually calling to WordPress options database as it will cache all retrieves and updates to speed-up.
 *
 * @package Jigoshop\Core
 * @author Jigoshop
 */
class Options
{
	private $_options = array();

	public function __construct()
	{
		$this->_loadOptions();
		$this->_addImageSizes();
		add_action('shutdown', array($this, '_updateOptions'));
	}

	public function getImageSizes()
	{
		return apply_filters('jigoshop\\image\\sizes', array(
			'shop_tiny' => array(
				'crop' => apply_filters('jigoshop\\image\\size\\crop', false, 'shop_tiny'),
				'width' => '36',
				'height' => '36',
			),
			'shop_thumbnail' => array(
				'crop' => apply_filters('jigoshop\\image\\size\\crop', false, 'shop_thumbnail'),
				'width' => '90',
				'height' => '90',
			),
			'shop_small' => array(
				'crop' => apply_filters('jigoshop\\image\\size\\crop', false, 'shop_small'),
				'width' => '150',
				'height' => '150',
			),
			'shop_large' => array(
				'crop' => apply_filters('jigoshop\\image\\size\\crop', false, 'shop_large'),
				'width' => '300',
				'height' => '300',
			),
		));
	}

	/**
	 * @param $name string Name of option to retrieve.
	 * @param $default mixed Default value (if not found).
	 * @return mixed Result.
	 */
	public function get($name, $default = null)
	{
		if(isset($this->_options[$name]))
		{
			return $this->_options[$name];
		}
		return $default;
	}

	/**
	 * @param $name string Name of option to update.
	 * @param $value mixed Value to set.
	 */
	public function update($name, $value)
	{
		$this->_options[$name] = $value;
	}

	private function _addImageSizes()
	{
		$sizes = $this->getImageSizes();

		foreach($sizes as $size => $options)
		{
			add_image_size($size, $options['width'], $options['height'], $options['crop']);
		}

//		add_image_size('admin_product_list', 32, 32, $this->get('jigoshop_use_wordpress_tiny_crop', 'no') == 'yes' ? true : false); // TODO: Is this needed?
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _updateOptions()
	{
		update_option('jigoshop', $this->_options);
	}

	private function _loadOptions()
	{
		$this->_options = get_option('jigoshop');
		// TODO: Think on something like "default" options.
	}
}