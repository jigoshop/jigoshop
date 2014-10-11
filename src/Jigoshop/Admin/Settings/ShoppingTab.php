<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Scripts;

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

	public function __construct(Options $options, Scripts $scripts)
	{
		$this->options = $options->get(self::SLUG);
		$scripts->add('jigoshop.admin.shopping', JIGOSHOP_URL.'/assets/js/admin/settings/shopping.js', array('jquery'));
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
			array(
				'title' => __('Validation', 'jigoshop'),
				'id' => 'validation',
				'fields' => array(
					array(
						'name' => '[validate_zip]',
						'title' => __('Validate ZIP/postal code', 'jigoshop'),
						'type' => 'checkbox',
						'value' => $this->options['validate_zip'],
					),
					array(
						'name' => '[restrict_selling_locations]',
						'id' => 'restrict_selling_locations',
						'title' => __('Restrict selling locations?', 'jigoshop'),
						'description' => __('This option allows you to select what countries you want to sell to.', 'jigoshop'),
						'type' => 'checkbox',
						'value' => $this->options['restrict_selling_locations'],
					),
					array(
						'name' => '[selling_locations]',
						'id' => 'selling_locations',
						'title' => __('Selling locations', 'jigoshop'),
						'type' => 'select',
						'multiple' => true,
						'value' => $this->options['selling_locations'],
						'options' => Country::getAll(),
						'classes' => array('hidden'),
					),
				),
			),
			array(
				'title' => __('Accounts', 'jigoshop'),
				'id' => 'accounts',
				'description' => __('This section allows you to modify checkout requirements for being signed in.', 'jigoshop'),
				'fields' => array(
					array(
						'name' => '[guest_purchases]',
						'title' => __('Allow guest purchases', 'jigoshop'),
						'type' => 'checkbox',
						'value' => $this->options['guest_purchases'],
					),
					array(
						'name' => '[show_login_form]',
						'title' => __('Show login form', 'jigoshop'),
						'type' => 'checkbox',
						'value' => $this->options['show_login_form'],
					),
					array(
						'name' => '[allow_registration]',
						'title' => __('Allow registration', 'jigoshop'),
						'type' => 'checkbox',
						'value' => $this->options['show_login_form'],
					),
				),
			),
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
							'same_page' => __('The same page', 'jigoshop'),
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
		$settings['guest_purchases'] = $settings['guest_purchases'] == 'on';
		$settings['show_login_form'] = $settings['show_login_form'] == 'on';
		$settings['allow_registration'] = $settings['allow_registration'] == 'on';

		$settings['validate_zip'] = $settings['validate_zip'] == 'on';
		$settings['restrict_selling_locations'] = $settings['restrict_selling_locations'] == 'on';
		if (!$settings['restrict_selling_locations']) {
			$settings['selling_locations'] = array();
		}

		// TODO: Other settings validation

		return $settings;
	}
}
