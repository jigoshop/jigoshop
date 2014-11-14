<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Entity\Customer\Address;
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Exception;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
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
	/** @var OrderServiceInterface */
	private $orderService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, CartServiceInterface $cartService,	CustomerServiceInterface $customerService,
		ShippingServiceInterface $shippingService, PaymentServiceInterface $paymentService, TaxServiceInterface $taxService, OrderServiceInterface $orderService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->cartService = $cartService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;
		$this->paymentService = $paymentService;
		$this->taxService = $taxService;
		$this->orderService = $orderService;

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

		if (isset($_POST['action']) && $_POST['action'] == 'purchase') {
			try {
				$order = $this->orderService->createFromCart($cart);

				$shipping = $order->getShippingMethod();
				if ($this->isShippingRequired($order) && (!$shipping || !$shipping->isEnabled())) {
					throw new Exception(__('Shipping is required for this order. Please select shipping method.', 'jigoshop'));
				}

				$payment = $order->getPaymentMethod();
				$isPaymentRequired = $this->isPaymentRequired($order);
				if ($isPaymentRequired && (!$payment || !$payment->isEnabled())) {
					throw new Exception(__('Payment is required for this order. Please select payment method.', 'jigoshop'));
				}

				var_dump('ok'); exit;

				$this->orderService->save($order);

				if ($isPaymentRequired && !$payment->process($order)) {
					throw new Exception(__('Payment failed. Please try again.', 'jigoshop'));
				}

				// Redirect to thank you page
				$url = $this->wp->getPermalink($this->options->getPageId(Pages::THANK_YOU));
				$url = $this->wp->addQueryArgs(array('order' => $order->getId()), $url);
				$this->wp->wpRedirect($url);
				exit;
			} catch(Exception $e) {
				$this->messages->addError($e->getMessage());
			}
		}
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
			'showWithTax' => $this->options->get('general.price_tax') == 'with_tax',
			'differentShipping' => isset($_POST['jigoshop_order']) ? $_POST['jigoshop_order']['different_shipping'] == 'on' : false, // TODO: Fetch whether user want different shipping by default
		));
	}

	private function getBillingFields(Address $address)
	{
		return array(
			array(
				'type' => 'text',
				'label' => __('First name', 'jigoshop'),
				'name' => 'jigoshop_order[billing][first_name]',
				'value' => $address->getFirstName(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Last name', 'jigoshop'),
				'name' => 'jigoshop_order[billing][last_name]',
				'value' => $address->getLastName(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Company', 'jigoshop'),
				'name' => 'jigoshop_order[billing][company]',
				'value' => $address instanceof CompanyAddress ? $address->getCompany() : '',
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('EU VAT number', 'jigoshop'),
				'name' => 'jigoshop_order[billing][eu_vat]',
				'value' => $address instanceof CompanyAddress ? $address->getEuVat() : '',
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Address', 'jigoshop'),
				'name' => 'jigoshop_order[billing][address]',
				'value' => $address->getAddress(),
				'size' => 10,
				'columnSize' => 12,
			),
			array(
				'type' => 'select',
				'label' => __('Country', 'jigoshop'),
				'name' => 'jigoshop_order[billing][country]',
				'options' => Country::getAll(),
				'value' => $address->getCountry(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => Country::hasStates($address->getCountry()) ? 'select' : 'text',
				'label' => __('State/Province', 'jigoshop'),
				'name' => 'jigoshop_order[billing][state]',
				'options' => Country::getStates($address->getCountry()),
				'value' => $address->getState(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('City', 'jigoshop'),
				'name' => 'jigoshop_order[billing][city]',
				'value' => $address->getCity(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Postcode', 'jigoshop'),
				'name' => 'jigoshop_order[billing][postcode]',
				'value' => $address->getPostcode(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Phone', 'jigoshop'),
				'name' => 'jigoshop_order[billing][phone]',
				'value' => $address->getPhone(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Email', 'jigoshop'),
				'name' => 'jigoshop_order[billing][email]',
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
				'name' => 'jigoshop_order[shipping][first_name]',
				'value' => $address->getFirstName(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Last name', 'jigoshop'),
				'name' => 'jigoshop_order[shipping][last_name]',
				'value' => $address->getLastName(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Company', 'jigoshop'),
				'name' => 'jigoshop_order[shipping][company]',
				'value' => $address instanceof CompanyAddress ? $address->getCompany() : '',
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Address', 'jigoshop'),
				'name' => 'jigoshop_order[shipping][address]',
				'value' => $address->getAddress(),
				'size' => 10,
				'columnSize' => 12,
			),
			array(
				'type' => 'select',
				'label' => __('Country', 'jigoshop'),
				'name' => 'jigoshop_order[shipping][country]',
				'options' => Country::getAll(),
				'value' => $address->getCountry(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => Country::hasStates($address->getCountry()) ? 'select' : 'text',
				'label' => __('State/Province', 'jigoshop'),
				'name' => 'jigoshop_order[shipping][state]',
				'options' => Country::getStates($address->getCountry()),
				'value' => $address->getState(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('City', 'jigoshop'),
				'name' => 'jigoshop_order[shipping][city]',
				'value' => $address->getCity(),
				'size' => 9,
				'columnSize' => 6,
			),
			array(
				'type' => 'text',
				'label' => __('Postcode', 'jigoshop'),
				'name' => 'jigoshop_order[shipping][postcode]',
				'value' => $address->getPostcode(),
				'size' => 9,
				'columnSize' => 6,
			),
		);
	}

	/**
	 * @param $order OrderInterface The order.
	 * @return bool
	 */
	private function isPaymentRequired($order)
	{
		return $order->getTotal() > 0;
	}

	/**
	 * @param $order OrderInterface The order.
	 * @return bool
	 */
	private function isShippingRequired($order)
	{
		foreach ($order->getItems() as $item) {
			/** @var $item Item */
			if ($item->isShippable()) {
				return true;
			}
		}

		return false;
	}
}
