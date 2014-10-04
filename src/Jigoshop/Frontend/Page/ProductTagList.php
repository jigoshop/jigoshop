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
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class ProductTagList extends AbstractProductList
{
	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, CartServiceInterface $cartService, Messages $messages, Styles $styles,
		Scripts $scripts)
	{
		parent::__construct($wp, $options, $productService, $cartService, $messages, $styles, $scripts);
	}

	public function getTitle()
	{
		$term = $this->wp->getTermBy('slug', $this->wp->getQueryParameter(Types\ProductTag::NAME), Types\ProductTag::NAME);

		if ($term) {
			return sprintf(__('Products tagged with "%s"', 'jigoshop'), $term->name);
		}

		return $this->wp->getQueryParameter(Types\ProductTag::NAME);
	}

	public function getContent()
	{
		return '';
	}
}
