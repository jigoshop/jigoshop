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

		$this->endpoints = array(
			'edit-address',
			'change-password',
			'orders',
			'pay',
			'download-file',
		);
	}

	public function run()
	{
		$this->addEndpoints();
		$this->wp->addFilter('request', array($this, 'intercept'));
		$this->wp->addFilter('wp_nav_menu_objects', array($this, 'menu'));
	}

	/**
	 * Updates menu items to enable "Shop" item when necessary.
	 *
	 * @param $items array Menu items.
	 * @return array Updated menu items.
	 */
	public function menu($items)
	{
		if ($this->wp->getQueryParameter('post_type', false) == Types::PRODUCT) {
			foreach ($items as $item) {
				/** @var $item \WP_Post */
				/** @noinspection PhpUndefinedFieldInspection */
				if ($item->object_id == $this->options->getPageId(Pages::SHOP)) {
					/** @noinspection PhpUndefinedFieldInspection */
					$item->classes[] = 'current-menu-item';
				}
			}
		}

		return $items;
	}

	/**
	 * Adds endpoints.
	 */
	public function addEndpoints()
	{
		foreach ($this->endpoints as $endpoint) {
			$this->wp->addRewriteEndpoint($endpoint, EP_ROOT | EP_PAGES | EP_PERMALINK);
		}
		$this->wp->flushRewriteRules();
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
		if ($this->isCart($request)) {
			return $request;
		}

		if ($this->isProductCategory($request)) {
			$query = $this->getProductListQuery($request);
			$query[Types\ProductCategory::NAME] = $request[Types\ProductCategory::NAME];
			return $query;
		}

		if ($this->isProductTag($request)) {
			$query = $this->getProductListQuery($request);
			$query[Types\ProductTag::NAME] = $request[Types\ProductTag::NAME];
			return $query;
		}

		if ($this->isProductList($request)) {
			return $this->getProductListQuery($request);
		}

		if ($this->isProduct($request)) {
			return array(
				'name' => $request['product'],
				'post_type' => Types::PRODUCT,
				'post_status' => 'publish',
				'posts_per_page' => 1,
			);
		}

		if ($this->isAccount($request)) {
			return $request;
		}

		return $request;
	}

	private function isAccount($request)
	{
		return isset($request['pagename']) && $request['pagename'] == Pages::SHOP;
	}

	private function isProductList($request)
	{
		return !isset($request['product']) && (
			(isset($request['pagename']) && $request['pagename'] == Pages::SHOP) ||
			(isset($request['post_type']) && $request['post_type'] == Types::PRODUCT)
		);
	}

	private function isProduct($request)
	{
		return isset($request['post_type']) && $request['post_type'] == Types::PRODUCT && isset($request['product']);
	}

	private function isCart($request)
	{
		return isset($request['pagename']) && $request['pagename'] == Pages::CART;
	}

	private function isProductCategory($request)
	{
		return isset($request[Types\ProductCategory::NAME]);
	}

	private function isProductTag($request)
	{
		return isset($request[Types\ProductTag::NAME]);
	}

	private function getProductListQuery($request)
	{
		$options = $this->options->get('shopping');
		return array(
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
}
