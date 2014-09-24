<?php

namespace Jigoshop\Query;

use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use WPAL\Wordpress;

class Interceptor
{
	private $intercepted = false;
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
		$wp->addFilter('request', array($this, 'intercept'));
	}

	public function intercept($request)
	{
		if ($this->intercepted || $this->wp->isAdmin()) {
			return $request;
		}

		$this->intercepted = true;
		return $this->parseRequest($request);
	}

	private function parseRequest($request)
	{
		// TODO: Refactor preparing requests
		if ($this->isProductList($request)) {
			$options = $this->options->get('shopping');
			$request = array(
				'post_type' => Types::PRODUCT,
				'post_status' => 'publish',
				'ignore_sticky_posts' => true,
				'posts_per_page' => $options['catalog_per_page'],
				'paged' => isset($request['paged']) ? $request['paged'] : 1,
				'orderby' => $options['catalog_order_by'],
				'order' => $options['catalog_order'],
				'meta_query' => array(
					array(
						'key' => 'visibility',
						'value' => array(Product::VISIBILITY_CATALOG, Product::VISIBILITY_PUBLIC),
						'compare' => 'IN'
					),
				),
			);
		}

		return $request;
	}

	private function isProductList($request)
	{
		return !isset($request['product']) && (
			(isset($request['pagename']) && $request['pagename'] == Pages::SHOP) ||
			(isset($request['post_type']) && $request['post_type'] == Types::PRODUCT)
		);
	}
}
