<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;

/**
 * Shopping tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class ShoppingTab implements TabInterface
{
	const SLUG = 'shopping';

	/** @var array */
	private $options;

	public function __construct(Options $options)
	{
		$this->options = $options->get(self::SLUG);
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Shopping', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return self::SLUG;
	}

	/**
	 * @return array List of items to display.
	 */
	public function getSections()
	{
		return array(
			array(
				'title' => __('Redirection', 'jigoshop'),
				'id' => 'redirection',
				'fields' => array(
					array(
						'name' => '[redirect_add_to_cart]',
						'title' => __('After adding to cart', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['redirect_add_to_cart'],
						'options' => array(
							'product' => __('Product page', 'jigoshop'),
							'cart' => __('Cart', 'jigoshop'),
							'checkout' => __('Checkout', 'jigoshop'),
							'product_list' => __('Product list', 'jigoshop'),
						),
					),
					array(
						'name' => '[redirect_continue_shopping]',
						'title' => __('Coming back to shop', 'jigoshop'),
						'description' => __("This will point users to the page you set for buttons like 'Return to shop' or 'Continue Shopping'.", 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['redirect_continue_shopping'],
						'options' => array(
							'product_list' => __('Product list', 'jigoshop'),
							'my_account' => __('My account', 'jigoshop'),
						),
					),
				),
			),
			array(
				'title' => __('Catalog', 'jigoshop'),
				'id' => 'catalog',
				'fields' => array(
					array(
						'name' => '[catalog_per_page]',
						'title' => __('Items per page', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['catalog_per_page'],
					),
					array(
						'name' => '[catalog_order_by]',
						'title' => __('Order by', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['catalog_order_by'],
						'options' => array(
							'post_date' => __('Date', 'jigoshop'),
							'post_title' => __('Product name', 'jigoshop'),
							'menu_order' => __('Product post order', 'jigoshop'),
						),
					),
					array(
						'name' => '[catalog_order]',
						'title' => __('Ordering', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['catalog_order'],
						'options' => array(
							'ASC' => __('Ascending', 'jigoshop'),
							'DESC' => __('Descending', 'jigoshop'),
						),
					),
				),
			),
		);
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate(array $settings)
	{
		// TODO: Implement validate() method.
		return $settings;
	}
}
