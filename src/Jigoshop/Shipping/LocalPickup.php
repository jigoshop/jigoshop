<?php

namespace Jigoshop\Shipping;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Service\CartServiceInterface;
use WPAL\Wordpress;

class LocalPickup implements Method
{
	const NAME = 'local_pickup';

	/** @var Wordpress */
	private $wp;
	/** @var array */
	private $options;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var string */
	private $baseCountry;

	public function __construct(Wordpress $wp, Options $options, CartServiceInterface $cartService)
	{
		$this->wp = $wp;
		$this->options = $options->get('shipping.'.self::NAME);
		$this->baseCountry = $options->get('general.country');
		$this->cartService = $cartService;
	}

	/**
	 * @return string Human readable name of method.
	 */
	public function getName()
	{
		return __('Local pickup', 'jigoshop');
	}

	/**
	 * @return bool Whether current method is enabled and able to work.
	 */
	public function isEnabled()
	{
		$cart = $this->cartService->getCurrent();
		$post = $this->wp->getGlobalPost();

		if ($post === null || $post->post_type != Types::ORDER) {
			$customer = $cart->getCustomer();
		} else {
			// TODO: Get rid of this hack for customer fetching
			$customer = unserialize($this->wp->getPostMeta($post->ID, 'customer', true));
		}

		return $this->options['enabled'] && $customer->getShippingAddress()->getCountry() == $this->baseCountry;
	}

	/**
	 * @return bool Whether current method is taxable.
	 */
	public function isTaxable()
	{
		return false;
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
				'checked' => $this->options['enabled'],
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

	/**
	 * @return string ID of shipping method.
	 */
	public function getId()
	{
		return self::NAME;
	}

	/**
	 * Restores shipping method state.
	 *
	 * @param array $state State to restore.
	 */
	public function restoreState(array $state)
	{
		// Empty
	}

	/**
	 * Checks whether current method is the one specified with selected rule.
	 *
	 * @param Method $method Method to check.
	 * @param int $rate Rate to check.
	 * @return boolean Is this the method?
	 */
	public function is(Method $method, $rate = null)
	{
		return $method->getId() == $this->getId();
	}
}
