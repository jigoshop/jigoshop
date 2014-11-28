<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Item;
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

		$styles->add('jigoshop.account', JIGOSHOP_URL.'/assets/css/account.css');
//		$styles->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
//		$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
//		$scripts->add('jigoshop.account', JIGOSHOP_URL.'/assets/js/account.js', array('jquery', 'jigoshop.vendors'));
		$this->wp->doAction('jigoshop\account\assets', $wp, $styles, $scripts);
	}


	public function action()
	{
		// TODO: Check if user is logged in and redirect to login page if not
		// TODO: Probably addresses should be changed here
	}

	public function render()
	{
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::ACCOUNT));
		$customer = $this->customerService->getCurrent();
		return Render::get('account', array(
			'content' => $content,
			'messages' => $this->messages,
			'customer' => $customer,
		));
	}
}
