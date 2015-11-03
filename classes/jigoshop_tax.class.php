<?php
/**
 * Tax Class
 *
 * Calculates tax added value from a total
 *
 * DISCLAIMER
 *
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
class jigoshop_tax extends Jigoshop_Base {
	public $rates;
	private $compound_tax;
	private $tax_amounts;
	private $imploded_tax_amounts;
	private $tax_divisor;
	private $shipping_tax_class;
	private $shippable;

	/**
	 * sets up current tax class without a divisor. May or may not have
	 * args.
	 */
	public function __construct(){
		// allow for multiple constructors. One with 1 arg, otherwise no arg constructor
		$this->tax_divisor = (func_num_args() == 1 ? func_get_arg(0) : -1);
		$this->init_tax();
	}

	/**
	 * provide a way to initialize the data on the class so we don't need to create a
	 * new object when we want to reset everything.
	 *
	 * @since 1.2
	 */
	public function init_tax(){
		$this->rates = $this->get_tax_rates();
		$this->compound_tax = false;
		$this->tax_amounts = array();
		$this->imploded_tax_amounts = '';
		$this->shipping_tax_class = '';
		$this->shippable = true; // default to true, as most shops will use shipping
	}

	/**
	 * create a defined string out of the array to be saved during checkout
	 *
	 * @param array array the tax array to convert
	 * @return string the array as string
	 */
	public static function array_implode($array){
		$glue = ':';
		$internal_glue = '^';

		if(!is_array($array)){
			return $array;
		}

		if(sizeof($array) <= 0){
			return '';
		}

		$array_string = array();
		foreach($array as $key => $val){
			if(is_array($val)){
				// -- reset internal_array
				$internal_array = array();
				foreach($val as $index => $value){
					$internal_array[] = "{$index}{$internal_glue}{$value}";
				}
				$val = implode(',', $internal_array);
			}

			$array_string[] = "{$key}{$glue}{$val}";
		}

		return implode('|', $array_string);
	}

	/**
	 * This function checks the tax_amounts array for the tax class passed in.
	 * If set, then tax has already been calculated for the tax class before and
	 * return true, otherwise return false.
	 *
	 * @param string $tax_class
	 * @return boolean true if the tax_amounts array has the tax class already defined, otherwise false
	 * @since 1.2
	 */
	public function has_tax($tax_class){
		return (isset($this->tax_amounts[$tax_class]));
	}

	/**
	 * creates a customized tax string to include
	 *
	 * @param float $price_ex_tax includes shipping price if shipping is available
	 * @param float $total_tax full tax amount
	 * @param float|int $shipping_tax shipping tax if available
	 * @param int $divisor
	 * @return string the tax array as a string
	 */
	public static function create_custom_tax($price_ex_tax, $total_tax, $shipping_tax = 0, $divisor = -1){
		if(!empty($total_tax)){
			if(empty($shipping_tax)){
				$shipping_tax = 0;
			}

			if(empty($divisor)){
				$divisor = -1;
			}

			// absolute order must be amount, rate, compound, display, shipping. This is how the original tax
			// array is created, and order matters when calling array_implode as reversing
			// the string back into the array depends on the order
//            $tax_amount['jigoshop_custom_rate']['amount'] = ($divisor > 0 ? ($total_tax - $shipping_tax) * $divisor : $total_tax - $shipping_tax);
			// NOTE: above line commented out in 1.3, this function only used in order-data-save.php if an order is altered and we want total tax
			$tax_amount['jigoshop_custom_rate']['amount'] = ($divisor > 0 ? $total_tax * $divisor : $total_tax);
			$tax_rate = (empty($price_ex_tax) ? 0 : $total_tax / $price_ex_tax) * 100;
			$tax_amount['jigoshop_custom_rate']['rate'] = number_format($tax_rate, 4, '.', '');
			$tax_amount['jigoshop_custom_rate']['compound'] = false;
			$tax_amount['jigoshop_custom_rate']['display'] = __('Tax', 'jigoshop');
			$tax_amount['jigoshop_custom_rate']['shipping'] = ($divisor > 0 ? $shipping_tax * $divisor : $shipping_tax);

			return self::array_implode($tax_amount);
		}

		return '';
	}

	/**
	 * Converts the string back into the array. To be used with the order class
	 *
	 * @param string $taxes_as_string the string that was originally coverted from an array
	 * @param float $tax_divisor the divisor used on the tax to avoid decimal rounding errors
	 * @return array Array of tax information
	 */
	public static function get_taxes_as_array($taxes_as_string, $tax_divisor = -1){
		$tax_classes = array();

		if($taxes_as_string){
			$taxes = explode('|', $taxes_as_string);

			foreach($taxes as $tax){
				@list($tax_class, $tax_info) = explode(':', $tax);
				if($tax_info !== null){
					$tax_info = explode(',', $tax_info);
					foreach($tax_info as $info){
						$value = explode('^', $info);
						if(in_array($value[0], array('rate', 'display', 'compound'))){
							$tax_classes[$tax_class][$value[0]] = (sizeof($value) > 1 ? ($value[0] == 'compound' && $value[1] == null ? false : $value[1]) : ($value[0] == 'compound' ? false : ''));
						} else {
							$tax_classes[$tax_class][$value[0]] = (sizeof($value) > 1 ? ($tax_divisor > 0 ? $value[1] / $tax_divisor : $value[1]) : '');
						}
					}
				}
			}
		}

		return $tax_classes;
	}

	/**
	 * the accessor to the string of tax data
	 *
	 * @return string string of imploded tax amounts
	 */
	public function get_taxes_as_string(){
		return $this->imploded_tax_amounts;
	}

	/**
	 * The tax divisor to be used to avoid floating point rounding errors
	 *
	 * @return int integer tax divisor
	 */
	public function get_tax_divisor(){
		return $this->tax_divisor;
	}

	// TODO: currently used for admin pages. This should change in the future and the proper data displayed on the admin pages
	// this doesn't work correctly as shipping is applied to grand total, and also, discounts etc. Therefore, this calculation is only right if
	// the grandtotal was the total of the cart without the rest. Still this is not a good way of figuring out the tax rate.
	// Ultimately, fixing the admin panel to show all tax classes applied will be the best way.
	public function get_total_tax_rate($total_item_price = 0){
		$tot_tax_rate = 0;
		//don't include shipping in the call, as the total item price doesn't include shipping
		$total_tax = $this->get_compound_tax_amount(false) + $this->get_non_compounded_tax_amount(false);
		if($total_item_price > 0){
			$tot_tax_rate = (self::get_options()
				->get('jigoshop_prices_include_tax') == 'yes' ? round($total_tax / ($total_item_price - $total_tax) * 100, 2) : round($total_tax / $total_item_price * 100, 2));
		}

		return $tot_tax_rate;
	}

	/**
	 * Get an array of tax classes
	 *
	 * @return  array
	 */
	function get_tax_classes(){
		$classes = self::get_options()->get('jigoshop_tax_classes');
		$classes = explode("\n", $classes);
		$classes_array = array();

		if(is_array($classes)){
			$classes = array_map('trim', $classes);
			$classes_array = array();
			foreach($classes as $class){
				if($class){
					$classes_array[] = $class;
				}
			}
		}

		return $classes_array;
	}

	/**
	 * Get the tax rates as an array
	 *
	 * @return  array
	 */
	function get_tax_rates(){
		$tax_rates = self::get_options()->get('jigoshop_tax_rates');
		$tax_rates_array = array();
		if(is_array($tax_rates)){
			foreach($tax_rates as $rate){
				if(!isset($rate['class'])){
					// Standard Rate
					$rate['class'] = '*';
				}
				if($rate['state'] === null){
					$rate['state'] = '*';
				}
				$tax_rates_array[$rate['country']][$rate['state']][$rate['class']] = array(
					'rate' => $rate['rate'],
					'shipping' => $rate['shipping'],
					'compound' => $rate['compound'],
					'label' => $rate['label']
				);
			}
    }

		return $tax_rates_array;
	}

	/**
	 * Searches for a country / state tax rate
	 *
	 * @param $country
	 * @param string $state
	 * @param string $tax_class
	 * @internal
	 * @return int
	 */
	private function find_rate($country, $state = '*', $tax_class = '*'){
		$rate['rate'] = 0;

		if(!jigoshop_countries::country_has_states($country)){
			$state = '*'; // make sure $state is set to * if user has input some value for a state
		}

		if(isset($this->rates[$country][$state])){
			if($tax_class && isset($this->rates[$country][$state][$tax_class])){
				$rate = $this->rates[$country][$state][$tax_class];
			}
		} else if(isset($this->rates[$country]['*'])){
			if($tax_class && isset($this->rates[$country]['*'][$tax_class])){
				$rate = $this->rates[$country]['*'][$tax_class];
			}
		}

		// if compound tax has been set to true, don't reset it.
		// eg. Shipping tax could reset it to false again
		if(!$this->compound_tax){
			$this->compound_tax = (isset($rate['compound']) && $rate['compound'] == 'yes' ? true : false);
		}

		return $rate;
	}

	/**
	 * @return string Customer country to be used as base for taxes.
	 */
	public static function get_customer_country()
	{
		if (Jigoshop_Base::get_options()->get('jigoshop_country_base_tax') == 'shipping_country') {
			return jigoshop_customer::get_shipping_country();
		}

		return jigoshop_customer::get_country();
	}

	/**
	 * @return string Customer state to be used as base for taxes.
	 */
	public static function get_customer_state()
	{
		if (Jigoshop_Base::get_options()->get('jigoshop_country_base_tax') == 'shipping_country') {
			return jigoshop_customer::get_shipping_state();
		}

		return jigoshop_customer::get_state();
	}

	/**
	 * Gets the tax classes for the customer based on customer shipping
	 * country and state.
	 *
	 * @return array array of tax classes
	 */
	public function get_tax_classes_for_customer(){
		// if local pickup, we need to use the base tax classes
		if(jigoshop_session::instance()->chosen_shipping_method_id == 'local_pickup'){
			return $this->get_tax_classes_for_base();
		}

		$allowed_countries = Jigoshop_Base::get_options()->get('jigoshop_allowed_countries');
		$country = self::get_customer_country();
		$state = self::get_customer_state();

		if($allowed_countries === 'specific'){
			$specific_countries = Jigoshop_Base::get_options()->get('jigoshop_specific_allowed_countries');
			$base_cc = jigoshop_countries::get_base_country();
			if(is_array($specific_countries) && !in_array($country, $specific_countries)){
				if(in_array($base_cc, $specific_countries)){
					$country = $base_cc;
				} else {
					$country = array_shift($specific_countries);
				}
			}
			if(jigoshop_countries::country_has_states($country) && !in_array($state, array_keys(jigoshop_countries::get_states($country)))){
				if(isset($this->rates[$country])){
					$states = array_keys($this->rates[$country]);
					$state = array_shift($states);
				} else {
					$state = jigoshop_countries::get_base_state();
				}
			}
		}

		$state = ($state && jigoshop_countries::country_has_states($country) ? $state : '*');
		$tax_classes = (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state] : false);

		return ($tax_classes && is_array($tax_classes) ? array_keys($tax_classes) : array());
	}

	/**
	 * Gets the tax classes for the shops base country and state
	 *
	 * @return array array of tax classes
	 */
	public function get_tax_classes_for_base(){
		$country = jigoshop_countries::get_base_country();
		$state = jigoshop_countries::get_base_state();
		$state = ($state && jigoshop_countries::country_has_states($country) ? $state : '*');
		$tax_classes = (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state] : false);

		return ($tax_classes && is_array($tax_classes) ? array_keys($tax_classes) : array());
	}

	/**
	 * Returns the label for display for base country, if one is set. Otherwise Tax will be returned
	 *
	 * @param string $class the tax class to lookup
	 * @return string label for online display
	 * @since 1.2
	 */
	private function get_online_label_for_base($class = '*'){
		$country = jigoshop_countries::get_base_country();
		$state = jigoshop_countries::get_base_state();
		$state = (jigoshop_countries::country_has_states($country) && $state ? $state : '*');

		return (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state][$class]['label'] : __('Tax', 'jigoshop'));
	}

	private function get_online_label_for_customer($class = '*'){
		if(jigoshop_session::instance()->chosen_shipping_method_id == 'local_pickup'){
			return $this->get_online_label_for_base($class);
		}

		$allowed_countries = Jigoshop_Base::get_options()->get('jigoshop_allowed_countries');
		$country = self::get_customer_country();
		$state = self::get_customer_state();

		if($allowed_countries === 'specific'){
			$specific_countries = Jigoshop_Base::get_options()->get('jigoshop_specific_allowed_countries');
			if(is_array($specific_countries) && !in_array($country, $specific_countries)){
				$country = array_shift($specific_countries);
			}

			if(isset($this->rates[$country])) {
				if (!in_array($state, array_keys($this->rates[$country]))) {
					$states = array_keys($this->rates[$country]);
					$state = array_shift($states);
				}
			}
		}

		$state = (jigoshop_countries::country_has_states($country) && $state ? $state : '*');

		return (isset($this->rates[$country]) && isset($this->rates[$country][$state]) ? $this->rates[$country][$state][$class]['label'] : __('Tax', 'jigoshop'));
	}

	/**
	 * determines if tax is compound tax or not. Since tax classes
	 * are ordered according if they are compounded or not, this will be true
	 * when all tax calculations are completed if there was mixed class types.
	 *
	 * @param array $rate
	 * @return boolean true if compound tax, otherwise false
	 */
	public function is_compound_tax($rate = array()){
		return (empty($rate) ? $this->compound_tax : (isset($rate['compound']) && $rate['compound'] == 'yes' ? true : false));
	}

	/**
	 * Returns the total tax amount for all classes based on compounded or not compounded
	 *
	 * @param boolean $compounded
	 * @param bool $inc_shipping
	 * @return double Total tax amount of all classes on $compounded value. If $compounded is true, then
	 * the total amount of compounded tax amounts will be returned. Otherwise the total amount of regular
	 * tax will be returned
	 * @since 1.2
	 */
	private function get_total_tax_amount($compounded, $inc_shipping = true){
		$tax_amount = 0;

		if(!empty($this->tax_amounts)){
			foreach($this->get_applied_tax_classes() as $tax_class){
				if(isset($this->tax_amounts[$tax_class]['amount']) && isset($this->tax_amounts[$tax_class]['compound'])){
					if(($compounded && $this->tax_amounts[$tax_class]['compound'] == 'yes') || (!$compounded && $this->tax_amounts[$tax_class]['compound'] != 'yes')){
						$tax_amount += round($this->tax_amounts[$tax_class]['amount']);
						if($inc_shipping && isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id])){
							$tax_amount += round($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id], 2);
						}
					}
				}
			}
		}

		return $tax_amount;
	}

	/**
	 * Sets whether the product being taxed is shippable or not
	 *
	 * @param boolean $is_shippable
	 * @since 1.2
	 */
	public function set_is_shipable($is_shippable = true){
		$this->shippable = $is_shippable;
	}

	public function get_total_shipping_tax_amount(){
		$tax_amount = 0;

		if(!empty($this->tax_amounts)){
			foreach($this->get_applied_tax_classes() as $tax_class){
				if(isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id])){
					$tax_amount += round($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id], 2);
				}
			}
		}

		return ($this->tax_divisor > 0 ? number_format($tax_amount / $this->tax_divisor, 2, '.', '') : number_format($tax_amount, 2, '.', ''));
	}

	/**
	 * Gets the amount of tax that has not been compounded
	 *
	 * @param bool $inc_shipping
	 * @return double Value of non compound tax tax
	 */
	public function get_non_compounded_tax_amount($inc_shipping = true){
		$tax_amount = $this->get_total_tax_amount(false, $inc_shipping);

		//TODO: number_format... might need to change this because of jigoshop options available for formatting numbers on cart
		return ($this->tax_divisor > 0 ? number_format($tax_amount / $this->tax_divisor, 2, '.', '') : number_format($tax_amount, 2, '.', ''));
	}

	/**
	 * Gets the amount of tax that has been compounded
	 *
	 * @param bool $inc_shipping
	 * @return float value of compound tax
	 */
	public function get_compound_tax_amount($inc_shipping = true){
		$tax_amount = $this->get_total_tax_amount(true, $inc_shipping);

		return ($this->tax_divisor > 0 ? number_format(($tax_amount / $this->tax_divisor), 2, '.', '') : number_format($tax_amount, 2, '.', ''));
	}

	/**
	 * Calculates the taxes on the total item price and creates the tax data array
	 *
	 * @param float $total_item_price the total value of the item
	 * @param array $tax_classes the tax classes applicable to the item
	 * @param array|bool $prices_include_tax determines if the tax was already included in the product or not
	 * @return array
	 */
	public function calculate_tax_amounts($total_item_price, $tax_classes, $prices_include_tax = true){
		$tax_amount = array();
		$tax_classes_applied = array();
		$non_compound_tax_amount = 0;
		$compounded_tax_amount = 0;
		$total_tax = 0;

		// using order of tax classes for customer since order is important
		if($this->get_tax_classes_for_customer()){
			$customer_tax_classes = ($prices_include_tax ? array_reverse($this->get_tax_classes_for_customer()) : $this->get_tax_classes_for_customer());
			foreach($customer_tax_classes as $tax_class){

				// make sure that the product is charging this particular tax_class.
				if(!in_array($tax_class, $tax_classes)){
					continue;
				}

				$rate = $this->get_rate($tax_class, false);
				$tax_rate = $rate['rate'];

				if(!$this->is_compound_tax($rate)){
					$tax = $this->calc_tax($total_item_price - $compounded_tax_amount, $tax_rate, $prices_include_tax);

					if($this->has_tax($tax_class)){
						$this->update_tax_amount($tax_class, $tax, false);
						$tax_classes_applied[] = $tax_class;
					} else {
						$tax_amount[$tax_class]['amount'] = $tax;
						$tax_amount[$tax_class]['rate'] = $tax_rate;
						$tax_amount[$tax_class]['compound'] = false;
						$tax_amount[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : __('Tax', 'jigoshop'));
						$tax_classes_applied[] = $tax_class;
					}

					$non_compound_tax_amount += $tax;
				} else {
					$tax = $this->calc_tax($total_item_price + $non_compound_tax_amount, $tax_rate, $prices_include_tax);

					if($this->has_tax($tax_class)){
						$this->update_tax_amount($tax_class, $tax, false);
						$tax_classes_applied[] = $tax_class;
					} else {
						$tax_amount[$tax_class]['amount'] = $tax;
						$tax_amount[$tax_class]['rate'] = $tax_rate;
						$tax_amount[$tax_class]['compound'] = true;
						$tax_amount[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : __('Tax', 'jigoshop'));
						$tax_classes_applied[] = $tax_class;
					}

					$compounded_tax_amount += $tax;
				}

				$total_tax += $tax;
			}
		} else {
			$tax_classes = $this->get_tax_classes_for_base();

			if(!empty($tax_classes)){
				foreach($tax_classes as $tax_class){
					// auto calculate zero rate for all other countries outside of tax base
					$tax_amount[$tax_class]['amount'] = 0;
					$tax_amount[$tax_class]['rate'] = 0;
					$tax_amount[$tax_class]['compound'] = false;
					$tax_amount[$tax_class]['display'] = ($this->get_online_label_for_base($tax_class) ? $this->get_online_label_for_base($tax_class) : __('Tax', 'jigoshop'));
					$tax_classes_applied[] = $tax_class;
				}
			} else {
				// auto calculate zero rate for all other countries outside of tax base
				$tax_amount['jigoshop_zero_rate']['amount'] = 0;
				$tax_amount['jigoshop_zero_rate']['rate'] = 0;
				$tax_amount['jigoshop_zero_rate']['compound'] = false;
				$tax_amount['jigoshop_zero_rate']['display'] = __('Tax', 'jigoshop');
				$tax_classes_applied[] = 'jigoshop_zero_rate';
			}
		}

		$this->tax_amounts = (empty($this->tax_amounts) ? $tax_amount : array_merge($this->tax_amounts, $tax_amount));
		$this->imploded_tax_amounts = self::array_implode($this->tax_amounts);

		return $tax_classes_applied;
	}

	/**
	 * calculates the total tax rate that is applied to a product from the applied
	 * tax classes defined on the product.
	 *
	 * @param array $product_rates_array the product rates array
	 * @return mixed null if no taxes or array is null, otherwise the total tax rate to apply
	 */
	public static function calculate_total_tax_rate($product_rates_array){

		$tax_rate = null;
		if($product_rates_array && is_array($product_rates_array) && self::get_options()->get('jigoshop_calc_taxes') == 'yes' && !empty($product_rates_array)){
			$tax_rate = 0;

			foreach($product_rates_array as $tax_class => $value){
				if(jigoshop_product::get_non_compounded_tax($tax_class, $product_rates_array)){
					$tax_rate += round(jigoshop_product::get_product_tax_rate($tax_class, $product_rates_array), 4);
				} else {
					$tax_rate += round((100 + $tax_rate) * (jigoshop_product::get_product_tax_rate($tax_class, $product_rates_array) / 100), 4);
				}
			}
		}

		return $tax_rate;
	}

	/**
	 * Gets applied shipping tax classes
	 *
	 * @return array Tax classes that have applied shipping
	 * @since 1.2
	 */
	public function get_shipping_tax_classes(){
		// TODO: once deprecated functions for shipping go, the new_shipping_tax flag
		// can go, and just return an empty array or the array of tax classes obtained
		// from the tax_amounts array.
		$tax_classes = array();
		$new_shipping_tax = false;

		foreach($this->tax_amounts as $tax_class => $value){
			if(isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id])){
				$tax_classes[] = $tax_class;
				$new_shipping_tax = true;
			}
		}

		if($new_shipping_tax){
			return $tax_classes;
		} else {
			return ($this->shipping_tax_class ? array($this->shipping_tax_class) : array());
		}
	}

	/**
	 * @deprecated shipping classes are to use calculate_shipping_tax($price, $tax_classes)
	 */
	public function update_tax_amount_with_shipping_tax($tax_amount){
		// shipping taxes may not be checked, and if they aren't, there will be no shipping tax class. Don't update
		// as the amount will be 0
		$new_shipping_tax = false;
		foreach($this->tax_amounts as $tax_class => $value){
			if(isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id])){
				$new_shipping_tax = true;
				break;
			}
		}

		if(!$new_shipping_tax){
			if($this->shipping_tax_class){
				$this->update_tax_amount($this->shipping_tax_class, round($tax_amount), false);
			}
		}
	}

	/**
	 * Calculate the shipping tax using the final value
	 *
	 * @param float $price The price.
	 * @param float $rate Tax rate.
	 * @return float
	 * @deprecated - use calculate_shipping_tax($price, $tax_classes) instead
	 */
	public function calc_shipping_tax($price, $rate){
		$rate = round($rate, 4);
		$tax_amount = $price * ($rate / 100);
		return round($tax_amount, 2);
	}

	public function update_tax_amount($tax_class, $amount, $recalculate_tax = true, $overwrite = false){
		if($tax_class){
			if(empty($this->tax_amounts)){
				$rate = $this->get_rate($tax_class, false);
				$this->tax_amounts[$tax_class]['rate'] = $rate['rate'];
				$this->tax_amounts[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : __('Tax', 'jigoshop'));
				$this->tax_amounts[$tax_class]['compound'] = false;
			}

			if($recalculate_tax){
				$rate = $this->get_rate($tax_class);
				$tax = $this->calc_tax($amount, $rate, ($this->is_compound_tax() ? false : self::get_options()->get('jigoshop_prices_include_tax') == 'yes'));
				$this->tax_amounts[$tax_class]['amount'] = $tax;
			} else if($overwrite){
				$this->tax_amounts[$tax_class]['amount'] = $amount;
			} else {
				if(isset($this->tax_amounts[$tax_class]['amount'])){
					$this->tax_amounts[$tax_class]['amount'] += $amount;
				} else {
					$this->tax_amounts[$tax_class]['amount'] = $amount;
				}
			}

			$this->imploded_tax_amounts = self::array_implode($this->tax_amounts);
		}
	}

    /**
     * Retrieve the tax classes that have been applied to the items
     * @return array Array of tax classes
     */
	public function get_applied_tax_classes(){
		return ($this->tax_amounts && is_array($this->tax_amounts) ? array_keys($this->tax_amounts) : array());
	}

	/**
	 * Gets the tax class that was entered by the user to display
	 *
	 * @param string $tax_class the tax class to retreive
	 * @return string which is the unsanitized tax class
	 */
	public function get_tax_class_for_display($tax_class){
		return (!empty($this->tax_amounts[$tax_class]['display']) ? $this->tax_amounts[$tax_class]['display'] : __('Tax', 'jigoshop'));
	}

	/**
	 * Gets the amount of tax for the particular tax class
	 *
	 * @param string $tax_class the tax class to retrieve the tax amount for
	 * @return float returns the tax amount with 2 decimal places
	 */
	public function get_tax_amount($tax_class){
		$tax_amount = 0;

		if(isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id])){
			$tax_amount += round($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id], 2);
		}

		if(isset($this->tax_amounts[$tax_class]['amount'])){
			$tax_amount += round($this->tax_amounts[$tax_class]['amount']);
		}

		return ($this->tax_divisor > 0 ? $tax_amount / $this->tax_divisor : $tax_amount);
	}

	/**
	 * get the tax rate at which the tax class is applying
	 *
	 * @param string $tax_class the class to find the rate for
	 * @return mixed the rate of tax or false if the rate hasn't been set on the class (error)
	 */
	public function get_tax_rate($tax_class){
		return (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class]['rate']) ? $this->tax_amounts[$tax_class]['rate'] : false);
	}

	/**
	 * validate if this is not compound tax or not. If it's compound tax
	 * the tax gets applied once all non compounded taxes have been added into the
	 * retail price of the item to the subtotal.
	 *
	 * @param string $tax_class the class to find if compound tax or not
	 * @return bool true if not compound tax otherwise false. Defaults to true
	 */
	public function is_tax_non_compounded($tax_class){
		return (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class]['compound']) ? !$this->tax_amounts[$tax_class]['compound'] : true);
	}

	/**
	 * Get the current taxation rate using find_rate()
	 *
	 * @param   string $tax_class the tax class to find rate on
	 * @param   boolean $rate_only if true, returns the tax rate, otherwise return the full rate array
	 * @return  mixed return current rate array if rate_only is false, otherwise
	 * return the double value of the rate
	 */
	public function get_rate($tax_class = '*', $rate_only = true){
		if(jigoshop_session::instance()->chosen_shipping_method_id == 'local_pickup') {
			return $this->get_shop_base_rate($tax_class, $rate_only);
		}

		$allowed_countries = Jigoshop_Base::get_options()->get('jigoshop_allowed_countries');
		$country = self::get_customer_country();
		$state = self::get_customer_state();

		if($allowed_countries === 'specific'){
			$specific_countries = Jigoshop_Base::get_options()->get('jigoshop_specific_allowed_countries');
			if(is_array($specific_countries) && !in_array($country, $specific_countries)){
				$country = array_shift($specific_countries);
			}
		}

		$has_states = jigoshop_countries::country_has_states($country);
		if($has_states && isset($this->rates[$country]) && !in_array($state, array_keys($this->rates[$country]))){
			$states = array_keys($this->rates[$country]);
			$state = array_shift($states);
		}

		$state = $has_states && $state ? $state : '*';
		$rate = $this->find_rate($country, $state, $tax_class);

		return ($rate_only ? $rate['rate'] : $rate);
	}

	/**
	 * Get the shop's taxation rate using find_rate()
	 *
	 * @param string $tax_class is the tax class (not object)
	 * @param bool $rate_only
	 * @return int
	 */
	public function get_shop_base_rate($tax_class = '*', $rate_only = true){
		$country = jigoshop_countries::get_base_country();
		$state = jigoshop_countries::get_base_state();
		$state = ($state && jigoshop_countries::country_has_states($country) ? $state : '*');

		$rate = $this->find_rate($country, $state, $tax_class);

		return ($rate_only ? $rate['rate'] : $rate);
	}

	private function get_shipping_tax_rates($tax_classes = array()){
		$rates = array();
		$country = jigoshop_customer::get_shipping_country();
		$state = jigoshop_customer::get_shipping_state();

		// retains order of tax classes for compound tax
		$customer_tax_classes = $this->get_tax_classes_for_customer();
		// If we are here then shipping is taxable - work it out
		if(!empty($tax_classes)){
			if($customer_tax_classes){
				//per item shipping
				foreach($customer_tax_classes as $tax_class){
					// make sure that the product is charging this particular tax_class.
					if(!in_array($tax_class, $tax_classes)){
						continue;
					}

					$rate = $this->find_rate($country, $state, $tax_class);
					if(isset($rate['shipping']) && $rate['shipping'] == 'yes'){
						$rates[$tax_class] = $rate;
					}
				}

				return $rates;
			}
		} else {
			//per order shipping
			$found_shipping_rates = array();

			$total_tax_rate = 0;
			foreach($customer_tax_classes as $tax_class){
				$found_rate = $this->find_rate($country, $state, $tax_class);

				if(isset($found_rate['shipping']) && $found_rate['shipping'] == 'yes'){
					$total_tax_rate += $found_rate['rate'];
					$found_shipping_rates[$tax_class] = $found_rate;
				}
			}

			if($total_tax_rate){
				$rates[$total_tax_rate] = $found_shipping_rates;
			}

			if(sizeof($rates) > 0){
				// sort reverse by keys. Largest key wins
				krsort($rates);
				// make sure pointer at first element in array
				reset($rates);

				return $rates[key($rates)];
			}
		}

		return $rates; // it will be an empty array
	}

	/**
	 * Get the tax rate based on the country and state.
	 *
	 * @param   string $tax_class is the tax class that has shipping tax applied
	 * @return  mixed
	 * @deprecated - use calculate_shipping_tax($price, $tax_classes) to calculate shipping taxes. No need to get the rates first
	 */
	function get_shipping_tax_rate($tax_class = ''){
		$this->shipping_tax_class = '';
		$country = jigoshop_customer::get_shipping_country();
		$state = jigoshop_customer::get_shipping_state();

		// If we are here then shipping is taxable - work it out
		if($tax_class){
			// This will be per item shipping
			$rate = $this->find_rate($country, $state, $tax_class);

			if(isset($rate['shipping']) && $rate['shipping'] == 'yes'){
				$this->shipping_tax_class = $tax_class;

				return $rate['rate'];
			} else {
				// Get standard rate
				$rate = $this->find_rate($country, $state);
				if(isset($rate['shipping']) && $rate['shipping'] == 'yes'){
					$this->shipping_tax_class = '*'; //standard rate
					return $rate['rate'];
				}
			}
		} else {
			// This will be per order shipping - loop through the order and find the highest tax class rate
			$found_shipping_rates = array();
			$customer_tax_classes = $this->get_tax_classes_for_customer();

			foreach($customer_tax_classes as $tax_class){
				$found_rate = $this->find_rate($country, $state, $tax_class);

				if(isset($found_rate['shipping']) && $found_rate['shipping'] == 'yes'){
					$this->shipping_tax_class = $tax_class;
					$found_shipping_rates[] = $found_rate['rate'];
				}
			}

			if(sizeof($found_shipping_rates) > 0){
				rsort($found_shipping_rates);

				return $found_shipping_rates[0];
			}
		}

		return 0; // return false
	}

	/**
	 * Calculate the tax using the final value
	 *
	 * @param float $price
	 * @param float $rate
	 * @param bool $price_includes_tax
	 * @return  int
	 */
	public function calc_tax($price, $rate, $price_includes_tax = true){
		// To avoid float rounding errors, work with integers (pence)
		$price = round($price * 100, 0);

		if($price_includes_tax){
			$rate = ($rate / 100) + 1;
			$tax_amount = $price - ($price / $rate);
		} else {
			$tax_amount = $price * ($rate / 100);
		}

		$tax_amount = round($tax_amount); // Round to the nearest pence
		$tax_amount = $tax_amount / 100; // Back to pounds

		//TODO: number_format... may need to change this because of jigoshop options with number formatting
		return number_format($tax_amount, 2, '.', '');
	}


	/**
	 * Calculate the shipping tax using the final price from shipping
	 *
	 * @param $price float Shipping cost (always excluding tax)
	 * @param $shipping_method_id
	 * @param array $tax_classes tax_classes - the tax_classes from the product if per-item
	 * @since   1.2
	 */
	public function calculate_shipping_tax($price, $shipping_method_id, $tax_classes = array()){
		$rates = $this->get_shipping_tax_rates($tax_classes);
		$non_compound_amount = 0;
		$selected_rate_id = jigoshop_session::instance()->selected_rate_id;

		if(!empty($rates)){
			foreach($rates as $tax_class => $rate){
				if (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class][$shipping_method_id.$selected_rate_id])) {
					continue;
				}

				// no tax on products, but shipping tax should be applied
				if(!isset($this->tax_amounts[$tax_class])){
					$this->tax_amounts[$tax_class]['amount'] = 0;
					$this->tax_amounts[$tax_class]['rate'] = $rate['rate'];
					$this->tax_amounts[$tax_class]['compound'] = $rate['compound'];
					$this->tax_amounts[$tax_class]['display'] = ($this->get_online_label_for_customer($tax_class) ? $this->get_online_label_for_customer($tax_class) : __('Tax', 'jigoshop'));
					$this->tax_amounts[$tax_class][$shipping_method_id.$selected_rate_id] = 0;
				}

				// initialize shipping if not already initialized
				if(!isset($this->tax_amounts[$tax_class][$shipping_method_id.$selected_rate_id])){
					$this->tax_amounts[$tax_class][$shipping_method_id.$selected_rate_id] = 0;
				}

				$tax_rate = round($rate['rate'], 4);
				if($rate['compound'] == 'yes'){
					// calculate compounded taxes. Increment value because of per-item shipping
					$this->tax_amounts[$tax_class][$shipping_method_id.$selected_rate_id] += ($this->tax_divisor > 0 ? (($price + $non_compound_amount) * ($tax_rate / 100) * $this->tax_divisor) : ($price + $non_compound_amount) * ($tax_rate / 100));
				} else {
					// calculate regular taxes. Increment value because of per-item shipping
					$non_compound_amount += ($price * ($tax_rate / 100)); // don't use divisor here, as it will be used with compound tax above
					$tax = $price * $tax_rate / 100;
					if ($this->tax_divisor > 0) {
						$tax *= $this->tax_divisor;
					}
					$this->tax_amounts[$tax_class][$shipping_method_id.$selected_rate_id] += $tax;
				}
			}
		} else {
			$tax_classes = $this->get_tax_classes_for_base();

			if(!empty($tax_classes)){
				foreach($tax_classes as $tax_class){
					$this->tax_amounts[$tax_class][$shipping_method_id.$selected_rate_id] = 0;
				}
			} else {
				// auto calculate zero rate for all customers outside of tax base
				$this->tax_amounts['jigoshop_zero_rate'][$shipping_method_id.$selected_rate_id] = 0;
			}
		}

		$this->imploded_tax_amounts = self::array_implode($this->tax_amounts);
	}

	/**
	 * @param $tax_class string Tax class to get tax for.
	 * @return float Tax value for current shipping and selected tax class.
	 */
	public function get_shipping_tax($tax_class)
	{
		if (isset($this->tax_amounts[$tax_class]) && isset($this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id])) {
			return $this->tax_amounts[$tax_class][jigoshop_session::instance()->chosen_shipping_method_id.jigoshop_session::instance()->selected_rate_id]/100;
		}

		return 0.0;
	}
}
