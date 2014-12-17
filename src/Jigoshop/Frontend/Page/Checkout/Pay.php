<?php

namespace Jigoshop\Frontend\Page\Checkout;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Frontend\Page\PageInterface;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Tax;
use Jigoshop\Integration;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
use WPAL\Wordpress;

class Pay implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages  */
	private $messages;
	/** @var OrderServiceInterface */
	private $orderService;
	/** @var PaymentServiceInterface */
	private $paymentService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, OrderServiceInterface $orderService, PaymentServiceInterface $paymentService,
		Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->orderService = $orderService;
		$this->paymentService = $paymentService;

		$styles->add('jigoshop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.checkout.pay', JIGOSHOP_URL.'/assets/css/shop/checkout/pay.css');
		$scripts->add('jigoshop.checkout.pay', JIGOSHOP_URL.'/assets/js/shop/checkout/pay.js', array('jquery'));
		$wp->doAction('jigoshop\checkout\pay\assets', $wp, $styles, $scripts);
	}

	public function action()
	{
		$order = $this->orderService->find((int)$this->wp->getQueryParameter('pay'));

		if ($order->getKey() !==$_GET['key']) {
			$this->messages->addError(__('Invalid security key. Unable to process order.', 'jigoshop'));
			$this->wp->redirectTo($this->options->getPageId(Pages::ACCOUNT));
		}

		if (isset($_POST['action']) && $_POST['action'] == 'purchase') {
			try {
				if ($this->options->get('advanced.pages.terms') > 0 && (!isset($_POST['terms']) || $_POST['terms'] != 'on')) {
					throw new Exception(__('You need to accept terms &amp; conditions!', 'jigoshop'));
				}

				if (!isset($_POST['payment_method'])) {
					throw new Exception(__('Please select one of available payment methods.', 'jigoshop'));
				}

				$payment = $this->paymentService->get($_POST['payment_method']);
				$order->setPaymentMethod($payment);

				if (!$payment->isEnabled()) {
					throw new Exception(__('Selected payment method is not available. Please select another one.', 'jigoshop'));
				}

				$this->orderService->save($order);
				$url = $payment->process($order);

				// Redirect to thank you page
				if (empty($url)) {
					$url = $this->wp->getPermalink($this->wp->applyFilters('jigoshop\checkout\redirect_page_id', $this->options->getPageId(Pages::THANK_YOU)));
					$url = $this->wp->getHelpers()->addQueryArg(array('order' => $order->getId(), 'key' => $order->getKey()), $url);
				}

				$this->wp->wpRedirect($url);
				exit;
			} catch(Exception $e) {
				$this->messages->addError($e->getMessage());
			}
		}
	}

	public function render()
	{
		$order = $this->orderService->find((int)$this->wp->getQueryParameter('pay'));

		$termsUrl = '';
		$termsPage = $this->options->get('advanced.pages.terms');
		if ($termsPage > 0) {
			$termsUrl = $this->wp->getPageLink($termsPage);
		}

		$accountUrl = $this->wp->getPermalink($this->options->getPageId(Pages::ACCOUNT));

		return Render::get('shop/checkout/pay', array(
			'messages' => $this->messages,
			'order' => $order,
			'showWithTax' => $this->options->get('tax.price_tax') == 'with_tax',
			'termsUrl' => $termsUrl,
			'myAccountUrl' => $accountUrl,
			'myOrdersUrl' => Api::getEndpointUrl('orders', '', $accountUrl),
			'paymentMethods' => $this->paymentService->getEnabled(),
			'getTaxLabel' => function($taxClass) use ($order) {
				return Tax::getLabel($taxClass, $order->getCustomer());
			},
		));
	}
}
