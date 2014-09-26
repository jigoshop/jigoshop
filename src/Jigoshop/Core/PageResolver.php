<?php

namespace Jigoshop\Core;

use Symfony\Component\DependencyInjection\Container;
use WPAL\Wordpress;

/**
 * Factory that decides what current page is and provides proper page object.
 *
 * @package Jigoshop\Core
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
		$that = $this;
		$this->wp->addAction('template_redirect', function() use ($container, $that){
			$page = $that->getPage($container);
			$container->set('jigoshop.page.current', $page);
			$container->get('jigoshop.template')->setPage($page);
		});
	}

	public function getPage(Container $container)
	{
		if (!$this->pages->isJigoshop()) {
			return null;
		}

		if ($this->pages->isProductList()) {
			return $container->get('jigoshop.page.product_list');
		}

		if ($this->pages->isProduct()) {
			return $container->get('jigoshop.page.product');
		}
	}
}
