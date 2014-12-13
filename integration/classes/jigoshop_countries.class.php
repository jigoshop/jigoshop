<?php

use Jigoshop\Helper\Country;

class jigoshop_countries extends \Jigoshop_Base {
	public static $countries;
	public static $states;
	public static $european_union_countries;

	public static function __init()
	{
		self::$countries = Country::getAll();
		self::$states = Country::getAllStates();
		self::$european_union_countries = arrayy();
		foreach (self::$countries as $code => $country) {
			if (Country::isEU($code)) {
				self::$european_union_countries[$code] = $country;
			}
		}
	}

	public static function country_has_states($country_code){
		return Country::hasStates($country_code);
	}

	public static function is_eu_country($country_code){
		return Country::isEU($country_code);
	}

	public static function get_base_country(){
		return Integration::getOptions()->get('general.country');
//		$default = self::get_options()->get('jigoshop_default_country');
//		$country = explode(':', $default);
//
//		return $country[0];
	}

	public static function get_base_state(){
		// TODO: Support for setting base state
		return Integration::getOptions()->get('general.country');
//		$default = self::get_options()->get('jigoshop_default_country');
//		$country = explode(':', $default);
//
//		return count($country) == 2 ? $country[1] : '';
	}

	public static function get_default_customer_country(){
		// TODO: Add support for default customer country
	}

	public static function get_default_customer_state(){
		// TODO: Add support for default customer state
	}

	public static function get_countries(){
		return Country::getAll();
	}

	public static function get_allowed_countries(){
		return Country::getAllowed();
	}

	public static function get_states($country_code){
		return Country::getStates($country_code);
	}

	public static function has_country($country_code){
		return Country::exists($country_code);
	}

	public static function get_country($country_code){
		return Country::getName($country_code);
	}

	public static function has_state($country_code, $state_code){
		return Country::hasState($country_code, $state_code);
	}

	public static function get_state($country_code, $state_code){
		return Country::getStateName($country_code, $state_code);
	}

	public static function country_has_state($country_code, $state_code){
		return Country::hasState($country_code, $state_code);
	}

	// Outputs the list of countries and states for use in dropdown boxes
	public static function country_dropdown_options(
		$selected_country = '',
		$selected_state = null,
		$escape = true,
		$show_all = true,
		$echo = true,
		$add_empty = false
	){
		// TODO: Rework this options (into helper probably)
		$output = '';
		$countries = self::get_countries();

		if($selected_state === null){
			$selected_state = '*';
		}

		if(is_array($selected_country)){
			$selected_country = array_unique($selected_country);
		}
		if(is_array($selected_state)){
			$selected_state = array_unique($selected_state);
		}

		if($add_empty){
			$output .= '<option value="-1">'.__('None', 'jigoshop').'</option>';
		}

		if($countries){
			foreach($countries as $country_key => $country_value){
				$country_value = $escape ? esc_js($country_value) : $country_value;

				if(($states = self::get_states($country_key))){
					$output .= '<optgroup label="'.$country_value.'">';

					if($show_all){
						if(!is_array($selected_country) || !in_array($country_key, $selected_country)){
							$output .= '<option value="'.esc_attr($country_key).'"';
							if($selected_country == $country_key && $selected_state == '*'){
								$output .= ' selected="selected"';
							}
							$output .= '>'.__('All of', 'jigoshop').' '.$country_value.'</option>';
						}
					}

					foreach($states as $state_key => $state_value){
						$is_selected = (is_array($selected_state) && in_array($state_key, $selected_state) && in_array($country_key, $selected_country)) ||
							(($selected_country == $country_key && $selected_state == $state_key) || (!$show_all && ($selected_state == '*' && $selected_country == $country_key)));

						$output .= '<option value="'.esc_attr($country_key).':'.esc_attr($state_key).'"';
						if($is_selected){
							$output .= ' selected="selected"';
						}
						$output .= '>'.$country_value.' &mdash; '.($escape ? esc_js($state_value) : $state_value).'</option>';
					}

					$output .= '</optgroup>';

					// Will only run update_option once
					// If the state is '*' , update the default country to the last state in the selected country
					if(!$show_all && ($selected_state == '*' && $selected_country == $country_key)){
						$state_keys = array_keys($states);
						self::get_options()->set('jigoshop_default_country', $country_key.':'.end($state_keys));
					}
				} else {
					$is_selected = (is_array($selected_country) && in_array($country_key, $selected_country)) || ($selected_country == $country_key);
					$output .= '<option';
					if($is_selected){
						$output .= ' selected="selected"';
					}
					$output .= ' value="'.esc_attr($country_key).'">'.__($country_value, 'jigoshop').'</option>';
				}
			}
		}

		if($echo){
			echo $output;
		}

		return $output;
	}

	public static function get_all_states()
	{
		return Country::getAllStates();
	}
}
