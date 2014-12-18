<?php

namespace Jigoshop\Integration;

use Jigoshop\Admin\Settings\ShippingTab;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Integration;
use Jigoshop\Shipping\Method;
use Jigoshop\Shipping\MultipleMethod;
use Jigoshop\Shipping\Rate;

class Shipping implements MultipleMethod
{
	/** @var \jigoshop_shipping_method */
	private $shipping;
	/** @var array */
	private $options;
	/** @var array */
	private $rates;
	/** @var int */
	private $rate;
	/** @var boolean */
	private $calculated = false;

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

		if ($this->rate !== null) {
			return $this->shipping->get_selected_price($this->rate);
		}

		return $this->shipping->get_cheapest_price();
	}

	/**
	 * Checks whether current method is the one specified with selected rule.
	 *
	 * @param Method $method Method to check.
	 * @param Rate $rate Rate to check.
	 * @return boolean Is this the method?
	 */
	public function is(Method $method, $rate = null)
	{
		if ($method->getId() == $this->getId()) {
			if ($rate != null) {
				return $rate->getId() == $this->rate;
			}

			return true;
		}

		return false;
	}

	/**
	 * Returns list of available shipping rates.
	 *
	 * @return array List of available shipping rates.
	 */
	public function getRates()
	{
		if ($this->rates === null) {
			$this->rates = array();
			$rates = $this->shipping->__get_rates();

			if ($rates === null) {
				$this->shipping->calculate_shipping();
				$rates = $this->shipping->__get_rates();
			}

			foreach ($rates as $id => $source) {
				$rate = new Rate();
				$rate->setId($id);
				$rate->setPrice($source['price']);
				$rate->setName($source['service']);
				$rate->setMethod($this);
				$this->rates[$rate->getId()] = $rate;
			}
		}

		return $this->rates;
	}

	/**
	 * @param $rate int Rate to use.
	 */
	public function setShippingRate($rate)
	{
		$this->shipping->set_selected_service_index($rate);
		$this->rate = $rate;
	}

	/**
	 * @return int Currently used rate.
	 */
	public function getShippingRate()
	{
		return $this->rate;
	}

	/**
	 * @return array Minimal state to fully identify shipping method.
	 */
	public function getState()
	{
		return array(
			'id' => $this->getId(),
			'rate' => $this->rate,
		);
	}

	/**
	 * Restores shipping method state.
	 *
	 * @param array $state State to restore.
	 */
	public function restoreState(array $state)
	{
		if (isset($state['rate'])) {
			$this->rate = (int)$state['rate'];
			$this->shipping->set_selected_service_index($this->rate);
		}
	}
}
