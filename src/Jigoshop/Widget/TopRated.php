<?php

namespace Jigoshop\Widget;

use Jigoshop\Core;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class TopRated extends \WP_Widget
{
	const ID = 'jigoshop_top_rated';

	/** @var ProductServiceInterface */
	private static $productService;

	public function __construct()
	{
		$options = array(
			'classname' => self::ID,
			'description' => __('The best of the best on your site', 'jigoshop')
		);

		// Create the widget
		parent::__construct(self::ID, __('Jigoshop: Top Rated Products', 'jigoshop'), $options);

		// Flush cache after every save
		add_action('save_post', array($this, 'deleteTransient'));
		add_action('deleted_post', array($this, 'deleteTransient'));
		add_action('switch_theme', array($this, 'deleteTransient'));
	}

	public static function setProductService($productService)
	{
		self::$productService = $productService;
	}

	/**
	 * Displays the widget in the sidebar.
	 *
	 * @param array $args Sidebar arguments.
	 * @param array $instance The instance.
	 * @return bool|void
	 */
	public function widget($args, $instance)
	{
		// Get the best selling products from the transient
		$cache = get_transient(Core::WIDGET_CACHE);

		// If cached get from the cache
		if (isset($cache[$args['widget_id']])) {
			echo $cache[$args['widget_id']];

			return;
		}

		// Start buffering
		ob_start();

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Top Rated Products', 'jigoshop'),
			$instance,
			$this->id_base
		);

		// Set number of products to fetch
		if (!$number = absint($instance['number'])) {
			$number = 5;
		}

		add_filter('posts_clauses', array($this, 'ratingOrder'));

		// Set up query
		$query_args = array(
			'posts_per_page' => $number,
			'post_type' => Types::PRODUCT,
			'post_status' => 'publish',
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
		remove_filter('posts_clauses', array($this, 'ratingOrder'));

		if (!empty($products)) {
			Render::output('widget/top_rated/widget', array_merge($args, array(
				'title' => $title,
				'products' => $products,
			)));
		}

		// Flush output buffer and save to transient cache
		$cache[$args['widget_id']] = ob_get_flush();
		set_transient(Core::WIDGET_CACHE, $cache, 3600 * 3); // 3 hours ahead
	}

	public function ratingOrder($clauses)
	{
		global $wpdb;

		$clauses['where'] .= " AND {$wpdb->commentmeta}.meta_key = 'rating' ";

		$clauses['join'] .= "
			LEFT JOIN $wpdb->comments ON ({$wpdb->posts}.ID = {$wpdb->comments}.comment_post_ID)
			LEFT JOIN $wpdb->commentmeta ON({$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id)
		";

		$clauses['orderby'] = "{$wpdb->commentmeta}.meta_value DESC";
		$clauses['groupby'] = "{$wpdb->posts}.ID";

		return $clauses;
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
		$instance['title'] = trim(strip_tags($new_instance['title']));
		$instance['number'] = absint($new_instance['number']);

		// Flush the cache
		$this->deleteTransient();

		return $instance;
	}

	public function deleteTransient()
	{
		delete_transient(Core::WIDGET_CACHE);
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

		Render::output('widget/top_rated/form', array(
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
			'number_id' => $this->get_field_id('number'),
			'number_name' => $this->get_field_name('number'),
			'number' => $number,
		));
	}
}
