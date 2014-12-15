<?php

use Jigoshop\Helper\Country;
use Jigoshop\Integration;

class jigoshop_customer {
	private static $instance;

	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function reset()
	{
		self::$instance = null;
	}

	public function __clone()
	{
		trigger_error("Cloning Singleton's is not allowed.", E_USER_ERROR);
	}

	public function __wakeup()
	{
		trigger_error("Unserializing Singleton's is not allowed.", E_USER_ERROR);
	}

	protected function __construct(){
		// update the customer if a user signs in
		add_action('wp_login', array($this, 'update_signed_in_customer'), 10, 2);

		// remove customer billing/shipping information
		add_action('wp_logout', array($this, 'update_signed_out_customer'));

		if (get_current_user_id() > 0) {
			$user = wp_get_current_user();
			$this->update_signed_in_customer('', $user);
		}
	}

	public function update_signed_out_customer(){
		// Do nothing, Jigoshop handles this
	}

	/**
	 * set the customer shipping and billing information from their saved data
	 *
	 * @param string $user_login - the user name of the customer logged in
	 * @param WP_User $user the user object from wp
	 * @since 1.4.4
	 */
	public function update_signed_in_customer($user_login, $user){
		// Load old user data
		// TODO: How to apply this to new systems using old plugins?
		$country = get_user_meta($user->ID, 'billing_country', true);
		$state = get_user_meta($user->ID, 'billing_state', true);
		$postcode = get_user_meta($user->ID, 'billing_postcode', true);
		$shipping_country = get_user_meta($user->ID, 'shipping_country', true);
		$shipping_state = get_user_meta($user->ID, 'shipping_state', true);
		$shipping_postcode = get_user_meta($user->ID, 'shipping_postcode', true);

		$cart = Jigoshop\Integration::getCart();
		$customer = $cart->getCustomer();

		$customer->getBillingAddress()->setCountry($country);
		$customer->getBillingAddress()->setState($state);
		$customer->getBillingAddress()->setPostcode($postcode);

		$customer->getShippingAddress()->setCountry($shipping_country);
		$customer->getShippingAddress()->setState($shipping_state);
		$customer->getShippingAddress()->setPostcode($shipping_postcode);
	}

	public static function is_taxable($_shippable){
		$cart = Integration::getCart();
		return Integration::getCustomerService()->isTaxable($cart->getCustomer());
	}

	/**
	 * Is customer shipping outside base, but within the same country? This is
	 * used to determine how to apply taxes. Also, it no country is set, assume
	 * shipping is going to base country.
	 */
	public static function is_customer_outside_base($shippable){
		// TODO
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

	public static function get_country(){
		return Integration::getCustomerService()->getCurrent()->getBillingAddress()->getCountry();
	}

	public static function get_state(){
		return Integration::getCustomerService()->getCurrent()->getBillingAddress()->getState();
	}

	public static function get_postcode(){
		return Integration::getCustomerService()->getCurrent()->getBillingAddress()->getPostcode();
	}

	public static function get_shipping_country(){
		return Integration::getCustomerService()->getCurrent()->getShippingAddress()->getCountry();
	}

	public static function get_shipping_state(){
		return Integration::getCustomerService()->getCurrent()->getShippingAddress()->getState();
	}

	public static function get_shipping_postcode() {
		return Integration::getCustomerService()->getCurrent()->getShippingAddress()->getPostcode();
	}

	protected static function is_valid_shipping_state($country, $state){
		if (Country::exists($country)) {
			$has_states = Country::hasStates($country);

			if($state && $has_states){
				return Country::hasState($country, $state);
			} else if($has_states) {
				return false;
			}

			return true;
		}

		return false;
	}

	public static function has_valid_shipping_state() {
		return self::is_valid_shipping_state(self::get_shipping_country(), self::get_shipping_state()) || self::is_valid_shipping_state(self::get_country(), self::get_state());
	}

	public static function get_shipping_country_or_state(){
		return Integration::getCustomerService()->getCurrent()->getShippingAddress()->getLocation();
	}

	public static function set_location($country, $state, $postcode = ''){
		$customer = Jigoshop\Integration::getCustomerService()->getCurrent();
		$address = $customer->getBillingAddress();
		$address->setCountry($country);
		$address->setState($state);
		$address->setPostcode($postcode);

		Integration::getCustomerService()->save($customer);
	}

	public static function set_shipping_location($country, $state = '', $postcode = ''){
		$customer = Jigoshop\Integration::getCustomerService()->getCurrent();
		$address = $customer->getShippingAddress();
		$address->setCountry($country);
		$address->setState($state);
		$address->setPostcode($postcode);

		Integration::getCustomerService()->save($customer);
	}

	public static function set_country($country){
		$customer = Jigoshop\Integration::getCustomerService()->getCurrent();
		$customer->getBillingAddress()->setCountry($country);
		Integration::getCustomerService()->save($customer);
	}

	public static function set_state($state){
		$customer = Jigoshop\Integration::getCustomerService()->getCurrent();
		$customer->getBillingAddress()->setState($state);
		Integration::getCustomerService()->save($customer);
	}

	public static function set_postcode($postcode){
		$customer = Jigoshop\Integration::getCustomerService()->getCurrent();
		$customer->getBillingAddress()->setPostcode($postcode);
		Integration::getCustomerService()->save($customer);
	}

	public static function set_shipping_country($country){
		$customer = Jigoshop\Integration::getCustomerService()->getCurrent();
		$customer->getShippingAddress()->setCountry($country);
		Integration::getCustomerService()->save($customer);
	}

	public static function set_shipping_state($state){
		$customer = Jigoshop\Integration::getCustomerService()->getCurrent();
		$customer->getShippingAddress()->setState($state);
		Integration::getCustomerService()->save($customer);
	}

	public static function set_shipping_postcode($postcode){
		$customer = Jigoshop\Integration::getCustomerService()->getCurrent();
		$customer->getShippingAddress()->setPostcode($postcode);
		Integration::getCustomerService()->save($customer);
	}

	/**
	 * Gets a user's downloadable products if they are logged in
	 *
	 * @return array Array of downloadable products
	 */
	public static function get_downloadable_products(){
		// TODO
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
		// TODO
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
						$field .= 'selected="selected"';
					} elseif(self::get_value($args['name']) && $current_c == $key) {
						$field .= 'selected="selected"';
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
						$field .= 'selected="selected"';
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
		// TODO: Any idea how to back this up with new Customer entity?
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
}
