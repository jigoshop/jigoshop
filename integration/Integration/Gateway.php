<?php

namespace Integration;

use Jigoshop\Entity\Order;
use Jigoshop\Payment\Method;

class Gateway implements Method
{
	/** @var \jigoshop_payment_gateway */
	private $gateway;

	public function __construct(\jigoshop_payment_gateway $gateway)
	{
		$this->gateway = $gateway;
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
		return $this->id;
	}

	/**
	 * @return string Human readable name of method.
	 */
	public function getName()
	{
		return $this->title;
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @return array List of options to display on Payment settings page.
	 */
	public function getOptions()
	{
		$options = array();
		$source = $this->gateway->__get_default_options();

		foreach ($source as $option) {
			$options[] = array(
				'title' => isset($option['name']) ? $option['name'] : false,
				'name' => isset($option['id']) ? $option['id'] : '',
				'description' => isset($option['desc']) ? $option['desc'] : false,
				'tip' => isset($option['tip']) ? $option['tip'] : false,
				'value' => isset($option['std']) ? $option['std'] : false,
				'type' => isset($option['type']) ? $option['type'] : 'text',
				'options' => isset($option['choices']) ? $option['choices'] : array(),
				// TODO: classes based on 'extra' field
				// TODO: Some additional options from 'extra' field
			);
		}

		return $options;
	}

	/**
	 * Validates and returns properly sanitized options.
	 *
	 * @param $settings array Input options.
	 * @return array Sanitized result.
	 */
	public function validateOptions($settings)
	{
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
