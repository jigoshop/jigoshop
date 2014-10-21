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
		$screen = $this->wp->getCurrentScreen();
//		echo '<pre>'; var_dump($screen); exit;

		if ($screen->post_type === Types::PRODUCT && $screen->id === 'edit-'.Types::PRODUCT) {
			return $container->get('jigoshop.admin.page.products');
		}

		if ($screen->post_type === Types::ORDER && $screen->id === 'edit-'.Types::ORDER) {
			return $container->get('jigoshop.admin.page.orders');
		}

		if ($screen->post_type === Types::ORDER && $screen->id === Types::ORDER) {
			return $container->get('jigoshop.admin.page.order');
		}

		return null;
	}
}
