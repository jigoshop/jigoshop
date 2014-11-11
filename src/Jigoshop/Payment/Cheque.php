<?php

namespace Jigoshop\Payment;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use WPAL\Wordpress;

class Cheque implements Method
{
	const ID = 'cheque';

	/** @var Wordpress */
	private $wp;
	/** @var array */
	private $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options->get('payment.'.self::ID);
	}

	/**
	 * @return string ID of payment method.
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * @return string Human readable name of method.
	 */
	public function getName()
	{
		return __('Cheque', 'jigoshop');
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		return $this->options['enabled'];
	}

	/**
	 * @return array List of options to display on Payment settings page.
	 */
	public function getOptions()
	{
		return array(
			array(
				'name' => sprintf('[%s][enabled]', self::ID),
				'title' => __('Is enabled?', 'jigoshop'),
				'type' => 'checkbox',
				'value' => $this->options['enabled'],
			),
			// TODO: Other options
		);
	}

	/**
	 * Validates and returns properly sanitized options.
	 *
	 * @param $settings array Input options.
	 * @return array Sanitized result.
	 */
	public function validateOptions($settings)
	{
		$settings['enabled'] = $settings['enabled'] == 'on';

		return $settings;
	}

	/**
	 * Renders method fields and data in Checkout page.
	 *
	 * @return string HTML to display.
	 */
	public function render()
	{
		// TODO: Implement render() method.
	}

	/**
	 * @param Order $order Order to process payment for.
	 * @return bool Is processing successful?
	 */
	public function process($order)
	{
		// TODO: Implement process() method.
	}
}
