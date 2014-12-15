<?php

namespace Jigoshop\Integration;

use Jigoshop\Admin\Settings\ShippingTab;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Integration;
use Jigoshop\Shipping\Method;

class Shipping implements Method
{
	/** @var \jigoshop_shipping_method */
	private $shipping;
	/** @var array */
	private $options;

	public function __construct($shipping)
	{
		$this->shipping = new $shipping();

		$settings = Integration::getOptions();
		$source = $this->shipping->__get_default_options();
		$options = array();

		foreach ($source as $sourceOption) {
			if ($sourceOption['type'] != 'title') {
				$name = ShippingTab::SLUG.'.'.$this->getId().'.'.$sourceOption['id'];
				Options::__addTransformation($sourceOption['id'], $name);
				$option = Helper\Options::parseOption($sourceOption);
				$option['__name'] = $sourceOption['id'];
				$option['name'] = '['.$this->getId().']['.$sourceOption['id'].']';

				if (($value = $settings->get($name)) !== null) {
					switch ($option['type']) {
						case 'checkbox':
							$option['checked'] = $value;
							break;
						default:
							$option['value'] = $value;
					}
				}

				$options[] = $option;
			}
		}

		$this->options = $options;
		// TODO: Any idea how to initialize all options while instance already created?
		$this->shipping = new $shipping();
	}

	/**
	 * @return \jigoshop_shipping_method
	 */
	public function getShipping()
	{
		return $this->shipping;
	}

	/**
	 * @return string ID of shipping method.
	 */
	public function getId()
	{
		return $this->shipping->id;
	}

	/**
	 * @return string Human readable name of method.
	 */
	public function getName()
	{
		if (is_admin()) {
			$source = $this->shipping->__get_default_options();
			if (count($source) > 0 && $source[0]['type'] == 'title') {
				return $source[0]['name'];
			}

			return $this->shipping->id;
		}

		return $this->shipping->title;
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		return $this->shipping->is_available();
	}

	/**
	 * @return array List of options to display on Shipping settings page.
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses()
	{
		return array('standard');
	}

	/**
	 * Validates and returns properly sanitized options.
	 *
	 * @param $settings array Input options.
	 * @return array Sanitized result.
	 */
	public function validateOptions($settings)
	{
		foreach ($this->options as $option) {
			if ($option['type'] == 'checkbox') {
				$settings[$option['__name']] = $settings[$option['__name']] == 'on';
			}
		}

		return $settings;
	}

	/**
	 * @param OrderInterface $order Order to calculate shipping for.
	 * @return float Calculates value of shipping for the order.
	 */
	public function calculate(OrderInterface $order)
	{
		$this->shipping->calculate_shipping();
		return $this->shipping->get_cheapest_price();
	}

	/**
	 * @return array Minimal state to fully identify shipping method.
	 */
	public function getState()
	{
		return array(
			'id' => $this->getId(),
		);
	}
}
