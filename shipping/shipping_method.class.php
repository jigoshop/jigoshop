<?php

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
class jigoshop_shipping_method
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

	protected $rates; // the rates in array format
	protected $has_error = false; // used for shipping methods that have issues and cannot be chosen

	private $tax;
	private $error_message = null;

	public function __construct()
	{
		Jigoshop_Base::get_options()->install_external_options_onto_tab(__('Shipping', 'jigoshop'), $this->get_default_options());
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

	public function is_available()
	{
		if ($this->get_enabled() == "no") {
			return false;
		}

		if (isset(jigoshop_cart::$cart_contents_total_ex_dl) && isset($this->min_amount) && $this->min_amount && apply_filters('jigoshop_shipping_min_amount', $this->min_amount, $this) > jigoshop_cart::$cart_contents_total_ex_dl - jigoshop_cart::$discount_total) {
			return false;
		}

		if (is_array($this->get_ship_to_countries()) && !in_array(jigoshop_customer::get_shipping_country(), $this->get_ship_to_countries())) {
			$this->set_error_message('Sorry, it seems that there are no available shipping methods to your location. Please contact us if you require assistance or wish to make alternate arrangements.');

			return false;
		}

		return !$this->has_error;
	}

	public function is_rate_selected($rate_index)
	{
		if($this->is_chosen()){
			if (is_numeric(jigoshop_session::instance()->selected_rate_id)) {
				return $rate_index == jigoshop_session::instance()->selected_rate_id;
			} else {
				return $rate_index == $this->get_cheapest_service();
			}
		}
		return false;
	}

	public function get_enabled()
	{
		return $this->enabled;
	}

	protected function get_ship_to_countries()
	{
		$ship_to_countries = '';

		if ($this->availability == 'specific') {
			$ship_to_countries = $this->countries;
		} else if (Jigoshop_Base::get_options()->get('jigoshop_allowed_countries') == 'specific') {
			$ship_to_countries = Jigoshop_Base::get_options()->get('jigoshop_specific_allowed_countries');
		}

		return $ship_to_countries;
	}

	public function set_error_message($error_message = null)
	{
		$this->error_message = $error_message;
	}

	public function get_error_message()
	{
		return $this->error_message;
	}

	/**
	 * sets the tax class to shipping_method. Needed to maintain current tax
	 * state from the shopping cart.
	 *
	 * @param $tax jigoshop_tax instance
	 */
	public function set_tax($tax)
	{
		$this->tax = $tax;
	}

	public function is_chosen()
	{
		if ($this->chosen) {
			return true;
		}

		return false;
	}

	// do not call this method from shipping plugins. Jigoshop core handles this

	public function choose()
	{
		$this->chosen = true;
		jigoshop_session::instance()->chosen_shipping_method_id = $this->id;
	}

	// do not call this method from shipping plugins. Jigoshop core handles this

	/**
	 * Set the index to the selected service on the session (selected_rate_id)
	 *
	 * @param string $selected_service
	 * @since 1.2
	 */
	public function set_selected_service_index($selected_service = '')
	{
		if (!empty($selected_service)) {
			for ($i = 0; $i < $this->get_rates_amount(); $i++) {
				if ($this->get_selected_service($i) == $selected_service) {
					jigoshop_session::instance()->selected_rate_id = $i;
					break;
				}
			}
		}
	}

	public function get_rates_amount()
	{
		return ($this->rates == null ? 1 : count($this->rates));
	}

	/**
	 * Retrieves the service name from the rate array based on the service selected.
	 * Override this method if you wish to provide your own user friendly service name
	 *
	 * @param $rate_index
	 * @return string - NULL if the rate by index doesn't exist, otherwise the service name associated with the
	 */
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
		return (empty($this->rates) ? null : $this->rates[$rate_index]);
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

	/**
	 * Gets the cheapest rate from the rates returned by shipping service. If an error occurred on
	 * on the shipping service service, NULL will be returned
	 */
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

	/** Override this functions if you want to provide your own label to the service name displayed */
	public function get_cheapest_price()
	{
		$my_cheapest_rate = $this->get_cheapest_rate();

		return apply_filters('jigoshop_shipping_total_price', ($my_cheapest_rate == null ? $this->shipping_total : $my_cheapest_rate['price']));
	}

	/** Called from shipping when calculating cheapest method */
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

	public function has_error()
	{
		return $this->has_error;
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
		if (Jigoshop_Base::get_options()->get('jigoshop_calc_taxes') == 'yes' && $this->tax_status == 'taxable' && $price > 0) {
			$tax = $this->calculate_shipping_tax($price);
		}

		// changed for 1.4.5 since there are instances where a shipping method may want to provide their own rules for
		// when shipping is free...that cannot be obtained within the free shipping method itself.
		if ($price >= 0) {
			$this->rates[] = array('service' => $service_name, 'price' => $price, 'tax' => $tax);
		}
	}

	public function get_fee($fee, $total)
	{
		if (strpos($fee, '%') !== false){
			return ($total / 100) * str_replace('%', '', $fee);
		}

		return $fee;
	}

	protected function calculate_shipping_tax($rate)
	{
		$tax = $this->get_tax();
		$tax->calculate_shipping_tax($rate, $this->id, $tax->get_tax_classes_for_customer());

		return 0;
	}

	protected function get_tax()
	{
		return $this->tax;
	}

	protected function get_cheapest_price_tax()
	{
		$my_cheapest_rate = $this->get_cheapest_rate();

		return apply_filters('jigoshop_shipping_tax_price', ($my_cheapest_rate == null ? $this->shipping_tax : $my_cheapest_rate['tax']));
	}

	/**
	 * If a shop sells mixed products and non-shippable products are all added to
	 * the cart, then the calculable service can call this method in that scenario
	 * and it will create a free shipping charge.
	 *
	 * @since 1.2
	 */
	protected function create_no_shipping_rate()
	{
		$this->rates[] = array('service' => 'non-shippable', 'price' => 0, 'tax' => 0);
	}
}
