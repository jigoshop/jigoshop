<?php

namespace Jigoshop\Shipping;

use Jigoshop\Admin\Settings;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Scripts;
use Jigoshop\Service\CartServiceInterface;
use WPAL\Wordpress;

class FlatRate implements Method
{
	const NAME = 'flat_rate';

	/** @var Wordpress */
	private $wp;
	/** @var array */
	private $options;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var Messages */
	private $messages;
	/** @var array */
	private $types;
	/** @var array */
	private $availability;

	public function __construct(Wordpress $wp, Options $options, CartServiceInterface $cartService, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options->get('shipping.'.self::NAME);
		$this->cartService = $cartService;
		$this->messages = $messages;
		$this->types = array(
			'per_order' => __('Per order', 'jigoshop'),
			'per_item' => __('Per item', 'jigoshop'),
		);
		$this->availability = array(
			'all' => __('All allowed countries', 'jigoshop'),
			'specific' => __('Selected countries', 'jigoshop'),
		);

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php', 'options.php'))) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, array('jigoshop_page_'.Settings::NAME, 'options'))) {
				return;
			}

			if (!isset($_GET['tab']) || $_GET['tab'] !== 'shipping') {
				return;
			}

			Scripts::add('jigoshop.admin.shipping.flat_rate', JIGOSHOP_URL.'/assets/js/admin/shipping/flat_rate.js', array(
				'jquery',
				'jigoshop.admin'
			));
		});
	}

	/**
	 * @return string Human readable name of method.
	 */
	public function getName()
	{
		return __('Flat rate', 'jigoshop');
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

		return $this->options['enabled'] && ($this->options['available_for'] === 'all' || in_array($customer->getShippingAddress()->getCountry(), $this->options['countries']));
	}

	/**
	 * @return bool Whether current method is taxable.
	 */
	public function isTaxable()
	{
		return $this->options['is_taxable'];
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
			array(
				'name' => sprintf('[%s][title]', self::NAME),
				'title' => __('Method title', 'jigoshop'),
				'type' => 'text',
				'value' => $this->options['title'],
			),
			array(
				'name' => sprintf('[%s][type]', self::NAME),
				'title' => __('Type', 'jigoshop'),
				'type' => 'select',
				'value' => $this->options['type'],
				'options' => $this->types,
			),
			array(
				'name' => sprintf('[%s][is_taxable]', self::NAME),
				'title' => __('Is taxable?', 'jigoshop'),
				'type' => 'checkbox',
				'checked' => $this->options['is_taxable'],
			),
			array(
				'name' => sprintf('[%s][cost]', self::NAME),
				'title' => __('Cost', 'jigoshop'),
				'type' => 'text',
				'value' => $this->options['cost'],
			),
			array(
				'name' => sprintf('[%s][fee]', self::NAME),
				'title' => __('Handling fee', 'jigoshop'),
				'type' => 'text',
				'value' => $this->options['fee'],
			),
			array(
				'name' => sprintf('[%s][available_for]', self::NAME),
				'id' => 'flat_rate_available_for',
				'title' => __('Available for', 'jigoshop'),
				'type' => 'select',
				'value' => $this->options['available_for'],
				'options' => $this->availability,
			),
			array(
				'name' => sprintf('[%s][countries]', self::NAME),
				'id' => 'flat_rate_countries',
				'title' => __('Select countries', 'jigoshop'),
				'type' => 'select',
				'value' => $this->options['countries'],
				'options' => Country::getAllowed(),
				'multiple' => true,
				'hidden' => $this->options['available_for'] == 'all',
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
		$settings['is_taxable'] = $settings['is_taxable'] == 'on';

		if (!in_array($settings['type'], array_keys($this->types))) {
			$settings['type'] = $this->options['type'];
			$this->messages->addWarning(__('Type is invalid - value is left unchanged.', 'jigoshop'));
		}

		if (!is_numeric($settings['cost'])) {
			$settings['cost'] = $this->options['cost'];
			$this->messages->addWarning(__('Cost was invalid - value is left unchanged.', 'jigoshop'));
		}
		if ($settings['cost'] >= 0) {
			$settings['cost'] = (float)$settings['cost'];
		} else {
			$settings['cost'] = $this->options['cost'];
			$this->messages->addWarning(__('Cost was below 0 - value is left unchanged.', 'jigoshop'));
		}

		if (!is_numeric($settings['fee'])) {
			$settings['fee'] = $this->options['fee'];
			$this->messages->addWarning(__('Fee was invalid - value is left unchanged.', 'jigoshop'));
		}
		if ($settings['fee'] >= 0) {
			$settings['fee'] = (float)$settings['fee'];
		} else {
			$settings['fee'] = $this->options['fee'];
			$this->messages->addWarning(__('Fee was below 0 - value is left unchanged.', 'jigoshop'));
		}

		if (!in_array($settings['available_for'], array_keys($this->availability))) {
			$settings['available_for'] = $this->options['available_for'];
			$this->messages->addWarning(__('Availability is invalid - value is left unchanged.', 'jigoshop'));
		}

		if ($settings['available_for'] === 'specific') {
			$settings['countries'] = array_filter($settings['countries'], function($item){
				return Country::exists($item);
			});
		} else {
			$settings['countries'] = array();
		}

		return $settings;
	}

	/**
	 * @param OrderInterface $order Order to calculate shipping for.
	 * @return float Calculated value of shipping for the order.
	 */
	public function calculate(OrderInterface $order)
	{
		return (float)($this->options['cost'] + $this->options['fee']);
	}

	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses()
	{
		return array('standard');
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
