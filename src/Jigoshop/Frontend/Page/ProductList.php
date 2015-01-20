<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Frontend\Pages;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class ProductList extends AbstractProductList
{
	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, CartServiceInterface $cartService, Messages $messages)
	{
		parent::__construct($wp, $options, $productService, $cartService, $messages);
	}

	public function action()
	{
		if (isset($_GET['page_id']) && $_GET['page_id'] == $this->options->getPageId(Pages::SHOP)) {
			$this->wp->wpSafeRedirect($this->wp->getPostTypeArchiveLink(Types::PRODUCT));
			exit;
		}

		parent::action();
	}

	public function getTitle()
	{
		return __('All products', 'jigoshop');
	}

	public function getContent()
	{
		return $this->wp->getPostField('post_content', $this->options->getPageId(Pages::SHOP));
	}
}
