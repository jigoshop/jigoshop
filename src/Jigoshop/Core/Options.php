<?php

namespace Jigoshop\Core;

use WPAL\Wordpress;

/**
 * Options holder.
 * Use this class instead of manually calling to WordPress options database as it will cache all retrieves and updates to speed-up.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class Options
{
	const NAME = 'jigoshop';

	/** @var Wordpress */
	private $wp;

	private $defaults = array(
		'general' => array(
			'country' => 'GB',
			'email' => '',
			'show_message' => false,
			'message' => 'Demo store',
			'company_name' => '',
			'company_address_1' => '',
			'company_address_2' => '',
			'company_tax_number' => '',
			'company_phone' => '',
			'company_email' => '',
			'currency' => 'GBP',
			'currency_position' => '%1$s%3$s', // Currency symbol on the left without spaces
			'currency_decimals' => 2,
			'currency_thousand_separator' => ',',
			'currency_decimal_separator' => '.',
		),
		'shopping' => array(
			'catalog_per_page' => 12,
			'catalog_order_by' => 'post_date',
			'catalog_order' => 'DESC',
			'redirect_add_to_cart' => 'same_page',
			'redirect_continue_shopping' => 'product_list',
			'guest_purchases' => true,
			'show_login_form' => false,
			'allow_registration' => false,
			'validate_zip' => true,
			'restrict_selling_locations' => false,
			'selling_locations' => array(),
		),
		'products' => array(
			'weight_unit' => 'kg',
			'dimensions_unit' => 'cm',
			'manage_stock' => true,
			'show_stock' => true,
			'low_stock_threshold' => 2,
			'hide_out_of_stock' => false,
			'notify_low_stock' => false,
			'notify_out_of_stock' => true,
			'notify_on_backorders' => false,
		),
		'tax' => array(
			'before_coupons' => false,
			'included' => false,
			'price_tax' => 'with_tax',
			'shipping' => false,
			'classes' => array(
				array('label' => 'Standard', 'class' => 'standard'),
			),
		),
		'shipping' => array(
			'enabled' => true,
			'calculator' => true,
			'only_to_billing' => false,
			'always_show_shipping' => false,
			'flat_rate' => array(
				'enabled' => false,
				'title' => 'Flat rate',
				'type' => 'per_order',
				'cost' => 0,
				'fee' => 0,
				'available_for' => 'all',
				'countries' => array(),
			),
			'free_shipping' => array(
				'enabled' => false,
				'minimum' => 0,
				'available_for' => 'all',
				'countries' => array(),
			),
			'local_pickup' => array(
				'enabled' => false,
			),
		),
		'payment' => array(
			'cheque' => array(
				'enabled' => false,
				'title' => 'Cheque',
				'description' => 'Pay with cheque sent to shop prior to dispatching your order.',
			),
			'on_delivery' => array(
				'enabled' => false,
				'title' => 'On delivery',
				'description' => 'Pay when your order arrives at your doorstep!',
			),
			'paypal' => array(
				'enabled' => false,
				'title' => 'PayPal',
				'description' => "Pay via PayPal; you can pay with your credit card if you don't have a PayPal account.",
				'email' => '',
				'send_shipping' => true,
				'force_payment' => false,
				'test_mode' => true,
				'test_email' => '',
			),
		),
		'advanced' => array(
			'automatic_complete' => false,
			'automatic_reset' => false,
			'force_ssl' => false,
			'integrations' => array(
				'share_this' => '',
				'google_analytics' => '',
			),
		),
	);
	private $options = array();
	private $dirty = false;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$this->_loadOptions();
		$this->_addImageSizes();
		$this->wp->addAction('shutdown', array($this, 'saveOptions'));
	}

	public function getImageSizes()
	{
		return $this->wp->applyFilters('jigoshop\image\sizes', array(
			'shop_tiny' => array(
				'crop' => $this->wp->applyFilters('jigoshop\image\size\crop', false, 'shop_tiny'),
				'width' => '36',
				'height' => '36',
			),
			'shop_thumbnail' => array(
				'crop' => $this->wp->applyFilters('jigoshop\image\size\crop', false, 'shop_thumbnail'),
				'width' => '90',
				'height' => '90',
			),
			'shop_small' => array(
				'crop' => $this->wp->applyFilters('jigoshop\image\size\crop', false, 'shop_small'),
				'width' => '150',
				'height' => '150',
			),
			'shop_large' => array(
				'crop' => $this->wp->applyFilters('jigoshop\image\size\crop', false, 'shop_large'),
				'width' => '300',
				'height' => '300',
			),
		));
	}

	/**
	 * @param $name string Name of option to retrieve.
	 * @param $default mixed Default value (if not found).
	 * @return mixed Result.
	 */
	public function get($name, $default = null)
	{
		return $this->_get(explode('.', $name), $this->options, $default);
	}

	private function _get(array $names, array $options, $default = null)
	{
		$name = array_shift($names);

		if (!isset($options[$name])) {
			return $default;
		}

		if (empty($names)) {
			return $options[$name];
		}

		return $this->_get($names, $options[$name], $default);
	}

	/**
	 * @return array All available options.
	 */
	public function getAll()
	{
		return $this->options;
	}

	/**
	 * @param $name string Name of option to update.
	 * @param $value mixed Value to set.
	 */
	public function update($name, $value)
	{
		$this->options[$name] = $value;
		$this->dirty = true;
	}

	private function _addImageSizes()
	{
		$sizes = $this->getImageSizes();

		foreach ($sizes as $size => $options) {
			$this->wp->addImageSize($size, $options['width'], $options['height'], $options['crop']);
		}

		$this->wp->addImageSize('admin_product_list', 70, 70, true);
	}

	/**
	 * Saves current option values (if needed).
	 */
	public function saveOptions()
	{
		if ($this->dirty) {
			$this->wp->updateOption(self::NAME, $this->options);
		}
	}

	/**
	 * Loads stored options and merges them with default ones.
	 */
	private function _loadOptions()
	{
		$options = (array)$this->wp->getOption(self::NAME);
		foreach($this->defaults as $key => $value){
			$options[$key] = array_replace_recursive($value, isset($options[$key]) ? $options[$key] : array());
		}
		$this->options = array_merge($this->defaults, $options);
	}

	/**
	 * Retrieves id of specified Jigoshop page.
	 *
	 * @param $page string Page slug.
	 * @return mixed Page ID.
	 */
	public function getPageId($page)
	{
		return $this->wp->getOption('jigoshop_'.$page.'_id');
	}

	/**
	 * Sets id of specified Jigoshop page.
	 *
	 * @param $page string Page slug.
	 * @param $id int Page ID.
	 */
	public function setPageId($page, $id)
	{
		$this->wp->updateOption('jigoshop_'.$page.'_id', $id);
	}

	/**
	 * @return array List of names of enabled product types.
	 */
	public function getEnabledProductTypes()
	{
		// TODO: Add product types to extensions tab
		return $this->wp->applyFilters('jigoshop\product\types', $this->get('enabled_product_types', array(
			'jigoshop.product_type.simple',
			'jigoshop.product_type.variable',
		)));
	}
}
