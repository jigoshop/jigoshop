<?php

namespace Jigoshop\Widget;

use Jigoshop\Core;
use Jigoshop\Helper\Render;
use Jigoshop\Service\ProductServiceInterface;

/**
 * Recent Reviews Widget
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Widgets
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
class RecentReviews extends \WP_Widget
{
	const ID = 'jigoshop_recent_reviews';

	/** @var ProductServiceInterface */
	private static $productService;

	public function __construct()
	{
		$options = array(
			'classname' => self::ID,
			'description' => __('Display a list of your most recent product reviews', 'jigoshop')
		);

		parent::__construct(self::ID, __('Jigoshop: Recent Reviews', 'jigoshop'), $options);

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
			($instance['title']) ? $instance['title'] : __('Recent Reviews', 'jigoshop'),
			$instance,
			$this->id_base
		);

		// Set number of products to fetch
		if (!$number = absint($instance['number'])) {
			$number = 5;
		}

		// Modify get_comments query to only include products which are visible
		add_filter('comments_clauses', array($this, 'visibleProduct'));

		// Get the latest reviews
		$comments = get_comments(array(
			'number' => $number,
			'status' => 'approve',
			'post_status' => 'publish',
			'post_type' => Core\Types::PRODUCT,
		));

		$service = self::$productService;
		$comments = array_map(function($comment) use ($service){
			$comment->product = $service->find($comment->comment_post_ID);
			$comment->rating = get_comment_meta($comment->comment_ID, 'rating', true);
			return $comment;
		}, $comments);

		// If there are products
		if ($comments) {
			Render::output('widget/recent_reviews/widget', array_merge($args, array(
				'title' => $title,
				'comments' => $comments,
			)));
		}

		// Remove the filter on comments to stop other queries from being manipulated
		remove_filter('comments_clauses', array($this, 'visibleProducts'));

		// Flush output buffer and save to transient cache
		$cache[$args['widget_id']] = ob_get_flush();
		set_transient(Core::WIDGET_CACHE, $cache, 3600 * 3); // 3 hours ahead
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
	 * Modifies get_comments query to only grab comments whose products are visible
	 *
	 * @param  array  Query Arguments
	 * @return  array
	 */
	public function visibleProducts($clauses)
	{
		global $wpdb;

		// Only fetch comments whose products are visible
		$clauses['where'] .= $wpdb->prepare(" AND {$wpdb->postmeta}.meta_value IN (%d,%d)", array(Product::VISIBILITY_PUBLIC));
		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} cpm ON {$wpdb->comments}.comment_post_ID = cpm.post_id AND cpm.meta_key = 'visibility')";

		return $clauses;
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

		Render::output('widget/recent_reviews/form', array(
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
			'number_id' => $this->get_field_id('number'),
			'number_name' => $this->get_field_name('number'),
			'number' => $number,
		));
	}
}
