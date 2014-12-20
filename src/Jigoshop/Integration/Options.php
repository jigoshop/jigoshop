<?php

namespace Jigoshop\Integration;

use Jigoshop\Integration;
use Jigoshop\Integration\Admin\Settings\Tab;

class Options implements \Jigoshop_Options_Interface
{
	private static $_transformations = array();
	private static $_basicTransformations = false;

	public function __construct()
	{
		if (!self::$_basicTransformations) {
			self::$_basicTransformations = true;

			$transformations = array(
				'jigoshop_default_country' => 'general.country',
				'jigoshop_currency' => 'general.currency',
				'jigoshop_allowed_countries' => 'shopping.restrict_selling_locations',
				'jigoshop_specific_allowed_countries' => 'shopping.selling_locations',
				'jigoshop_demo_store' => 'general.show_message',
				'jigoshop_company_name' => 'general.company_name',
				'jigoshop_tax_number' => 'general.company_tax_number',
				'jigoshop_address_1' => 'general.company_address_1',
				'jigoshop_address_2' => 'general.company_address_2',
				'jigoshop_company_phone' => 'general.company_phone',
				'jigoshop_company_email' => 'general.company_email',
//				'jigoshop_prepend_shop_page_to_urls' => 'no',
//				'jigoshop_prepend_shop_page_to_product' => 'no',
//				'jigoshop_prepend_category_to_product' => 'no',
				'jigoshop_product_category_slug' => 'permalinks.category',
				'jigoshop_product_tag_slug' => 'permalinks.tag',
				'jigoshop_email' => 'general.email',
//				'jigoshop_cart_shows_shop_button' => 'yes',
				'jigoshop_redirect_add_to_cart' => 'shopping.redirect_add_to_cart',
				'jigoshop_reset_pending_orders' => 'advanced.automatic_reset',
				'jigoshop_complete_processing_orders' => 'advanced.automatic_complete',
				'jigoshop_downloads_require_login' => 'shopping.login_for_downloads',
//				'jigoshop_disable_css' => 'no',
//				'jigoshop_frontend_with_theme_css' => 'no',
//				'jigoshop_disable_fancybox' => 'no',
				'jigoshop_enable_postcode_validating' => 'shopping.validate_zip',
//				'jigoshop_verify_checkout_info_message' => 'yes',
//				'jigoshop_eu_vat_reduction_message' => 'yes',
				'jigoshop_enable_guest_checkout' => 'shopping.guest_purchases',
				'jigoshop_enable_guest_login' => 'shopping.show_login_form',
				'jigoshop_enable_signup_form' => 'shopping.allow_registration',
				'jigoshop_force_ssl_checkout' => 'advanced.force_ssl',
				'jigoshop_sharethis' => 'advanced.integration.share_this',
				'jigoshop_ga_id' => 'advanced.integration.google_analytics',
//				'jigoshop_ga_ecommerce_tracking_enabled' => 'no',
//				'jigoshop_catalog_product_button' => 'add',
				'jigoshop_catalog_sort_orderby' => 'shopping.catalog_order_by',
				'jigoshop_catalog_sort_direction' => 'shopping.catalog_order',
//				'jigoshop_catalog_columns' => '3',
				'jigoshop_catalog_per_page' => 'shopping.catalog_per_page',
//				'jigoshop_currency_pos' => 'left',
				'jigoshop_price_thousand_sep' => 'general.currency_thousand_separator',
				'jigoshop_price_decimal_sep' => 'general.currency_decimal_separator',
				'jigoshop_price_num_decimals' => 'general.currency_decimals',
//				'jigoshop_use_wordpress_tiny_crop' => 'no',
//				'jigoshop_use_wordpress_thumbnail_crop' => 'no',
//				'jigoshop_use_wordpress_catalog_crop' => 'no',
//				'jigoshop_use_wordpress_featured_crop' => 'no',
//				'jigoshop_shop_tiny_w' => 36,
//				'jigoshop_shop_tiny_h' => 36,
//				'jigoshop_shop_thumbnail_w' => 90,
//				'jigoshop_shop_thumbnail_h' => 90,
//				'jigoshop_shop_small_w' => 150,
//				'jigoshop_shop_small_h' => 150,
//				'jigoshop_shop_large_w' => 300,
//				'jigoshop_shop_large_h' => 300,
//				'jigoshop_enable_sku' => 'yes',
//				'jigoshop_enable_weight' => 'yes',
				'jigoshop_weight_unit' => 'products.weight_unit',
//				'jigoshop_enable_dimensions' => 'yes',
				'jigoshop_dimension_unit' => 'products.dimensions_unit',
//				'jigoshop_product_thumbnail_columns' => '3',
//				'jigoshop_enable_related_products' => 'yes',
				'jigoshop_manage_stock' => 'products.manage_stock',
				'jigoshop_show_stock' => 'products.show_stock',
				'jigoshop_notify_low_stock' => 'products.notify_low_stock',
				'jigoshop_notify_low_stock_amount' => 'products.low_stock_threshold',
				'jigoshop_notify_no_stock' => 'products.notify_out_of_stock',
//				'jigoshop_notify_no_stock_amount' => '0',
				'jigoshop_hide_no_stock_product' => 'products.hide_out_of_stock',
//				'jigoshop_calc_taxes' => '',
//				'jigoshop_tax_after_coupon' => '',
				'jigoshop_prices_include_tax' => 'tax.included',
				'jigoshop_tax_classes' => 'tax.classes',
				'jigoshop_tax_rates' => '',
				'jigoshop_calc_shipping' => 'shipping.enabled',
				'jigoshop_enable_shipping_calc' => 'shipping.calculator',
				'jigoshop_ship_to_billing_address_only' => 'shipping.only_to_billing',
				'jigoshop_show_checkout_shipping_fields' => 'shipping.always_show_shipping',
//				'jigoshop_default_gateway' => 'cheque',
//				'jigoshop_error_disappear_time' => 8000,
//				'jigoshop_message_disappear_time' => 4000,
			);

			foreach ($transformations as $from => $to) {
				self::__addTransformation($from, $to);
			}
		}
	}

	public static function __getTransformations()
	{
		return self::$_transformations;
	}

	public static function __addTransformation($from, $to)
	{
		self::$_transformations[$from] = $to;
	}

	public function update_options()
	{
		// Empty
	}

	public function add($name, $value)
	{
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}

		Integration::getOptions()->update($name, $value);
	}

	public function add_option($name, $value)
	{
		$this->add($name, $value);
	}

	public function get($name, $default = null)
	{
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}

		return Integration::getOptions()->get($name, $default);
	}

	public function get_option($name, $default = null)
	{
		return $this->get($name, $default);
	}

	public function set($name, $value)
	{
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}

		Integration::getOptions()->update($name, $value);
	}

	public function set_option($name, $value)
	{
		$this->set($name, $value);
	}

	public function delete($name)
	{
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}

		return Integration::getOptions()->remove($name);
	}

	public function delete_option($name)
	{
		return $this->delete($name);
	}

	public function exists($name)
	{
		if (isset(self::$_transformations[$name])) {
			$name = self::$_transformations[$name];
		}

		return Integration::getOptions()->exists($name);
	}

	public function exists_option($name)
	{
		return $this->exists($name);
	}

	public function install_external_options_tab($tab, $options)
	{
		Integration::getAdminSettings()->addTab(new Tab($tab, $options));
	}

	/**
	 * Install additional default options for parsing onto a specific Tab
	 * Shipping methods, Payment gateways and Extensions would use this
	 *
	 * @param  string  The name of the Tab ('tab') to install onto
	 * @param  array  The array of options to install
	 * @since  1.3
	 */
	public function install_external_options_onto_tab($tab, $options)
	{
		// TODO: Implement
	}

	/**
	 * Install additional default options for parsing after a specific option ID
	 * Extensions would use this
	 *
	 * @param  string  The name of the ID  to install -after-
	 * @param  array  The array of options to install
	 * @since  1.3
	 */
	public function install_external_options_after_id($insert_after_id, $options)
	{
		// TODO: Implement
	}

	public function get_current_options()
	{
		return Integration::getOptions()->getAll();
	}

	public function get_default_options()
	{
		return Integration::getOptions()->getDefaults();
	}
}
