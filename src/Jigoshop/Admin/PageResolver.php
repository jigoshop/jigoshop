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
			$this->wp->addAction('current_screen', function () use ($container, $that){
				$page = $that->getPage($container);
				$container->set('jigoshop.page.current', $page);
			});
		}
	}

	public function getPage(Container $container)
	{
		$this->wp->doAction('jigoshop\admin\page_resolver\before');

		if ($this->isProductsList()) {
			return $container->get('jigoshop.admin.page.products');
		}

		if ($this->isProduct()) {
			return $container->get('jigoshop.admin.page.product');
		}

		if ($this->isOrdersList()) {
			return $container->get('jigoshop.admin.page.orders');
		}

		if ($this->isOrder()) {
			return $container->get('jigoshop.admin.page.order');
		}

		if ($this->isEmail()) {
			return $container->get('jigoshop.admin.page.email');
		}

		if ($this->isCouponList()) {
			return $container->get('jigoshop.admin.page.coupons');
		}

		if ($this->isCoupon()) {
			return $container->get('jigoshop.admin.page.coupon');
		}

		return null;
	}

	private function isProductsList()
	{
		$screen = $this->wp->getCurrentScreen();

		if ($screen !== null) {
			return $screen->post_type === Types::PRODUCT && $screen->id === 'edit-'.Types::PRODUCT;
		}

		return DOING_AJAX && isset($_POST['action']) && strpos($_POST['action'], 'admin.products') !== false;
	}

	private function isProduct()
	{
		$screen = $this->wp->getCurrentScreen();

		if ($screen !== null) {
			return $screen->post_type === Types::PRODUCT && $screen->id === Types::PRODUCT;
		}

		return DOING_AJAX && isset($_POST['action']) && strpos($_POST['action'], 'admin.product') !== false;
	}

	private function isOrdersList()
	{
		$screen = $this->wp->getCurrentScreen();

		if ($screen !== null) {
			return $screen->post_type === Types::ORDER && $screen->id === 'edit-'.Types::ORDER;
		}

		return DOING_AJAX && isset($_POST['action']) && strpos($_POST['action'], 'admin.orders') !== false;
	}

	private function isOrder()
	{
		$screen = $this->wp->getCurrentScreen();

		if ($screen !== null) {
			return $screen->post_type === Types::ORDER && $screen->id === Types::ORDER;
		}

		return DOING_AJAX && isset($_POST['action']) && strpos($_POST['action'], 'admin.order') !== false;
	}

	private function isEmail()
	{
		$screen = $this->wp->getCurrentScreen();

		if ($screen !== null) {
			return $screen->post_type === Types::EMAIL && $screen->id === Types::EMAIL;
		}

		return DOING_AJAX && isset($_POST['action']) && strpos($_POST['action'], 'admin.email') !== false;
	}

	private function isCouponList()
	{
		$screen = $this->wp->getCurrentScreen();

		if ($screen !== null) {
			return $screen->post_type === Types::COUPON && $screen->id === 'edit-'.Types::COUPON;
		}

		return DOING_AJAX && isset($_POST['action']) && strpos($_POST['action'], 'admin.coupons') !== false;
	}

	private function isCoupon()
	{
		$screen = $this->wp->getCurrentScreen();

		if ($screen !== null) {
			return $screen->post_type === Types::COUPON && $screen->id === Types::COUPON;
		}

		return DOING_AJAX && isset($_POST['action']) && strpos($_POST['action'], 'admin.coupon') !== false;
	}
}
