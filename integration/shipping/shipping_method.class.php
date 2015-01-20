<?php
use Jigoshop\Integration;

/**
 * Shipping method class
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
abstract class jigoshop_shipping_method
{
	var $id;
	var $title;
	var $availability;
	var $countries;
	var $type;
	var $cost = 0;
	var $fee = 0;
	var $min_amount = null;
	var $enabled = false;
	var $chosen = false;
	var $shipping_total = 0;
	var $shipping_tax = 0;

	protected $tax_status = '';

	protected $rates = array();
	protected $has_error = false;

	private $tax;
	private $error_message = null;

	public function __construct()
	{
		// Empty
	}

	/**
	 * @internal
	 * @return array List of default options.
	 */
	public function __get_default_options()
	{
		return $this->get_default_options();
	}

	/**
	 * Default Option settings for WordPress Settings API using an implementation of the Jigoshop_Options_Interface
	 * These should be installed on the Jigoshop_Options 'Shipping' tab
	 *
	 * @since 1.3
	 */
	protected function get_default_options()
	{
		return array();
	}

	/**
	 * @internal
	 * @return array List of all rates added to the method.
	 */
	public function __get_rates()
	{
		return $this->rates;
	}

	/**
	 * @internal
	 * @return string Tax status of the method.
	 */
	public function __get_tax_status()
	{
		return $this->tax_status;
	}

	public function is_available()
	{
		if (!$this->get_enabled()) {
			return false;
		}

		if (isset(jigoshop_cart::$cart_contents_total_ex_dl) && isset($this->min_amount) && $this->min_amount && apply_filters('jigoshop_shipping_min_amount', $this->min_amount, $this) > jigoshop_cart::$cart_contents_total_ex_dl - jigoshop_cart::$discount_total) {
			return false;
		}

		$customer = Integration::getCart()->getCustomer();
		$shippingCountries = $this->get_ship_to_countries();

		if (!empty($shippingCountries) && !in_array($customer->getShippingAddress()
				->getCountry(), $shippingCountries)
		) {
			return false;
		}

		$this->calculate_shipping();

		return !$this->has_error();
	}

	public function get_enabled()
	{
		return $this->enabled;
	}

	protected function get_ship_to_countries()
	{
		$options = Integration::getOptions();

		if ($this->availability == 'specific') {
			return $this->countries;
		} else if ($options->get('shopping.restrict_selling_locations')) {
			return $options->get('shopping.selling_locations');
		}

		return array();
	}

	public abstract function calculate_shipping();

	public function has_error()
	{
		return $this->has_error;
	}

	public function is_rate_selected($rate_index)
	{
		if ($this->is_chosen()) {
			$rate = Integration::getShippingRate();
			if (is_numeric($rate)) {
				return $rate_index == $rate;
			} else {
				return $rate_index == $this->get_cheapest_service();
			}
		}

		return false;
	}

	public function is_chosen()
	{
		return $this->chosen;
	}

	public function get_cheapest_service()
	{
		$my_cheapest_rate = $this->get_cheapest_rate();

		if ($this->title && $my_cheapest_rate['service'] != $this->title) {
			$service = $my_cheapest_rate['service'].__(' via ', 'jigoshop').$this->title;
		} else {
			$service = $my_cheapest_rate['service'];
		}

		return ($my_cheapest_rate == null ? $this->title : $service);
	}

	protected function get_cheapest_rate()
	{
		$cheapest_rate = null;
		if ($this->rates != null) {
			for ($i = 0; $i < count($this->rates); $i++) {
				if (!isset($cheapest_rate) || $this->rates[$i]['price'] < $cheapest_rate['price']) {
					$cheapest_rate = $this->rates[$i];
				}
			}
		}

		return $cheapest_rate;
	}

	public function get_error_message()
	{
		return $this->error_message;
	}

	public function set_error_message($error_message = null)
	{
		$this->error_message = $error_message;
	}

	public function choose()
	{
		$this->chosen = true;
	}

	public function set_selected_service_index($selected_service = '')
	{
		if (!empty($selected_service)) {
			for ($i = 0; $i < $this->get_rates_amount(); $i++) {
				if ($this->get_selected_service($i) == $selected_service) {
					Integration::setShippingRate($i);
					break;
				}
			}
		}
	}

	public function get_rates_amount()
	{
		return ($this->rates == null ? 1 : count($this->rates));
	}

	public function get_selected_service($rate_index)
	{
		$my_rate = $this->get_selected_rate($rate_index);

		if ($this->title && $my_rate['service'] != $this->title) {
			$service = $my_rate['service'].__(' via ', 'jigoshop').$this->title;
		} else {
			$service = $my_rate['service'];
		}

		return ($my_rate == null ? $this->title : $service);
	}

	protected function get_selected_rate($rate_index)
	{
		return !isset($this->rates[$rate_index]) ? null : $this->rates[$rate_index];
	}

	public function get_cheapest_price()
	{
		$my_cheapest_rate = $this->get_cheapest_rate();

		return apply_filters('jigoshop_shipping_total_price', ($my_cheapest_rate == null ? $this->shipping_total : $my_cheapest_rate['price']));
	}

	public function get_selected_price($rate_index)
	{
		$my_rate = $this->get_selected_rate($rate_index);

		return apply_filters('jigoshop_shipping_total_price', ($my_rate == null ? $this->shipping_total : $my_rate['price']));
	}

	public function get_selected_tax($rate_index)
	{
		$my_rate = $this->get_selected_rate($rate_index);

		return apply_filters('jigoshop_shipping_tax_price', ($my_rate == null ? $this->shipping_tax : $my_rate['tax']));
	}

	public function reset_method()
	{
		$this->chosen = false;
		$this->shipping_total = 0;
		$this->shipping_tax = 0;
		$this->tax = null;
		$this->rates = array();
		$this->has_error = false;
		$this->error_message = null;
	}

	protected function add_rate($price, $service_name)
	{
		$price += (empty($this->fee) ? 0 : $this->get_fee($this->fee, jigoshop_cart::$cart_contents_total_ex_dl));

		$tax = 0;
		if (Jigoshop_Base::get_options()
				->get('jigoshop_calc_taxes') == 'yes' && $this->tax_status == 'taxable' && $price > 0
		) {
			$tax = $this->calculate_shipping_tax($price);
		}

		if ($price >= 0) {
			$this->rates[] = array('service' => $service_name, 'price' => $price, 'tax' => $tax);
		}
	}

	public function get_fee($fee, $total)
	{
		if (strpos($fee, '%') !== false) {
			return ($total / 100) * str_replace('%', '', $fee);
		}

		return $fee;
	}

	protected function calculate_shipping_tax($rate)
	{
		// TODO: Properly calculate shipping tax
//		$tax = $this->get_tax();
//		$service = \Jigoshop\Integration::getTaxService();
//		$service->
//		$tax->calculate_shipping_tax($rate, $this->id, $tax->get_tax_classes_for_customer());

		return 0;
	}

	protected function get_tax()
	{
		_deprecated_function('get_tax', '2.0');

		return null; // TODO: What to return here?
	}

	public function set_tax($tax)
	{
		_deprecated_function('set_tax', '2.0');
	}

	protected function get_cheapest_price_tax()
	{
		$my_cheapest_rate = $this->get_cheapest_rate();

		return apply_filters('jigoshop_shipping_tax_price', ($my_cheapest_rate == null ? $this->shipping_tax : $my_cheapest_rate['tax']));
	}

	protected function create_no_shipping_rate()
	{
		$this->rates[] = array('service' => 'non-shippable', 'price' => 0, 'tax' => 0);
	}
}
