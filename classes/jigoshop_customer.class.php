<?php
/**
 * Customer Class
 *
 * The JigoShop customer class handles storage of the current customer's data, such as location.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Customer
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

class jigoshop_customer extends Jigoshop_Singleton {
	/** constructor */
	protected function __construct(){
		// update the customer if a user signs in
		$this->add_action('wp_login', 'update_signed_in_customer', 10, 2);

		// remove customer billing/shipping information
		$this->add_action('wp_logout', 'update_signed_out_customer');

		if (get_current_user_id() > 0) {
			$user = wp_get_current_user();
			$this->update_signed_in_customer('', $user);
		}

		// if we don't check the status of the customer, we will constantly destroy what the customer
		// has selected in their forms as pages get reloaded or refreshed.
		if(!isset(jigoshop_session::instance()->customer)){
			$this->set_default_customer();
		}
	}

	/**
	 * set the customer shipping and billing information from their saved data
	 *
	 * @param string $user_login - the user name of the customer logged in
	 * @param WP_User $user the user object from wp
	 * @since 1.4.4
	 */
	public function update_signed_in_customer($user_login, $user){
		$country = get_user_meta($user->ID, 'billing_country', true);
		$state = get_user_meta($user->ID, 'billing_state', true);
		$postcode = get_user_meta($user->ID, 'billing_postcode', true);
		$shipping_country = get_user_meta($user->ID, 'shipping_country', true);
		$shipping_state = get_user_meta($user->ID, 'shipping_state', true);
		$shipping_postcode = get_user_meta($user->ID, 'shipping_postcode', true);

		jigoshop_session::instance()->customer = array(
			'country' => $country,
			'state' => $state,
			'postcode' => $postcode,
			'shipping_country' => $shipping_country,
			'shipping_state' => $shipping_state,
			'shipping_postcode' => $shipping_postcode
		);
	}

	/**
	 * Provide a default value for the customer shipping/billing information. It
	 * utilizes the base country information to set up the default values.
	 *
	 * @since 1.4.4
	 */
	private function set_default_customer() {
		$country = jigoshop_countries::get_default_customer_country();
		$state = jigoshop_countries::get_default_customer_state();

		jigoshop_session::instance()->customer = array(
			'country'          => $country,
			'state'            => $state,
			'postcode'         => '',
			'shipping_country' => $country,
			'shipping_state'   => $state,
			'shipping_postcode'=> ''
		);
	}

	/**
	 * Find out if the customer should be taxed or not
	 *
	 * @param boolean $shippable tells if the cart has shippable items, and therefore we should use shipping country. Otherwise if not shippable items, we need to use the customer country.
	 * @return boolean true if customer is taxable, false otherwise
	 * @since 1.4
	 */
	public static function is_taxable($shippable){
		// if no taxes, than no one is taxable
		if(self::get_options()->get('jigoshop_calc_taxes') == 'no'){
			return false;
		}

		$shop_country = jigoshop_countries::get_base_country();
		$my_country = jigoshop_tax::get_customer_country();

		if(jigoshop_countries::is_eu_country($shop_country)){
			return jigoshop_countries::is_eu_country($my_country);
		}

		return ($shop_country == $my_country);
	}

	/**
	 * Is customer shipping outside base, but within the same country? This is
	 * used to determine how to apply taxes. Also, it no country is set, assume
	 * shipping is going to base country.
	 */
	public static function is_customer_outside_base($shippable){
		$outside = false;
		$country = ($shippable ? self::get_shipping_country() : self::get_country());

		// if no country is set, then assume customer is from the shop base
		if($country){
			$shop_country = jigoshop_countries::get_base_country();
			// check if it's a country with states.
			if(jigoshop_countries::country_has_states($country)){
				$shop_state = jigoshop_countries::get_base_state();

				// taxes only apply if the customer is shipping in the same country. If the customer is
				// shipping outside of the shop country, then taxes do not apply.
				if($shop_country === $country && $shop_state !== ($shippable ? self::get_shipping_state() : self::get_state())){
					$outside = true;
				}
			} else if(jigoshop_countries::is_eu_country($shop_country) && $shop_country != $country){
				// if both base country and shipping country are in the EU, then outside country base is true
				$outside = jigoshop_countries::is_eu_country($country);
			}
		}

		return $outside;
	}

	/** Gets the country from the current session */
	public static function get_shipping_country(){
		if(self::get_customer_session('shipping_country')){
			return self::get_customer_session('shipping_country');
		}

		return jigoshop_countries::get_default_customer_country();
	}

	private static function get_customer_session($name){
		return isset(jigoshop_session::instance()->customer[$name]) ? jigoshop_session::instance()->customer[$name] : null;
	}

	/** Gets the country from the current session */
	public static function get_country(){
		if(self::get_customer_session('country')){
			return self::get_customer_session('country');
		}

		return jigoshop_countries::get_default_customer_country();
	}

	/** Gets the state from the current session */
	public static function get_shipping_state(){
		if(self::get_customer_session('shipping_state')){
			return trim(self::get_customer_session('shipping_state'), ':');
		}

		return jigoshop_countries::get_default_customer_state();
	}

	/** Gets the state from the current session */
	public static function get_state(){
		if(self::get_customer_session('state')){
			return trim(self::get_customer_session('state'), ':');
		}

		return jigoshop_countries::get_default_customer_state();
	}

	/** Checks for a customer requiring a state for a country that has states */
	public static function has_valid_shipping_state() {
		return self::is_valid_shipping_state(self::get_shipping_country(), self::get_shipping_state()) || self::is_valid_shipping_state(self::get_country(), self::get_state());
	}

	protected static function is_valid_shipping_state($country, $state){
		if($country){
			$has_states = jigoshop_countries::country_has_states($country);

			if($state && $has_states){
				return jigoshop_countries::country_has_state($country, $state);
			} else if($has_states) {
				return false;
			}

			return true;
		}

		return false;
	}

	/** Gets the country and state from the current session for cart shipping display */
	public static function get_shipping_country_or_state(){
		$country = self::get_customer_session('shipping_country');
		if($country){
			$state = trim(self::get_customer_session('shipping_state'), ':');
			if($state && jigoshop_countries::country_has_states($country)){
				return jigoshop_countries::get_state($country, $state);
			} else {
				return jigoshop_countries::get_country($country);
			}
		}

		$country = jigoshop_countries::get_default_customer_country();
		return jigoshop_countries::get_country($country);
	}

	/** Sets session data for the location */
	public static function set_location($country, $state, $postcode = ''){
		self::set_country($country);
		self::set_state($state);
		if(!empty($postcode)){
			self::set_postcode($postcode);
		}
	}

	/** Sets session data for the country */
	public static function set_country($country){
		self::set_customer_session('country', $country);
	}

	/**
	 * Setting the customer session for country, postcode, and state
	 *
	 * @param string $name the index to set on the session array
	 * @param string $value postcode, country, or state
	 */
	private static function set_customer_session($name, $value){
		if(!is_array(jigoshop_session::instance()->customer)){
			jigoshop_session::instance()->customer = array();
		}
		$customer = jigoshop_session::instance()->customer;
		$customer[$name] = $value;
		jigoshop_session::instance()->customer = $customer;
	}

	/** Sets session data for the state */
	public static function set_state($state){
		self::set_customer_session('state', $state);
	}

	/** Sets session data for the postcode */
	public static function set_postcode($postcode){
		self::set_customer_session('postcode', $postcode);
	}

	/** Sets session data for the location */
	public static function set_shipping_location($country, $state = '', $postcode = ''){
		self::set_shipping_country($country);
		if(!empty($state)){
			self::set_shipping_state($state);
		}
		if(!empty($postcode)){
			self::set_shipping_postcode($postcode);
		}
	}

	/** Sets session data for the country */
	public static function set_shipping_country($country){
		self::set_customer_session('shipping_country', $country);
	}

	/** Sets session data for the state */
	public static function set_shipping_state($state){
		self::set_customer_session('shipping_state', $state);
	}

	/** Sets session data for the postcode */
	public static function set_shipping_postcode($postcode){
		self::set_customer_session('shipping_postcode', $postcode);
	}

	/**
	 * Gets a user's downloadable products if they are logged in
	 *
	 * @return array Array of downloadable products
	 */
	public static function get_downloadable_products(){
		global $wpdb;
		$downloads = array();

		if(is_user_logged_in()){
			$jigoshop_orders = new jigoshop_orders();
			$jigoshop_orders->get_customer_orders(get_current_user_id());
			if($jigoshop_orders->orders){
				$user_info = get_userdata(get_current_user_id());
				foreach($jigoshop_orders->orders as $order){
					if($order->status == 'completed'){
						$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}jigoshop_downloadable_product_permissions WHERE order_key = %s AND user_id = %d;", $order->order_key, get_current_user_id()));
						if($results){
							foreach($results as $result){
								$_product = new jigoshop_product_variation($result->product_id);
								$download_name = $_product->ID ? get_the_title($_product->ID) : get_the_title($result->product_id);

								if(isset($_product->variation_data)){
									$download_name .= ' ('.jigoshop_get_formatted_variation($_product, array(), true).')';
								}
								$downloads[] = array(
									'download_url' => add_query_arg('download_file', $result->product_id, add_query_arg('order', $result->order_key, add_query_arg('email', $user_info->user_email, home_url()))),
									'product_id' => $result->product_id,
									'download_name' => $download_name,
									'order_key' => $result->order_key,
									'downloads_remaining' => $result->downloads_remaining
								);
							}
						}
					}
				}
			}
		}

		return apply_filters('jigoshop_downloadable_products', $downloads);
	}

	/**
	 * Outputs a form field
	 *
	 * @param array $args contains a list of args for showing the field, merged with defaults (below)
	 * @return string
	 */
	public static function address_form_field($args){
		$defaults = array(
			'type' => 'text',
			'name' => '',
			'label' => '',
			'placeholder' => '',
			'required' => false,
			'class' => array(),
			'label_class' => array(),
			'rel' => '',
			'return' => false,
			'options' => array(),
			'value' => ''
		);

		$args = wp_parse_args($args, $defaults);
		if($args['required']){
			$required = ' <span class="required">*</span>';
			$input_required = ' input-required';
		} else {
			$required = '';
			$input_required = '';
		}

		if(in_array('form-row-last', $args['class'])){
			$after = '<div class="clear"></div>';
		} else {
			$after = '';
		}

		switch($args['type']){
			case "country" :
				$current_c = self::get_value($args['name']);
				$is_shipping_c = strpos($args['name'], 'shipping');
				if(!$current_c){
					if($is_shipping_c === false){
						$current_c = jigoshop_customer::get_country();
					} else {
						$current_c = jigoshop_customer::get_shipping_country();
					}
				}

				// Remove 'Select a Country' option from drop-down menu for countries.
				// There is no need to have it, because was assume when user hasn't selected
				// a country that they are from the shop base country.
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
        <label for="'.esc_attr($args['name']).'" class="'.esc_attr(implode(' ', $args['label_class'])).'">'.$args['label'].$required.'</label>
        <select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="country_to_state" rel="'.esc_attr($args['rel']).'">';

				foreach(jigoshop_countries::get_allowed_countries() as $key => $value){
					$field .= '<option value="'.esc_attr($key).'"';
					if(self::get_value($args['name']) == $key){
						$field .= ' selected="selected"';
					} elseif(self::get_value($args['name']) && $current_c == $key) {
						$field .= ' selected="selected"';
					}
					$field .= '>'.__($value, 'jigoshop').'</option>';
				}

				$field .= '</select></p>'.$after;
				break;
			case "state" :
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>';

				$is_shipping_s = strpos($args['name'], 'shipping');
				$current_cc = self::get_value($args['rel']);
				if(!$current_cc){
					if($is_shipping_s === false){
						$current_cc = jigoshop_customer::get_country();
					} else {
						$current_cc = jigoshop_customer::get_shipping_country();
					}
				}

				$current_r = self::get_value($args['name']);
				if(!$current_r){
					if($is_shipping_s === false){
						$current_r = jigoshop_customer::get_state();
					} else {
						$current_r = jigoshop_customer::get_shipping_state();
					}
				}

				$states = jigoshop_countries::get_states($current_cc);

				if(!empty($states)){
					// Dropdown
					$field .= '<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="'.esc_attr($input_required).'"><option value="">'.__('Select a state&hellip;', 'jigoshop').'</option>';
					foreach($states as $key => $value){
						$field .= '<option value="'.esc_attr($key).'"';
						if($current_r == $key){
							$field .= ' selected="selected"';
						}
						$field .= '>'.__($value, 'jigoshop').'</option>';
					}
					$field .= '</select>';
				} else {
					// Input
					$field .= '<input type="text" class="input-text" value="'.esc_attr($current_r).'" placeholder="'.__('State/Province', 'jigoshop').'" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" />';
				}

				$field .= '</p>'.$after;
				break;
			case "postcode" :
				$current_pc = self::get_value($args['name']);
				$is_shipping_pc = strpos($args['name'], 'shipping');
				if(!$current_pc){
					if($is_shipping_pc === false){
						$current_pc = jigoshop_customer::get_postcode();
					} else {
						$current_pc = jigoshop_customer::get_shipping_postcode();
					}
				}
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<input type="text" class="input-text" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" value="'.esc_attr($current_pc).'" />
				</p>'.$after;
				break;
			case "textarea" :
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<textarea name="'.esc_attr($args['name']).'" class="input-text'.esc_attr($input_required).'" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" cols="5" rows="2">'.esc_textarea(self::get_value($args['name'])).'</textarea>
				</p>'.$after;
				break;
			//Adds a drop down custom type
			case "select":
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
						  <label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>';
				$field .= '<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="'.esc_attr($input_required).'">';
				foreach($args['options'] as $value => $label){
					$field .= '<option value="'.esc_attr($value).'"';
					if(self::get_value($args['name']) == $value){
						$field .= ' selected="selected"';
					}
					$field .= '>'.__($label, 'jigoshop').'</option>';
				};
				$field .= '</select></p>'.$after;
				break;
			default :
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<input type="'.$args['type'].'" class="input-text'.esc_attr($input_required).'" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" value="'.self::get_value($args['name']).'" />
				</p>'.$after;

				break;
		}

		$field = apply_filters('jigoshop_address_field_types', $field, $args);

		if($args['return']){
			return $field;
		} else {
			echo $field;

			return null;
		}
	}

	/** Gets the value either from the posted data, or from the users meta data */
	public static function get_value($input){
		if(isset($_POST[$input]) && !empty($_POST[$input])){
			return $_POST[$input];
		} else if(is_user_logged_in()){
			if(get_user_meta(get_current_user_id(), $input, true)){
				return get_user_meta(get_current_user_id(), $input, true);
			}

			$current_user = wp_get_current_user();

			switch($input){
				case "billing_email" :
					return $current_user->user_email;
					break;
			}
		}

		return '';
	}

	/** Gets the postcode from the current session */
	public static function get_postcode(){
		if(self::get_customer_session('postcode')){
			return strtolower(str_replace(' ', '', self::get_customer_session('postcode')));
		}

		return '';
	}

	/** Gets the postcode from the current session */
	public static function get_shipping_postcode() {
		if (self::get_customer_session('shipping_postcode'))
		{
			return strtolower(str_replace(' ', '', self::get_customer_session('shipping_postcode')));
		}
		return '';
	}

	/**
	 * remove the logged out user shipping information from the session once they log out
	 *
	 * @since 1.4.4
	 */
	public function update_signed_out_customer(){
		unset(jigoshop_session::instance()->customer);
		$this->set_default_customer();
	}
}
