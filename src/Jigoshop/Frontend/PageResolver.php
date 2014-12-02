<?php

namespace Jigoshop\Frontend;

use Jigoshop\Core\Pages;
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
	/** @var \Jigoshop\Core\Pages */
	private $pages;

	public function __construct(Wordpress $wp, Pages $pages)
	{
		$this->wp = $wp;
		$this->pages = $pages;
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
		if (!$this->pages->isJigoshop() && !$this->pages->isAjax()) {
			return null;
		}

		if ($this->pages->isCheckoutThankYou()) {
			return $container->get('jigoshop.page.checkout.thank_you');
		}

		if ($this->pages->isCheckout()) {
			return $container->get('jigoshop.page.checkout');
		}

		if ($this->pages->isCart()) {
			return $container->get('jigoshop.page.cart');
		}

		if ($this->pages->isProductCategory()) {
			return $container->get('jigoshop.page.product_category_list');
		}

		if ($this->pages->isProductTag()) {
			return $container->get('jigoshop.page.product_tag_list');
		}

		if ($this->pages->isProductList()) {
			return $container->get('jigoshop.page.product_list');
		}

		if ($this->pages->isProduct()) {
			return $container->get('jigoshop.page.product');
		}

		if ($this->pages->isAccountOrders()) {
			return $container->get('jigoshop.page.account.orders');
		}

		if ($this->pages->isAccountEditAddress()) {
			return $container->get('jigoshop.page.account.edit_address');
		}

		if ($this->pages->isAccountChangePassword()) {
			return $container->get('jigoshop.page.account.change_password');
		}

		if ($this->pages->isAccount()) {
			return $container->get('jigoshop.page.account');
		}
	}
}
