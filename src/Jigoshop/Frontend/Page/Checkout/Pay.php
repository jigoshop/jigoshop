<?php

namespace Jigoshop\Frontend\Page\Checkout;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Frontend\Page\PageInterface;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
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
	/** @var TaxServiceInterface */
	private $taxService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, OrderServiceInterface $orderService, TaxServiceInterface $taxService,
		Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->orderService = $orderService;
		$this->taxService = $taxService;

		$styles->add('jigoshop.checkout.pay', JIGOSHOP_URL.'/assets/css/checkout/pay.css');
		$wp->doAction('jigoshop\checkout\pay\assets', $wp, $styles, $scripts);
	}

	public function action()
	{
	}

	public function render()
	{
		$taxService = $this->taxService;
		$order = $this->orderService->find((int)$_GET['pay']);
		if ($order->getKey() != $_GET['key']) {
			$this->messages->addError(__('Invalid security key. Unable to process order.', 'jigoshop'));
			$this->wp->redirectTo($this->options->getPageId(Pages::ACCOUNT));
		}

		return Render::get('shop/checkout/pay', array(
			'messages' => $this->messages,
			'order' => $order,
			'showWithTax' => $this->options->get('tax.price_tax') == 'with_tax',
			'shopUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::SHOP)),
			'getTaxLabel' => function($taxClass) use ($taxService, $order) {
				return $taxService->getLabel($taxClass, $order->getCustomer());
			},
		));
	}
}
