<?php

namespace Jigoshop\Frontend\Page\Account;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order as Entity;
use Jigoshop\Frontend\Page\PageInterface;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Tax;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class Orders implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages  */
	private $messages;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var OrderServiceInterface */
	private $orderService;

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService, OrderServiceInterface $orderService, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->orderService = $orderService;
		$this->messages = $messages;

		Styles::add('jigoshop.user.account', JIGOSHOP_URL.'/assets/css/user/account.css');
		Styles::add('jigoshop.user.account.orders', JIGOSHOP_URL.'/assets/css/user/account/orders.css');
		Styles::add('jigoshop.user.account.orders.single', JIGOSHOP_URL.'/assets/css/user/account/orders/single.css');
		$this->wp->doAction('jigoshop\account\orders\assets', $wp);
	}


	public function action()
	{
	}

	public function render()
	{
		if (!$this->wp->isUserLoggedIn()) {
			return Render::get('user/login', array());
		}

		$order = $this->wp->getQueryParameter('orders');
		$accountUrl = $this->wp->getPermalink($this->options->getPageId(Pages::ACCOUNT));

		if (!empty($order) && is_numeric($order)) {
			$order = $this->orderService->find($order);
			/** @var Entity $order */
			return Render::get('user/account/orders/single', array(
				'messages' => $this->messages,
				'order' => $order,
				'myAccountUrl' => $accountUrl,
				'listUrl' => Api::getEndpointUrl('orders', '', $accountUrl),
				'showWithTax' => $this->options->get('tax.price_tax') == 'with_tax',
				'getTaxLabel' => function($taxClass) use ($order) {
					return Tax::getLabel($taxClass, $order);
				},
			));
		}

		$customer = $this->customerService->getCurrent();
		$orders = $this->orderService->findForUser($customer->getId());
		return Render::get('user/account/orders', array(
			'messages' => $this->messages,
			'customer' => $customer,
			'orders' => $orders,
			'myAccountUrl' => $accountUrl,
		));
	}
}
