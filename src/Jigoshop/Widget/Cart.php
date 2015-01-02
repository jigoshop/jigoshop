<?php

namespace Jigoshop\Widget;

use Jigoshop\Core\Options;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Service\CartServiceInterface;

class Cart extends \WP_Widget
{
	const ID = 'jigoshop_cart';

	/** @var Pages */
	private static $pages;
	/** @var \Jigoshop\Entity\Cart */
	private static $cart;
	/** @var Options */
	private static $options;

	public function __construct()
	{
		$options = array(
			'classname' => self::ID,
			'description' => __('Shopping Cart for the sidebar', 'jigoshop')
		);

		parent::__construct(self::ID, __('Jigoshop: Cart', 'jigoshop'), $options);
	}

	/**
	 * @param $pages Pages
	 */
	public static function setPages($pages)
	{
		self::$pages = $pages;
	}

	/**
	 * @param $cart CartServiceInterface
	 */
	public static function setCart($cart)
	{
		self::$cart = $cart->getCurrent();
	}

	/**
	 * @param $options Options
	 */
	public static function setOptions($options)
	{
		self::$options = $options;
	}

	/**
	 * Display the widget in the sidebar.
	 *
	 * @param array $args Sidebar arguments.
	 * @param array $instance Instance.
	 */
	public function widget($args, $instance)
	{
		// Hide widget if page is the cart or checkout
		if (self::$pages->isCart() || self::$pages->isCheckout()) {
			return;
		}

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Cart', 'jigoshop'),
			$instance,
			$this->id_base
		);

		Render::output('widget/cart/widget', array_merge($args, array(
			'title' => $title,
			'cart' => self::$cart,
			'cart_url' => get_permalink(self::$options->getPageId(\Jigoshop\Frontend\Pages::CART)),
			'checkout_url' => get_permalink(self::$options->getPageId(\Jigoshop\Frontend\Pages::CHECKOUT)),
		)));
	}

	/**
	 * Handles the processing of information entered in the wordpress admin.
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array instance
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		// Save the new values
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['view_cart_button'] = strip_tags($new_instance['view_cart_button']);
		$instance['checkout_button'] = strip_tags($new_instance['checkout_button']);

		return $instance;
	}

	/**
	 * Displays the form for the WordPress admin.
	 *
	 * @param  array $instance The instance.
	 * @return string|void
	 */
	public function form($instance)
	{
		// Get instance data
		$title = isset($instance['title']) ? esc_attr($instance['title']) : null;
		$view_cart_button = isset($instance['view_cart_button']) ? esc_attr($instance['view_cart_button']) : 'View Cart &rarr;';
		$checkout_button = isset($instance['checkout_button']) ? esc_attr($instance['checkout_button']) : 'Checkout &rarr;';

		Render::output('widget/cart/form', array(
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
			'view_cart_button_id' => $this->get_field_id('view_cart_button'),
			'view_cart_button_name' => $this->get_field_name('view_cart_button'),
			'view_cart_button' => $view_cart_button,
			'checkout_button_id' => $this->get_field_id('checkout_button'),
			'checkout_button_name' => $this->get_field_name('checkout_button'),
			'checkout_button' => $checkout_button,
		));
	}
}
