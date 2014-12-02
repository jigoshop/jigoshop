<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;
use Jigoshop\Payment\Method;
use Jigoshop\Service\PaymentServiceInterface;

/**
 * Payment tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class PaymentTab implements TabInterface
{
	const SLUG = 'payment';

	/** @var array */
	private $options;
	/** @var PaymentServiceInterface */
	private $paymentService;

	public function __construct(Options $options, PaymentServiceInterface $paymentService)
	{
		$this->options = $options->get(self::SLUG);
		$this->paymentService = $paymentService;
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Payment', 'jigoshop');
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
		$options = array();

		foreach ($this->paymentService->getAvailable() as $method) {
			/** @var $method Method */
			$options[] = array(
				'title' => $method->getName(),
				'id' => $method->getId(),
				'fields' => $method->getOptions(),
			);
		}

		return $options;
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
		foreach ($this->paymentService->getAvailable() as $method) {
			/** @var $method Method */
			$settings[$method->getId()] = $method->validateOptions($settings[$method->getId()]);
		}

		return $settings;
	}
}
