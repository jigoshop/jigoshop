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
					array(
						'name' => '[hide_out_of_stock]',
						'title' => __('Hide out of stock products?', 'jigoshop'),
						'description' => __('This option allows you to hide products which are out of stock from lists.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['hide_out_of_stock'],
					),
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
					array(
						'name' => '[notify_on_backorders]',
						'title' => __('On backorders', 'jigoshop'),
//						'description' => __('Notify when product reaches backorders', 'jigoshop'), // TODO: How to describe this?
						'type' => 'checkbox',
						'checked' => $this->options['notify_on_backorders'],
					),
				),
			),
//			array( // TODO: Images section
//				'title' => __('Images', 'jigoshop'),
//				'id' => 'images',
//				'fields' => array(
//					array(
//						'name' => '[guest_purchases]',
//						'title' => __('Allow guest purchases', 'jigoshop'),
//						'type' => 'checkbox',
//						'checked' => $this->options['guest_purchases'],
//					),
//					array(
//						'name' => '[show_login_form]',
//						'title' => __('Show login form', 'jigoshop'),
//						'type' => 'checkbox',
//						'checked' => $this->options['show_login_form'],
//					),
//					array(
//						'name' => '[allow_registration]',
//						'title' => __('Allow registration', 'jigoshop'),
//						'type' => 'checkbox',
//						'checked' => $this->options['show_login_form'],
//					),
//				),
//			),
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

		return $settings;
	}
}
