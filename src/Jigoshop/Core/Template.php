<?php


namespace Jigoshop\Core;

use Jigoshop\Helper\Render;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

/**
 * Class binding all basic templates.
 *
 * @package Jigoshop\Core
 */
class Template
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Core\Pages */
	private $pages;
	/** @var ProductServiceInterface */
	private $productService;

	public function __construct(Wordpress $wp, Options $options, Pages $pages, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->pages = $pages;
		$this->productService = $productService;
	}

	/**
	 * Loads proper template based on current page.
	 *
	 * @param $template string Template chain.
	 * @return string Template to load.
	 */
	public function process($template)
	{
		if (!$this->pages->isJigoshop()) {
			return $template;
		}

		if ($this->pages->isProductList()) {
			$this->productList();
		}

		return false;
	}

	/**
	 * Renders product list page.
	 */
	protected function productList()
	{
		$page = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::SHOP));
		$query = $this->wp->getWpQuery();
		$products = $this->productService->findByQuery($query);
		Render::output('shop', array(
			'page' => $page,
			'products' => $products,
		));
	}
}