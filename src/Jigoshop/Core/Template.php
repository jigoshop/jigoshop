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
	 * Redirect Jigoshop pages to proper types.
	 */
	public function redirect()
	{
		if (isset($_GET['page_id']) && $_GET['page_id'] == $this->options->getPageId(Pages::SHOP)) {
			$this->wp->wpSafeRedirect($this->wp->getPostTypeArchiveLink(Types::PRODUCT));
		}
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

		$content = '';
		if ($this->pages->isProductList()) {
			$content = $this->productList();
		}

		$template = $this->wp->getOption('template');
		$theme = $this->wp->wpGetTheme();
		if ($theme->get('Author') === 'WooThemes') {
			$template = 'woothemes';
		}

		Render::output('layout/'.$template, array(
			'content' => $content,
		));

		return false;
	}

	/**
	 * Renders product list page.
	 */
	protected function productList()
	{
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::SHOP));
//		$page = $this->wp->getQueryParameter('paged');
		$query = $this->wp->getWpQuery();
		$products = $this->productService->findByQuery($query);
		return Render::get('shop', array(
			'content' => $content,
			'products' => $products,
		));
	}
}