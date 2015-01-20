<?php

namespace Jigoshop\Admin;

use Jigoshop\Core\Types;
use Symfony\Component\DependencyInjection\Container;
use WPAL\Wordpress;

/**
 * Factory that decides what current page is and provides proper page object.
 *
 * @package Jigoshop\Admin
 */
class PageResolver
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Pages */
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
			$this->wp->addAction('current_screen', function () use ($container, $that){
				$page = $that->getPage($container);
				$container->set('jigoshop.page.current', $page);
			}, 1);
		}
	}

	public function getPage(Container $container)
	{
		$this->wp->doAction('jigoshop\admin\page_resolver\before');

		if ($this->pages->isProductCategories()) {
			return $container->get('jigoshop.admin.page.product_categories');
		}

		if ($this->pages->isProductsList()) {
			return $container->get('jigoshop.admin.page.products');
		}

		if ($this->pages->isProduct()) {
			return $container->get('jigoshop.admin.page.product');
		}

		if ($this->pages->isOrdersList()) {
			return $container->get('jigoshop.admin.page.orders');
		}

		if ($this->pages->isOrder()) {
			return $container->get('jigoshop.admin.page.order');
		}

		if ($this->pages->isEmail()) {
			return $container->get('jigoshop.admin.page.email');
		}

		if ($this->pages->isCouponList()) {
			return $container->get('jigoshop.admin.page.coupons');
		}

		if ($this->pages->isCoupon()) {
			return $container->get('jigoshop.admin.page.coupon');
		}

		return null;
	}
}
