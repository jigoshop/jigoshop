<?php

namespace Jigoshop\Frontend\Page\Account;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Frontend\Page\PageInterface;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CustomerServiceInterface;
use WPAL\Wordpress;

class EditAddress implements PageInterface
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
	/** @var TaxServiceInterface */
	private $taxService;

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService, OrderServiceInterface $orderService,
		TaxServiceInterface $taxService, Messages $messages, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->orderService = $orderService;
		$this->taxService = $taxService;
		$this->messages = $messages;

		$styles->add('jigoshop.user.account', JIGOSHOP_URL.'/assets/css/user/account.css');
		$styles->add('jigoshop.user.account.orders.single', JIGOSHOP_URL.'/assets/css/user/account/orders/single.css');
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

		$customer = $this->customerService->getCurrent();
		switch ($this->wp->getQueryParameter('edit-address')) {
			case 'billing':
				$address = $customer->getBillingAddress();
				break;
			case 'shipping':
				$address = $customer->getShippingAddress();
				break;
		}
		return Render::get('user/account/edit_address', array(
			'messages' => $this->messages,
			'customer' => $customer,
			'address' => $address,
			'myAccountUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::ACCOUNT)),
		));
	}
}
