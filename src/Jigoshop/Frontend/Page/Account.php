<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class Account implements PageInterface
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

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService, OrderServiceInterface $orderService, Messages $messages,
		Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->orderService = $orderService;
		$this->messages = $messages;

		$styles->add('jigoshop.user.account', JIGOSHOP_URL.'/assets/css/user/account.css');
		$scripts->add('jigoshop.helpers', JIGOSHOP_URL.'/assets/js/helpers.js', array('jquery'));
		$scripts->add('jigoshop.shop', JIGOSHOP_URL.'/assets/js/shop.js', array('jquery', 'jigoshop.helpers'));
		$this->wp->doAction('jigoshop\account\assets', $wp, $styles, $scripts);
	}


	public function action()
	{
	}

	public function render()
	{
		if (!$this->wp->isUserLoggedIn()) {
			return Render::get('user/login', array());
		}

		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::ACCOUNT));
		$customer = $this->customerService->getCurrent();
		$query = new \WP_Query(array(
			'post_type' => Types::ORDER,
			'post_status' => array(Status::PENDING, Status::ON_HOLD),
			'posts_per_page' => $this->options->get('shopping.unpaid_orders_number'),
		));
		$orders = $this->orderService->findByQuery($query);

		return Render::get('user/account', array(
			'content' => $content,
			'messages' => $this->messages,
			'customer' => $customer,
			'unpaidOrders' => $orders,
			'editBillingAddressUrl' => Api::getEndpointUrl('edit-address', 'billing'),
			'editShippingAddressUrl' => Api::getEndpointUrl('edit-address', 'shipping'),
			'changePasswordUrl' => Api::getEndpointUrl('change-password'),
			'myOrdersUrl' => Api::getEndpointUrl('orders'),
		));
	}
}
