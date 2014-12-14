<?php

namespace Jigoshop\Integration;

use Jigoshop\Admin\Settings\PaymentTab;
use Jigoshop\Entity\Order;
use Jigoshop\Exception;
use Jigoshop\Integration;
use Jigoshop\Payment\Method;

class Gateway implements Method
{
	/** @var \jigoshop_payment_gateway */
	private $gateway;
	/** @var array */
	private $options;

	public function __construct($gateway)
	{
		$this->gateway = new $gateway();

		$settings = Integration::getOptions();
		$source = $this->gateway->__get_default_options();
		$options = array();

		foreach ($source as $sourceOption) {
			if ($sourceOption['type'] != 'title') {
				$name = PaymentTab::SLUG.'.'.$this->getId().'.'.$sourceOption['id'];
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
		$this->gateway = new $gateway();
	}

	/**
	 * @return \jigoshop_payment_gateway
	 */
	public function getGateway()
	{
		return $this->gateway;
	}

	/**
	 * @return string ID of payment method.
	 */
	public function getId()
	{
		return $this->gateway->id;
	}

	/**
	 * @return string Human readable name of method.
	 */
	public function getName()
	{
		if (is_admin()) {
			$source = $this->gateway->__get_default_options();
			if (count($source) > 0 && $source[0]['type'] == 'title') {
				return $source[0]['name'];
			}

			return $this->gateway->id;
		}

		return $this->gateway->title;
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		return $this->gateway->enabled;
	}

	/**
	 * @return array List of options to display on Payment settings page.
	 */
	public function getOptions()
	{
		return $this->options;
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
	 * Renders method fields and data in Checkout page.
	 */
	public function render()
	{
		if ($this->gateway->has_fields || $this->gateway->description) {
			$this->gateway->payment_fields();
		}
	}

	/**
	 * @param Order $order Order to process payment for.
	 * @return string URL to redirect to.
	 * @throws Exception On any payment error.
	 */
	public function process($order)
	{
		$result = $this->gateway->process_payment($order->getId());

		// Redirect to success/confirmation/payment page
		if (isset($result['result']) && $result['result'] == 'success') {
			return $result['redirect'];
		}

		return '';
	}
}
