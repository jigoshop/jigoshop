<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

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
	/** @var Messages */
	private $messages;
	/** @var array */
	private $addToCartRedirectionOptions;
	/** @var array */
	private $backToShopRedirectionOptions;
	/** @var array */
	private $catalogOrderBy;
	/** @var array */
	private $catalogOrder;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, Scripts $scripts)
	{
		$this->options = $options->get(self::SLUG);
		$this->messages = $messages;

		$this->addToCartRedirectionOptions = $wp->applyFilters('jigoshop\admin\settings\shopping\add_to_cart_redirect', array(
			'same_page' => __('The same page', 'jigoshop'),
			'product' => __('Product page', 'jigoshop'),
			'cart' => __('Cart', 'jigoshop'),
			'checkout' => __('Checkout', 'jigoshop'),
			'product_list' => __('Product list', 'jigoshop'),
		));
		$this->backToShopRedirectionOptions = $wp->applyFilters('jigoshop\admin\settings\shopping\continue_shopping_redirect', array(
			'product_list' => __('Product list', 'jigoshop'),
			'my_account' => __('My account', 'jigoshop'),
		));
		$this->catalogOrderBy = $wp->applyFilters('jigoshop\admin\settings\shopping\catalog_order_by', array(
			'post_date' => __('Date', 'jigoshop'),
			'post_title' => __('Product name', 'jigoshop'),
			'menu_order' => __('Product post order', 'jigoshop'),
		));
		$this->catalogOrder = $wp->applyFilters('jigoshop\admin\settings\shopping\catalog_order', array(
			'ASC' => __('Ascending', 'jigoshop'),
			'DESC' => __('Descending', 'jigoshop'),
		));

		$wp->addAction('admin_enqueue_scripts', function() use ($scripts){
			if (isset($_GET['tab']) && $_GET['tab'] == ShoppingTab::SLUG) {
				$scripts->add('jigoshop.admin.settings.shopping', JIGOSHOP_URL.'/assets/js/admin/settings/shopping.js', array('jquery'), array('page' => 'jigoshop_page_jigoshop_settings'));
			}
		});
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
						'type' => 'number',
						'value' => $this->options['catalog_per_page'],
					),
					array(
						'name' => '[catalog_order_by]',
						'title' => __('Order by', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['catalog_order_by'],
						'options' => $this->catalogOrderBy,
					),
					array(
						'name' => '[catalog_order]',
						'title' => __('Ordering', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['catalog_order'],
						'options' => $this->catalogOrder,
					),
				),
			),
			array(
				'title' => __('Validation', 'jigoshop'),
				'id' => 'validation',
				'fields' => array(
					array(
						'name' => '[validate_zip]',
						'title' => __('Validate postcode', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['validate_zip'],
					),
					array(
						'name' => '[restrict_selling_locations]',
						'id' => 'restrict_selling_locations',
						'title' => __('Restrict selling locations?', 'jigoshop'),
						'description' => __('This option allows you to select what countries you want to sell to.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['restrict_selling_locations'],
					),
					array(
						'name' => '[selling_locations]',
						'id' => 'selling_locations',
						'title' => __('Selling locations', 'jigoshop'),
						'type' => 'select',
						'multiple' => true,
						'value' => $this->options['selling_locations'],
						'options' => Country::getAll(),
						'classes' => array($this->options['restrict_selling_locations'] ?  '' : 'not-active'),
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
						'checked' => $this->options['guest_purchases'],
					),
					array(
						'name' => '[show_login_form]',
						'title' => __('Show login form', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['show_login_form'],
					),
					array(
						'name' => '[allow_registration]',
						'title' => __('Allow registration', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['allow_registration'],
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
						'options' => $this->addToCartRedirectionOptions,
					),
					array(
						'name' => '[redirect_continue_shopping]',
						'title' => __('Coming back to shop', 'jigoshop'),
						'description' => __("This will point users to the page you set for buttons like 'Return to shop' or 'Continue Shopping'.", 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['redirect_continue_shopping'],
						'options' => $this->backToShopRedirectionOptions,
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
		$settings['catalog_per_page'] = (int)$settings['catalog_per_page'];
		if ($settings['catalog_per_page'] <= 0) {
			$this->messages->addWarning(sprintf(__('Invalid products per page value: "%d". Value set to 12.', 'jigoshop'), $settings['catalog_per_page']));
			$settings['catalog_per_page'] = 12;
		}
		if (!in_array($settings['catalog_order_by'], array_keys($this->catalogOrderBy))) {
			$this->messages->addWarning(sprintf(__('Invalid products sorting: "%s". Value set to %s.', 'jigoshop'), $settings['catalog_order_by'], $this->catalogOrderBy['post_date']));
			$settings['catalog_order_by'] = 'post_date';
		}
		if (!in_array($settings['catalog_order'], array_keys($this->catalogOrder))) {
			$this->messages->addWarning(sprintf(__('Invalid products sorting orientation: "%s". Value set to %s.', 'jigoshop'), $settings['catalog_order'], $this->catalogOrder['DESC']));
			$settings['catalog_order'] = 'DESC';
		}

		$settings['guest_purchases'] = $settings['guest_purchases'] == 'on';
		$settings['show_login_form'] = $settings['show_login_form'] == 'on';
		$settings['allow_registration'] = $settings['allow_registration'] == 'on';

		$settings['validate_zip'] = $settings['validate_zip'] == 'on';
		$settings['restrict_selling_locations'] = $settings['restrict_selling_locations'] == 'on';

		if (!$settings['restrict_selling_locations']) {
			$settings['selling_locations'] = array();
		} else {
			$settings['selling_locations'] = array_intersect($settings['selling_locations'], array_keys(Country::getAll()));
		}
		if (!in_array($settings['redirect_add_to_cart'], array_keys($this->addToCartRedirectionOptions))) {
			$this->messages->addWarning(sprintf(__('Invalid add to cart redirection: "%s". Value set to %s.', 'jigoshop'), $settings['redirect_add_to_cart'], $this->addToCartRedirectionOptions['same_page']));
			$settings['redirect_add_to_cart'] = 'same_page';
		}
		if (!in_array($settings['redirect_continue_shopping'], array_keys($this->backToShopRedirectionOptions))) {
			$this->messages->addWarning(sprintf(__('Invalid continue shopping redirection: "%s". Value set to %s.', 'jigoshop'), $settings['redirect_continue_shopping'], $this->backToShopRedirectionOptions['product_list']));
			$settings['redirect_continue_shopping'] = 'product_list';
		}

		return $settings;
	}
}
