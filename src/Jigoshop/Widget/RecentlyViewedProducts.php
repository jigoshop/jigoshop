<?php

namespace Jigoshop\Widget;

use Jigoshop\Core;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;

class RecentlyViewedProducts extends \WP_Widget
{
	const ID = 'jigoshop_recently_viewed_products';
	const SESSION_KEY = 'jigoshop_recently_viewed_products';

	/** @var \Jigoshop\Service\ProductServiceInterface */
	private static $productService;

	public function __construct()
	{
		$options = array(
			'classname' => self::ID,
			'description' => __('A list of your customers most recently viewed products', 'jigoshop')
		);

		// Create the widget
		parent::__construct(self::ID, __('Jigoshop: Recently Viewed', 'jigoshop'), $options);

		// Attach the tracker to the product view action
		add_action('jigoshop\template\product\before', '\Jigoshop\Widget\RecentlyViewedProducts::productViewTracker', 10, 1);
	}

	public static function setProductService($productService)
	{
		self::$productService = $productService;
	}

	/**
	 * Logs viewed products into the session
	 *
	 * @var $product Product
	 */
	public static function productViewTracker($product)
	{
		$instance = get_option('widget_'.self::ID);
		$number = 0;
		if (is_array($instance)) {
			foreach ($instance as $entry) {
				if (isset($entry['number'])) {
					$number = $entry['number'];
					break;
				}
			}
		}

		if (!$number) {
			return;
		}

		if (!is_array($_SESSION[self::SESSION_KEY])) {
			$_SESSION[self::SESSION_KEY] = array();
		}

		$key = array_search($product->getId(), $_SESSION[self::SESSION_KEY]);
		if ($key !== false) {
			unset($_SESSION[self::SESSION_KEY][$key]);
		}

		array_unshift($_SESSION[self::SESSION_KEY], $product->getId());

		if (count($_SESSION[self::SESSION_KEY]) > $number) {
			array_pop($_SESSION[self::SESSION_KEY]);
		}

		$_SESSION[self::SESSION_KEY] = array_values($_SESSION[self::SESSION_KEY]);
	}

	/**
	 * Displays the widget in the sidebar.
	 *
	 * @param array $args Sidebar arguments.
	 * @param array $instance The instance.
	 */
	public function widget($args, $instance)
	{
		// Check if session contains recently viewed products
		if (!isset($_SESSION[self::SESSION_KEY]) || empty($_SESSION[self::SESSION_KEY])) {
			return;
		}

		// Start buffering the output
		ob_start();

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Recently Viewed Products', 'jigoshop'),
			$instance,
			$this->id_base
		);

		// Set number of products to fetch
		if (!$number = absint($instance['number'])) {
			$number = 5;
		}

		// Set up query
		$query_args = array(
			'posts_per_page' => $number,
			'post_type' => Core\Types::PRODUCT,
			'post_status' => 'publish',
			'nopaging' => true,
			'post__in' => $_SESSION[self::SESSION_KEY],
			'meta_query' => array(
				array(
					'key' => 'visibility',
					'value' => array(Product::VISIBILITY_CATALOG, Product::VISIBILITY_PUBLIC),
					'compare' => 'IN',
				),
			)
		);

		// Run the query
		$q = new \WP_Query($query_args);
		$products = self::$productService->findByQuery($q);

		$ordered = array();
		foreach($_SESSION[self::SESSION_KEY] as $key) {
			if(isset($products[$key])) {
				$ordered[$key] = $products[$key];
			}
		}
		$products = $ordered;

		if (!empty($products)) {
			Render::output('widget/recently_viewed_products/widget', array_merge($args, array(
				'title' => $title,
				'products' => $products,
			)));
		}
	}

	/**
	 * Handles the processing of information entered in the wordpress admin
	 * Flushes the cache & removes entry from options array
	 *
	 * @param array $new_instance new instance
	 * @param array $old_instance old instance
	 * @return array instance
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		// Save the new values
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = absint($new_instance['number']);

		// Unset the session array
		unset($_SESSION[self::SESSION_KEY]);

		return $instance;
	}

	/**
	 * Displays the form for the wordpress admin.
	 *
	 * @param array $instance Instance data.
	 * @return string|void
	 */
	public function form($instance)
	{
		// Get instance data
		$title = isset($instance['title']) ? esc_attr($instance['title']) : null;
		$number = isset($instance['number']) ? absint($instance['number']) : 5;

		Render::output('widget/recently_viewed_products/form', array(
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
			'number_id' => $this->get_field_id('number'),
			'number_name' => $this->get_field_name('number'),
			'number' => $number,
		));
	}
}
