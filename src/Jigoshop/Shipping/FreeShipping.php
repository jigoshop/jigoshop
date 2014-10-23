<?php

namespace Jigoshop\Shipping;

use Jigoshop\Core\Options;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Frontend\Cart;

class FreeShipping implements Method
{
	const NAME = 'free_shipping';

	/** @var array */
	private $options;

	public function __construct(Options $options)
	{
		$this->options = $options->get('shipping.'.self::NAME);
	}

	/**
	 * @return string ID of shipping method.
	 */
	public function getId()
	{
		return self::NAME;
	}

	/**
	 * @return string Human readable name of method.
	 */
	public function getName()
	{
		return __('Free shipping', 'jigoshop');
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		// TODO: Implement isEnabled() method.
		return $this->options['enabled'];
	}

	/**
	 * @return array List of options to display on Shipping settings page.
	 */
	public function getOptions()
	{
		return array(
			array(
				'name' => sprintf('[%s][enabled]', self::NAME),
				'title' => __('Is enabled?', 'jigoshop'),
				'type' => 'checkbox',
				'value' => $this->options['enabled'],
			),
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
	 * @param OrderInterface $order Order to calculate shipping for.
	 * @return float Calculated value of shipping for the order.
	 */
	public function calculate(OrderInterface $order)
	{
		// TODO: Implement calculate() method.
		return 0.0;
	}

	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses()
	{
		return array();
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
