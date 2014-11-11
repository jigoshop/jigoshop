<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
use WPAL\Wordpress;

class Checkout implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages  */
	private $messages;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var ShippingServiceInterface */
	private $shippingService;
	/** @var TaxServiceInterface */
	private $taxService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, CartServiceInterface $cartService,	CustomerServiceInterface $customerService,
		ShippingServiceInterface $shippingService, TaxServiceInterface $taxService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->cartService = $cartService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;
		$this->taxService = $taxService;

		$styles->add('jigoshop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.checkout', JIGOSHOP_URL.'/assets/css/shop/checkout.css');
		$scripts->add('jigoshop.checkout', JIGOSHOP_URL.'/assets/js/shop/checkout.js', array('jquery'));
		$scripts->localize('jigoshop.checkout', 'jigoshop_checkout', array(
			'ajax' => $this->wp->getAjaxUrl(),
		));
	}
	/**
	 * Executes actions associated with selected page.
	 */
	public function action()
	{
		$cart = $this->cartService->getCurrent();

		if ($cart->isEmpty()) {
			$this->messages->addWarning(__('Your cart is empty, please add products before proceeding.', 'jigoshop'));
			$this->wp->redirectTo($this->options->getPageId(Pages::SHOP));
		}

		// TODO: Implement action() method.
	}

	/**
	 * Renders page template.
	 *
	 * @return string Page template.
	 */
	public function render()
	{
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::CHECKOUT));
		$cart = $this->cartService->getCurrent();

		return Render::get('shop/checkout', array(
			'cartUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::CART)),
			'content' => $content,
			'cart' => $cart,
			'messages' => $this->messages,
			'shippingMethods' => $this->shippingService->getEnabled(),
			'paymentMethods' => array(),
			'billingFields' => array(
				array(
					'type' => 'text',
					'label' => __('First name', 'jigoshop'),
					'name' => 'order[billing][first_name]',
					'value' => '', // TODO: Properly fetch customer data
					'size' => 9,
					'columnSize' => 6,
				),
				array(
					'type' => 'text',
					'label' => __('Last name', 'jigoshop'),
					'name' => 'order[billing][last_name]',
					'value' => '', // TODO: Properly fetch customer data
					'size' => 9,
					'columnSize' => 6,
				),
			),
			'shippingFields' => array(
				array(
					'type' => 'text',
					'label' => __('First name', 'jigoshop'),
					'name' => 'order[shipping][first_name]',
					'value' => '', // TODO: Properly fetch customer data
					'size' => 9,
					'columnSize' => 6,
				),
				array(
					'type' => 'text',
					'label' => __('Last name', 'jigoshop'),
					'name' => 'order[shipping][last_name]',
					'value' => '', // TODO: Properly fetch customer data
					'size' => 9,
					'columnSize' => 6,
				),
			),
		));
	}
}
