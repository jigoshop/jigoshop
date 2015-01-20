<?php

namespace Jigoshop\Widget;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;

class LayeredNav extends \WP_Widget
{
	const ID = 'jigoshop_layered_nav';

	/** @var ProductServiceInterface */
	private static $productService;
	/** @var array */
	private static $parameters;

	/** @var array */
	private $products;

	public function __construct()
	{
		$options = array(
			'classname' => self::ID,
			'description' => __('Shows a custom attribute in a widget which lets you narrow down the list of shown products in categories.', 'jigoshop'),
		);

		// Create the widget
		parent::__construct(self::ID, __('Jigoshop: Layered Nav', 'jigoshop'), $options);

		add_action('wp_enqueue_scripts', array($this, 'assets'));
		add_filter('jigoshop\service\product\find_by_query', array($this, 'query'));
		add_action('init', '\Jigoshop\Widget\LayeredNav::loadParameters');

		// Add own hidden fields to filter
		add_filter('jigoshop\get_fields', array($this, 'hiddenFields'));
	}

	public static function setProductService($productService)
	{
		self::$productService = $productService;
	}

	public static function loadParameters()
	{
		self::$parameters = array();
		$attributes = self::$productService->findAllAttributes();

		foreach ($attributes as $attribute) {
			/** @var $attribute Product\Attribute */
			self::$parameters[$attribute->getId()] = isset($_GET['filter_'.$attribute->getSlug()]) ? array_filter(explode('|', $_GET['filter_'.$attribute->getSlug()])) : array();
		}
	}

	public function assets()
	{
		Styles::add('jigoshop.widget.layered_nav', JIGOSHOP_URL.'/assets/css/widget/layered_nav.css');
	}

	public function query($products)
	{
		// Filter products
		foreach (self::$parameters as $attribute => $values) {
			if (!empty($values)) {
				$products = array_filter($products, function($product) use ($attribute, $values){
					/** @var $product Product */
					if (!$product->hasAttribute($attribute)) {
						return false;
					}

					$value = $product->getAttribute($attribute)->getValue();

					if (count($values) > 0 && !is_array($value)) {
						return false;
					} else if (is_array($value)) {
						return count(array_intersect($value, $values)) == count($values);
					} else {
						return in_array($value, $values);
					}
				});
			}
		}

		$this->products = $products;

		return $products;
	}

	public function hiddenFields($fields)
	{
		foreach (self::$parameters as $key => $value) {
			$fields['filter_'.$key] = $value;
		}

		return $fields;
	}

	/**
	 * Displays the widget in the sidebar.
	 *
	 * @param array $args Sidebar arguments.
	 * @param array $instance The instance.
	 */
	public function widget($args, $instance)
	{
		// Hide widget if not product related
		if (!Pages::isProductList()) {
			return;
		}

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Filter by Attributes', 'jigoshop'),
			$instance,
			$this->id_base
		);

		$attribute = self::$productService->getAttribute($instance['attribute']);

		if ($attribute && $attribute->hasOptions()) {
			$selected = self::$parameters[$attribute->getId()];
			$productsPerOption = array();
			$products = array_filter($this->products, function($product) use ($attribute, $selected, &$productsPerOption) {
				/** @var $product Product */
				if (!$product->hasAttribute($attribute->getId())) {
					return false;
				}

				$value = $product->getAttribute($attribute->getId())->getValue();

				if (is_array($value)) {
					foreach ($value as $subValue) {
						if (!isset($productsPerOption[$subValue])) {
							$productsPerOption[$subValue] = 0;
						}
						$productsPerOption[$subValue]++;
					}

					return (bool)array_intersect($value, $selected);
				} else {
					if (!isset($productsPerOption[$value])) {
						$productsPerOption[$value] = 0;
					}
					$productsPerOption[$value]++;

					return in_array($value, $selected);
				}
			});

			if (empty($selected) || !empty($products)) {
				$fields = apply_filters('jigoshop_get_hidden_fields', array());

				Render::output('widget/layered_nav/widget', array(
					'title' => $title,
					'attribute' => $attribute,
					'selected' => $selected,
					'productsPerOption' => $productsPerOption,
					'baseUrl' => remove_query_arg('filter_'.$attribute->getSlug()),
					'fields' => $fields,
				));
			}
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
		$instance['title'] = trim(strip_tags($new_instance['title']));
		$instance['attribute'] = (int)$new_instance['attribute'];

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
		$title = (isset($instance['title'])) ? esc_attr($instance['title']) : null;
		$attribute = (isset($instance['attribute'])) ? esc_attr($instance['attribute']) : null;
		$attributes = self::$productService->findAllAttributes();

		Render::output('widget/layered_nav/form', array(
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
			'attribute_id' => $this->get_field_id('attribute'),
			'attribute_name' => $this->get_field_name('attribute'),
			'attribute' => $attribute,
			'attributes' => $attributes,
		));
	}
}

function jigoshop_layered_nav_query($filtered_posts)
{
	global $_chosen_attributes;

	if (sizeof($_chosen_attributes) > 0) {
		$matched_products = array();
		$filtered = false;

		foreach ($_chosen_attributes as $attribute => $values) {
			if (sizeof($values) > 0) {
				foreach ($values as $value) {

					$posts = get_objects_in_term($value, $attribute);
					if (!is_wp_error($posts) && (sizeof($matched_products) > 0 || $filtered)) {
						$matched_products = array_intersect($posts, $matched_products);
					} elseif (!is_wp_error($posts)) {
						$matched_products = $posts;
					}

					$filtered = true;
				}
			}
		}

		if ($filtered) {
			$matched_products[] = 0;
			$filtered_posts = array_intersect($filtered_posts, $matched_products);
		}
	}

	return $filtered_posts;
}
