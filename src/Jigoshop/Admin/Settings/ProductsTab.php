<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use WPAL\Wordpress;

/**
 * Products tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class ProductsTab implements TabInterface
{
	const SLUG = 'products';

	/** @var array */
	private $options;
	/** @var Messages */
	private $messages;
	/** @var array */
	private $weightUnit;
	/** @var array */
	private $dimensionUnit;

	public function __construct(Wordpress $wp, Options $options, Messages $messages)
	{
		$this->options = $options->get(self::SLUG);
		$this->messages = $messages;

		$this->weightUnit = array(
			'kg' => __('Kilograms', 'jigoshop'),
			'lbs' => __('Pounds', 'jigoshop'),
		);
		$this->dimensionUnit = array(
			'cm' => __('Centimeters', 'jigoshop'),
			'in' => __('Inches', 'jigoshop'),
		);
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Products', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return self::SLUG;
	}

	/**
	 * @return array List of items to display.
	 */
	public function getSections()
	{
		return array(
			array(
				'title' => __('Units', 'jigoshop'),
				'id' => 'units',
				'fields' => array(
					array(
						'name' => '[weight_unit]',
						'title' => __('Weight units', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['weight_unit'],
						'options' => $this->weightUnit,
					),
					array(
						'name' => '[dimensions_unit]',
						'title' => __('Dimensions unit', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['dimensions_unit'],
						'options' => $this->dimensionUnit,
					),
				),
			),
			array(
				'title' => __('Stock management', 'jigoshop'),
				'id' => 'stock_management',
				'fields' => array(
					array(
						'name' => '[manage_stock]',
						'title' => __('Enable for all items', 'jigoshop'),
						'description' => __("You can always disable management per item, it's just default value.", 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['manage_stock'],
					),
					array(
						'name' => '[show_stock]',
						'title' => __('Show stock amounts', 'jigoshop'),
						'description' => __('This option allows you to show available amounts on product page.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['show_stock'],
					),
					array(
						'name' => '[low_stock_threshold]',
						'title' => __('Low stock threshold', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['low_stock_threshold'],
					),
					// TODO: Add support for hiding out of stock items
//					array(
//						'name' => '[hide_out_of_stock]',
//						'title' => __('Hide out of stock products?', 'jigoshop'),
//						'description' => __('This option allows you to hide products which are out of stock from lists.', 'jigoshop'),
//						'type' => 'checkbox',
//						'checked' => $this->options['hide_out_of_stock'],
//					),
				),
			),
			array(
				'title' => __('Stock notifications', 'jigoshop'),
				'id' => 'stock_notifications',
				'fields' => array(
					array(
						'name' => '[notify_low_stock]',
						'title' => __('Low stock', 'jigoshop'),
						'description' => __('Notify when product reaches low stock', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['notify_low_stock'],
					),
					array(
						'name' => '[notify_out_of_stock]',
						'title' => __('Out of stock', 'jigoshop'),
						'description' => __('Notify when product becomes out of stock', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['notify_out_of_stock'],
					),
					// TODO: Backorders notifications
//					array(
//						'name' => '[notify_on_backorders]',
//						'title' => __('On backorders', 'jigoshop'),
////						'description' => __('Notify when product reaches backorders', 'jigoshop'), // TODO: How to describe this?
//						'type' => 'checkbox',
//						'checked' => $this->options['notify_on_backorders'],
//					),
				),
			),
			array(
				'title' => __('Images', 'jigoshop'),
				'description' => __('Changing any of those settings will affect image sizes on your page. If you have cropping enabled you will need to regenerate thumbnails.', 'jigoshop'),
				'id' => 'images',
				'fields' => array(
					array(
						'name' => '[images][tiny][width]',
						'title' => __('Tiny image width', 'jigoshop'),
						'tip' => __('Used in cart for product image.', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['images']['tiny']['width'],
					),
					array(
						'name' => '[images][tiny][height]',
						'title' => __('Tiny image height', 'jigoshop'),
						'tip' => __('Used in cart for product image.', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['images']['tiny']['height'],
					),
					array(
						'name' => '[images][tiny][crop]',
						'title' => __('Crop tiny image', 'jigoshop'),
						'tip' => __('Leave disabled to scale images proportionally, enable to do real cropping.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['images']['tiny']['crop'],
					),
					array(
						'name' => '[images][thumbnail][width]',
						'title' => __('Thumbnail image width', 'jigoshop'),
						'tip' => __('Used in single product view for other images thumbnails.', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['images']['thumbnail']['width'],
					),
					array(
						'name' => '[images][thumbnail][height]',
						'title' => __('Thumbnail image height', 'jigoshop'),
						'tip' => __('Used in single product view for other images thumbnails.', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['images']['thumbnail']['height'],
					),
					array(
						'name' => '[images][thumbnail][crop]',
						'title' => __('Crop thumbnail image', 'jigoshop'),
						'tip' => __('Leave disabled to scale images proportionally, enable to do real cropping.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['images']['thumbnail']['crop'],
					),
					array(
						'name' => '[images][small][width]',
						'title' => __('Small image width', 'jigoshop'),
						'tip' => __('Used in catalog for product thumbnails.', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['images']['small']['width'],
					),
					array(
						'name' => '[images][small][height]',
						'title' => __('Small image height', 'jigoshop'),
						'tip' => __('Used in catalog for product thumbnails.', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['images']['small']['height'],
					),
					array(
						'name' => '[images][small][crop]',
						'title' => __('Crop small image', 'jigoshop'),
						'tip' => __('Leave disabled to scale images proportionally, enable to do real cropping.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['images']['small']['crop'],
					),
					array(
						'name' => '[images][large][width]',
						'title' => __('Large image width', 'jigoshop'),
						'tip' => __('Used in single product view for featured image.', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['images']['large']['width'],
					),
					array(
						'name' => '[images][large][height]',
						'title' => __('Large image height', 'jigoshop'),
						'tip' => __('Used in single product view for featured image.', 'jigoshop'),
						'type' => 'number',
						'value' => $this->options['images']['large']['height'],
					),
					array(
						'name' => '[images][large][crop]',
						'title' => __('Crop large image', 'jigoshop'),
						'tip' => __('Leave disabled to scale images proportionally, enable to do real cropping.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['images']['large']['crop'],
					),
				),
			),
		);
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate(array $settings)
	{
		if (!in_array($settings['weight_unit'], array_keys($this->weightUnit))) {
			$this->messages->addWarning(sprintf(__('Invalid weight unit: "%s". Value set to %s.', 'jigoshop'), $settings['weight_unit'], $this->weightUnit['kg']));
			$settings['weight_unit'] = 'kg';
		}
		if (!in_array($settings['dimensions_unit'], array_keys($this->dimensionUnit))) {
			$this->messages->addWarning(sprintf(__('Invalid dimensions unit: "%s". Value set to %s.', 'jigoshop'), $settings['dimensions_unit'], $this->dimensionUnit['cm']));
			$settings['dimensions_unit'] = 'cm';
		}

		$settings['manage_stock'] = $settings['manage_stock'] == 'on';
		$settings['show_stock'] = $settings['show_stock'] == 'on';
		$settings['hide_out_of_stock'] = $settings['hide_out_of_stock'] == 'on';

		$settings['low_stock_threshold'] = (int)$settings['low_stock_threshold'];
		if ($settings['low_stock_threshold'] < 0) {
			$this->messages->addWarning(sprintf(__('Invalid low stock threshold: "%d". Value set to 2.', 'jigoshop'), $settings['low_stock_threshold']));
			$settings['low_stock_threshold'] = 2;
		}

		$settings['notify_low_stock'] = $settings['notify_low_stock'] == 'on';
		$settings['notify_out_of_stock'] = $settings['notify_out_of_stock'] == 'on';
		$settings['notify_on_backorders'] = $settings['notify_on_backorders'] == 'on';

		$settings['images']['tiny'] = array(
			'width' => (int)$settings['images']['tiny']['width'],
			'height' => (int)$settings['images']['tiny']['height'],
			'crop' => $settings['images']['tiny']['crop'] == 'on',
		);
		$settings['images']['thumbnail'] = array(
			'width' => (int)$settings['images']['thumbnail']['width'],
			'height' => (int)$settings['images']['thumbnail']['height'],
			'crop' => $settings['images']['thumbnail']['crop'] == 'on',
		);
		$settings['images']['small'] = array(
			'width' => (int)$settings['images']['small']['width'],
			'height' => (int)$settings['images']['small']['height'],
			'crop' => $settings['images']['small']['crop'] == 'on',
		);
		$settings['images']['large'] = array(
			'width' => (int)$settings['images']['large']['width'],
			'height' => (int)$settings['images']['large']['height'],
			'crop' => $settings['images']['large']['crop'] == 'on',
		);

		return $settings;
	}
}
