<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Frontend\Page;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Product implements Page
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var Messages  */
	private $messages;

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, Messages $messages, Styles $styles)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->messages = $messages;
		$styles->add('jigoshop.shop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.shop.product', JIGOSHOP_URL.'/assets/css/shop/product.css');
	}


	public function action()
	{
	}

	public function render()
	{
		$post = $this->wp->getGlobalPost();
		$product = $this->productService->findForPost($post);
		return Render::get('shop/product', array(
			'product' => $product,
			'messages' => $this->messages,
		));
	}
}
