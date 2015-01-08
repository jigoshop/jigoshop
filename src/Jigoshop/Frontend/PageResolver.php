<?php

namespace Jigoshop\Frontend;

use Symfony\Component\DependencyInjection\Container;
use WPAL\Wordpress;

/**
 * Factory that decides what current page is and provides proper page object.
 *
 * @package Jigoshop\Frontend
 */
class PageResolver
{
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
	}

	public function resolve(Container $container)
	{
		if (defined('DOING_AJAX') && DOING_AJAX) {
			// Instantiate page to install Ajax actions
			$this->getPage($container);
		} else {
			$that = $this;
			$this->wp->addAction('template_redirect', function () use ($container, $that){
				$page = $that->getPage($container);
				$container->set('jigoshop.page.current', $page);
				$container->get('jigoshop.template')->setPage($page);
			});
		}
	}

	public function getPage(Container $container)
	{
		if (!Pages::isJigoshop() && !Pages::isAjax()) {
			return null;
		}

		$this->wp->doAction('jigoshop\page_resolver\before');

		if (Pages::isCheckoutThankYou()) {
			return $container->get('jigoshop.page.checkout.thank_you');
		}

		if (Pages::isCheckoutPay()) {
			return $container->get('jigoshop.page.checkout.pay');
		}

		if (Pages::isCheckout()) {
			return $container->get('jigoshop.page.checkout');
		}

		if (Pages::isCart()) {
			return $container->get('jigoshop.page.cart');
		}

		if (Pages::isProductCategory()) {
			return $container->get('jigoshop.page.product_category_list');
		}

		if (Pages::isProductTag()) {
			return $container->get('jigoshop.page.product_tag_list');
		}

		if (Pages::isProductList()) {
			return $container->get('jigoshop.page.product_list');
		}

		if (Pages::isProduct()) {
			return $container->get('jigoshop.page.product');
		}

		if (Pages::isAccountOrders()) {
			return $container->get('jigoshop.page.account.orders');
		}

		if (Pages::isAccountEditAddress()) {
			return $container->get('jigoshop.page.account.edit_address');
		}

		if (Pages::isAccountChangePassword()) {
			return $container->get('jigoshop.page.account.change_password');
		}

		if (Pages::isAccount()) {
			return $container->get('jigoshop.page.account');
		}
	}
}
