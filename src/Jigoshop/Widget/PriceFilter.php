<?php

namespace Jigoshop\Widget;

use Jigoshop\Core;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class PriceFilter extends \WP_Widget
{
	const ID = 'jigoshop_price_filter';

	/** @var Styles */
	private static $styles;
	/** @var float */
	private $max = 0.0;

	/**
	 * Constructor
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct()
	{
		$options = array(
			'classname' => self::ID,
			'description' => __('Outputs a price filter slider', 'jigoshop')
		);

		// Create the widget
		parent::__construct(self::ID, __('Jigoshop: Price Filter', 'jigoshop'), $options);

		// Add price filter init to init hook
		add_action('wp_enqueue_scripts', array($this, 'assets'));
		add_filter('jigoshop\service\product\find_by_query', array($this, 'query'));

		// Add own hidden fields to filter
		add_filter('jigoshop\get_fields', array($this, 'hiddenFields'));
	}

	public static function setStyles($styles)
	{
		self::$styles = $styles;
	}

	public function assets()
	{
		// if price filter in use on front end, load jquery-ui slider (WP loads in footer)
		if (is_active_widget(false, false, self::ID) && !is_admin()) {
			wp_enqueue_script('jquery-ui-slider');
		}

		self::$styles->add('jigoshop.widget.price_filter', JIGOSHOP_URL.'/assets/css/widget/price_filter.css');
	}

	public function query($products)
	{
		$this->max = 0.0;
		foreach ($products as $product) {
			/** @var $product Product */
			if ($product instanceof Product\Purchasable) {
				$price = $product->getPrice();

				if ($price > $this->max) {
					$this->max = $price;
				}
			}
		}

		return $products;
	}

	public function hiddenFields($fields)
	{
		if (isset($_GET['max_price'])) {
			$fields['max_price'] = $_GET['max_price'];
		}

		if (isset($_GET['min_price'])) {
			$fields['min_price'] = $_GET['min_price'];
		}

		return $fields;
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
		if (!Pages::isProductList()) {
			return;
		}

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Filter by Price', 'jigoshop'),
			$instance,
			$this->id_base
		);

		$fields = apply_filters('jigoshop\get_fields', array());

		Render::output('widget/price_filter/widget', array_merge($args, array(
			'title' => $title,
			'max' => $this->max,
			'fields' => $fields,
		)));
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

		Render::output('widget/price_filter/form', array(
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
		));
	}
}

function filter($query)
{
	if (isset($_GET['max_price']) && isset($_GET['min_price'])) {
		if (!isset($query['meta_query'])) {
			$query['meta_query'] = array();
		}

		// TODO: How to support filtering using jigoshop_price() DB function?
		// TODO: Support for variable products
		$query['meta_query'][] = array(
			'key' => 'regular_price',
			'value' => array($_GET['min_price'], $_GET['max_price']),
			'type' => 'NUMERIC',
			'compare' => 'BETWEEN'
		);
	}

	return $query;
}

add_filter('jigoshop\query\product_list_base', '\Jigoshop\Widget\filter');
