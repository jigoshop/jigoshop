<?php

namespace Jigoshop\Payment;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Exception;
use WPAL\Wordpress;

class Dummy implements Method
{
	private $id;
	private $label;

	public function __construct($id, $label = null)
	{
		$this->id = $id;
		$this->label = $label !== null ? $label : $id;
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
		return $this->label;
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		return false;
	}

	/**
	 * @return array List of options to display on Payment settings page.
	 */
	public function getOptions()
	{
		return array();
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
	}

	/**
	 * @param Order $order Order to process payment for.
	 * @return string URL to redirect to.
	 * @throws Exception On any payment error.
	 */
	public function process($order)
	{
		throw new Exception(sprintf(__('Payment gateway "%s" does not exist in the system. This should never happen, please contact Jigoshop support.', 'jigoshop'), $this->id));
	}
}
