<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Entity\Customer\Address;
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
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
	/** @var PaymentServiceInterface */
	private $paymentService;
	/** @var TaxServiceInterface */
	private $taxService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, CartServiceInterface $cartService,	CustomerServiceInterface $customerService,
		ShippingServiceInterface $shippingService, PaymentServiceInterface $paymentService, TaxServiceInterface $taxService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->cartService = $cartService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;
		$this->paymentService = $paymentService;
		$this->taxService = $taxService;

		$styles->add('jigoshop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.checkout', JIGOSHOP_URL.'/assets/css/shop/checkout.css');
		$styles->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
		$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js', array('jquery'));
		$scripts->add('jigoshop.checkout', JIGOSHOP_URL.'/assets/js/shop/checkout.js', array('jquery', 'jigoshop.vendors'));
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

		// TODO: Check if everything is set
		// TODO: Implement placing an order
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

		$billingFields = $this->wp->applyFilters('jigoshop\checkout\billing_fields', $this->getBillingFields($cart->getCustomer()->getBillingAddress()));
		$shippingFields = $this->wp->applyFilters('jigoshop\checkout\shipping_fields', $this->getShippingFields($cart->getCustomer()->getShippingAddress()));

		return Render::get('shop/checkout', array(
			'cartUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::CART)),
			'content' => $content,
			'cart' => $cart,
			'messages' => $this->messages,
			'shippingMethods' => $this->shippingService->getEnabled(),
			'paymentMethods' => $this->paymentService->getEnabled(),
			'billingFields' => $billingFields,
			'shippingFields' => $shippingFields,
		));
	}

	private function getBillingFields(Address $address)
	{
		return array(
			array(
				'type' => 'text',
				'label' => __('First name', 'jigoshop'),
				'name' => 'order[billing][first_name]',
				'value' => $address->getFirstName(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Last name', 'jigoshop'),
				'name' => 'order[billing][last_name]',
				'value' => $address->getLastName(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Company', 'jigoshop'),
				'name' => 'order[billing][company]',
				'value' => $address instanceof CompanyAddress ? $address->getCompany() : '',
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('EU VAT number', 'jigoshop'),
				'name' => 'order[billing][eu_vat]',
				'value' => $address instanceof CompanyAddress ? $address->getEuVat() : '',
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Address', 'jigoshop'),
				'name' => 'order[billing][address]',
				'value' => $address->getAddress(),
				'size' => 10,
				'columnSize' => 12,
			),
			array(
				'type' => 'select',
				'label' => __('Country', 'jigoshop'),
				'name' => 'order[billing][country]',
				'options' => Country::getAll(),
				'value' => $address->getCountry(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => Country::hasStates($address->getCountry()) ? 'select' : 'text',
				'label' => __('State/Province', 'jigoshop'),
				'name' => 'order[billing][state]',
				'options' => Country::getStates($address->getCountry()),
				'value' => $address->getState(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('City', 'jigoshop'),
				'name' => 'order[billing][city]',
				'value' => $address->getCity(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Postcode', 'jigoshop'),
				'name' => 'order[billing][postcode]',
				'value' => $address->getPostcode(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Phone', 'jigoshop'),
				'name' => 'order[billing][phone]',
				'value' => $address->getPhone(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Email', 'jigoshop'),
				'name' => 'order[billing][email]',
				'value' => $address->getEmail(),
				'size' => 9,
				'columnSize' => 6,
			),
		);
	}

	private function getShippingFields(Address $address)
	{
		return array(
			array(
				'type' => 'text',
				'label' => __('First name', 'jigoshop'),
				'name' => 'order[shipping][first_name]',
				'value' => $address->getFirstName(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Last name', 'jigoshop'),
				'name' => 'order[shipping][last_name]',
				'value' => $address->getLastName(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Company', 'jigoshop'),
				'name' => 'order[shipping][company]',
				'value' => $address instanceof CompanyAddress ? $address->getCompany() : '',
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Address', 'jigoshop'),
				'name' => 'order[shipping][address]',
				'value' => $address->getAddress(),
				'size' => 10,
				'columnSize' => 12,
			),
			array(
				'type' => 'select',
				'label' => __('Country', 'jigoshop'),
				'name' => 'order[shipping][country]',
				'options' => Country::getAll(),
				'value' => $address->getCountry(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => Country::hasStates($address->getCountry()) ? 'select' : 'text',
				'label' => __('State/Province', 'jigoshop'),
				'name' => 'order[shipping][state]',
				'options' => Country::getStates($address->getCountry()),
				'value' => $address->getState(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('City', 'jigoshop'),
				'name' => 'order[shipping][city]',
				'value' => $address->getCity(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Postcode', 'jigoshop'),
				'name' => 'order[shipping][postcode]',
				'value' => $address->getPostcode(),
				'size' => 9,
				'columnSize' => 6,
			),
		);
	}
}
