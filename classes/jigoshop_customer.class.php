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
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

class jigoshop_customer extends Jigoshop_Singleton {

	/** constructor */
	protected function __construct() {

		// update the customer if a user signs in
		$this->add_action('wp_login', 'update_signed_in_customer', 10, 2);

		// remove customer billing/shipping information
		$this->add_action('wp_logout', 'update_signed_out_customer');

		// if we don't check the status of the customer, we will constantly destroy what the customer
		// has selected in their forms as pages get reloaded or refreshed.
		if ( !isset( jigoshop_session::instance()->customer ) ) {
			$this->set_default_customer();
		}
	}

	/**
	 * Provide a default value for the customer shipping/billing information. It
	 * utilizes the base country information to set up the default values.
	 *
	 * @since 1.4.4
	 */
	private function set_default_customer() {

		$default = self::get_options()->get_option('jigoshop_default_country');
		if (strstr($default, ':')) {
			$dcountry = current(explode(':', $default));
			$dstate = end(explode(':', $default));
		}
		else {
			$dcountry = $default;
			$dstate = '';
		}

		jigoshop_session::instance()->customer = array(
			'country'          => $dcountry,
			'state'            => $dstate,
			'postcode'         => '',
			'shipping_country' => $dcountry,
			'shipping_state'   => $dstate,
			'shipping_postcode'=> ''
		);

	}

	/**
	 * remove the logged out user shipping information from the session once they log out
	 *
	 * @since 1.4.4
	 */
	public function update_signed_out_customer() {
		unset(jigoshop_session::instance()->customer);
		$this->set_default_customer();
	}

	/**
	 * set the customer shipping and billing information from their saved data
	 *
	 * @param string $user_login - the user name of the customer logged in
	 * @param user $user the user object from wp
	 * @since 1.4.4
	 */
	public function update_signed_in_customer($user_login, $user) {

		$country = get_user_meta( $user->ID, 'billing-country', true );
		$state = get_user_meta( $user->ID, 'billing-state', true );
		$postcode = get_user_meta( $user->ID, 'billing-postcode', true );
		$shipping_country = get_user_meta( $user->ID, 'shipping-country', true );
		$shipping_state    = get_user_meta( $user->ID, 'shipping-state', true );
		$shipping_postcode = get_user_meta( $user->ID, 'shipping-postcode', true );

		jigoshop_session::instance()->customer = array(
			'country'          => $country,
			'state'            => $state,
			'postcode'         => $postcode,
			'shipping_country' => $shipping_country,
			'shipping_state'   => $shipping_state,
			'shipping_postcode'=> $shipping_postcode
		);

	}

    /**
     * Find out if the customer should be taxed or not
     * @param boolean $shipable tells if the cart has shipable items, and therefore we should use
     * shipping country. Otherwise if not shippable items, we need to use the customer country.
     * @return boolean true if customer is taxable, false otherwise
     * @since 1.4
     */
	public static function is_taxable($shipable) {
		// if no taxes, than no one is taxable
		if (self::get_options()->get_option('jigoshop_calc_taxes') == 'no') {
			return false;
		}

		$shop_country = jigoshop_countries::get_base_country();
		$my_country = ($shipable ? self::get_shipping_country() : self::get_country());

		$taxable = false;
		if (jigoshop_countries::is_eu_country($shop_country)) {
			$taxable = jigoshop_countries::is_eu_country($my_country);
		}
		else {
			$taxable = ($shop_country == $my_country);
		}
		// in order for a customer to be taxed, they have to be shipping to the taxing country
		// or belong to that country. Also, if the shop is eu shop, then
		return $taxable;
	}

    /**
     * Is customer shipping outside base, but within the same country? This is
     * used to determine how to apply taxes. Also, it no country is set, assume
     * shipping is going to base country.
     */
	public static function is_customer_outside_base($shipable) {
        $outside = false;
        $country = ($shipable ? self::get_shipping_country() : self::get_country());

        // if no country is set, then assume customer is from the shop base
		if ( $country ) :

            $shopcountry = jigoshop_countries::get_base_country();
            // check if it's a country with states.
            if (jigoshop_countries::country_has_states($country)) :

                $shopstate = jigoshop_countries::get_base_state();

                // taxes only apply if the customer is shipping in the same country. If the customer is
                // shipping outside of the shop country, then taxes do not apply.
                if ( $shopcountry === $country && $shopstate !== ($shipable ? self::get_shipping_state() : self::get_state())) :
                    $outside = true;
                endif;
            elseif (jigoshop_countries::is_eu_country($shopcountry) && $shopcountry != $country) :

                // if both base country and shipping country are in the EU, then outside country base is true
                $outside = jigoshop_countries::is_eu_country($country);
            endif;
		endif;
		return $outside;
	}

	/** Gets the state from the current session */
	public static function get_state() {
		if (self::get_customer_session('state')) return self::get_customer_session('state');
	}

	/** Gets the country from the current session */
	public static function get_country() {
		if (self::get_customer_session('country')) return self::get_customer_session('country');
	}

	/** Gets the postcode from the current session */
	public static function get_postcode() {
		if ( self::get_customer_session('postcode')) return strtolower(str_replace(' ', '', self::get_customer_session('postcode')));
	}

	/** Checks for a customer requiring a state for a country that has states */
	public static function has_valid_shipping_state() {
		if ( self::get_customer_session('shipping_country')) {
			if ( self::get_customer_session('shipping_state')) {
				$states = jigoshop_countries::get_states( self::get_customer_session('shipping_country') );
				if ( empty( $states ) ) {
					return true;    /* there are no states for this country, states are not required */
				} else if ( isset( $states[self::get_customer_session('shipping_state')] ) ) {
					return false;    /* there are states and this one is valid, states are required */
				} else {
					return false;   /* there are states and no match from the session, states are required */
				}
			} else if ( jigoshop_countries::country_has_states( self::get_customer_session('shipping_country') ) ) {
				return false;       /* states are required */
			} else {
				return true;        /* no session for state, no states for country, states are not required */
			}
		}
		if ( self::get_customer_session('country'))	{
			if ( self::get_customer_session('state')) {
				$states = jigoshop_countries::get_states( self::get_customer_session('country') );
				if ( empty( $states ) ) {
					return true;    /* there are no states for this country, states are not required */
				} else if ( isset( $states[self::get_customer_session('state')] ) ) {
					return false;    /* there are states and this one is valid, states are required */
				} else {
					return false;   /* there are states and no match from the session, states are required */
				}
			} elseif ( jigoshop_countries::country_has_states( self::get_customer_session('country') ) ) {
				return false;       /* states are required */
			} else {
				return true;        /* no session for state, no states for country, states are not required */
			}
		}
		return false;               /* default to states are required */
	}

	/** Gets the state from the current session */
	public static function get_shipping_state() {
		if (self::get_customer_session('shipping_state')) return self::get_customer_session('shipping_state');
	}

	/** Gets the country from the current session */
	public static function get_shipping_country() {
		if (self::get_customer_session('shipping_country'))	return self::get_customer_session('shipping_country');
	}

	/** Gets the country and state from the current session for cart shipping display */
	public static function get_shipping_country_or_state() {
		if (self::get_customer_session('shipping_country'))	{
			if (self::get_customer_session('shipping_state')) {
				return isset(jigoshop_countries::$states[self::get_customer_session('shipping_country')][self::get_customer_session('shipping_state')]) ? jigoshop_countries::$states[self::get_customer_session('shipping_country')][self::get_customer_session('shipping_state')] : '';
			} else {
				return jigoshop_countries::$countries[self::get_customer_session('shipping_country')];
			}
		}
	}

	/** Gets the postcode from the current session */
	public static function get_shipping_postcode() {
        if (self::get_customer_session('shipping_postcode')) return strtolower(str_replace(' ', '', self::get_customer_session('shipping_postcode')));
	}

	/** Sets session data for the location */
	public static function set_location( $country, $state, $postcode = '' ) {
		$data = (array) jigoshop_session::instance()->customer;
		$data['country'] = $country;
		$data['state'] = $state;
		$data['postcode'] = $postcode;
		jigoshop_session::instance()->customer = $data;
	}

	/** Sets session data for the country */
	public static function set_country( $country ) {
        self::set_customer_session('country', $country);
	}

	/** Sets session data for the state */
	public static function set_state( $state ) {
        self::set_customer_session('state', $state);
	}

	/** Sets session data for the postcode */
	public static function set_postcode( $postcode ) {
        self::set_customer_session('postcode', $postcode);
	}

	/** Sets session data for the location */
	public static function set_shipping_location( $country, $state = '', $postcode = '' ) {
		$data = (array) jigoshop_session::instance()->customer;
		$data['shipping_country'] = $country;
		$data['shipping_state'] = $state;
		$data['shipping_postcode'] = $postcode;
		jigoshop_session::instance()->customer = $data;
	}

	/** Sets session data for the country */
	public static function set_shipping_country( $country ) {
        self::set_customer_session('shipping_country', $country);
	}

	/** Sets session data for the state */
	public static function set_shipping_state( $state ) {
        self::set_customer_session('shipping_state', $state);
	}

	/** Sets session data for the postcode */
	public static function set_shipping_postcode( $postcode ) {
        self::set_customer_session('shipping_postcode', $postcode);
	}

    /**
     * Setting the customer session for country, postcode, and state
     * @param string $array_index the index to set on the session array
     * @param string $value postcode, country, or state
     */
    private static function set_customer_session($array_index, $value) {
        $customer = (array) jigoshop_session::instance()->customer;
        $customer[$array_index] = $value;
        jigoshop_session::instance()->customer = $customer;
    }

    private static function get_customer_session($array_index) {
        $customer = (array) jigoshop_session::instance()->customer;
        return $customer[$array_index];
    }

	/**
	 * Gets a user's downloadable products if they are logged in
	 *
	 * @return   array	downloads	Array of downloadable products
	 */
	public static function get_downloadable_products() {

		global $wpdb;

		$downloads = array();

		if (is_user_logged_in()) :

			$jigoshop_orders = new jigoshop_orders();
			$jigoshop_orders->get_customer_orders( get_current_user_id() );
			if ($jigoshop_orders->orders) foreach ($jigoshop_orders->orders as $order) :
				if ( $order->status == 'completed' ) {
					$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."jigoshop_downloadable_product_permissions WHERE order_key = %s AND user_id = %d;", $order->order_key, get_current_user_id() ) );
					$user_info = get_userdata(get_current_user_id());
					if ($results) foreach ($results as $result) :
							$_product = new jigoshop_product_variation( $result->product_id );
							$download_name = $_product->ID ? get_the_title($_product->ID) : get_the_title($result->product_id);

							if (isset($_product->variation_data)) :
								$download_name = $download_name .' (' . jigoshop_get_formatted_variation( $_product->variation_data, true ).')';
							endif;
							$downloads[] = array(
								'download_url'       => add_query_arg('download_file', $result->product_id, add_query_arg('order', $result->order_key, add_query_arg('email', $user_info->user_email, home_url()))),
								'product_id'         => $result->product_id,
								'download_name'      => $download_name,
								'order_key'          => $result->order_key,
								'downloads_remaining'=> $result->downloads_remaining
							);
					endforeach;
				}
			endforeach;

		endif;

		return apply_filters( 'jigoshop_downloadable_products', $downloads );

	}

	public function address_form($load_address, $fields) {

		$title = '<h3>';
		if($load_address=='billing'):
			$title .= __('Billing Address', 'jigoshop');
		else:
			$title .= __('Shipping Address', 'jigoshop');
		endif;
		$title .='</h3>';
		echo $title;
		// Billing Details
		foreach ($fields as $field) :
			self::address_form_field( $field );
		endforeach;

	}

	/**
	 * Outputs a form field
	 *
	 * @param   array	args	contains a list of args for showing the field, merged with defaults (below)
	 */
	function address_form_field( $args ) {

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
			'options'=> array(),
			'value'	=>''
		);

		$args = wp_parse_args( $args, $defaults );

		if ($args['required']) {
			$required = ' <span class="required">*</span>';
			$input_required = ' input-required';
		} else {
			$required = '';
			$input_required = '';
		}

		if (in_array('form-row-last', $args['class'])) {
			$after = '<div class="clear"></div>';
		} else {
			$after = '';
		}

		$field = '';

		switch ($args['type']) :
			case "country" :

				$current_c = self::get_value($args['name']);
				$is_shipping_c = strpos($args['name'], 'shipping');
				if (!$current_c) :
					if ($is_shipping_c === false) $current_c = jigoshop_customer::get_country();
					else $current_c = jigoshop_customer::get_shipping_country();
				endif;

                // Remove 'Select a Country' option from drop-down menu for countries.
                // There is no need to have it, because was assume when user hasn't selected
                // a country that they are from the shop base country.
                $field = '<p class="form-row '.implode(' ', $args['class']).'">
                <label for="'.esc_attr($args['name']).'" class="'.esc_attr(implode(' ', $args['label_class'])).'">'.$args['label'].$required.'</label>
                <select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="country_to_state" rel="'.esc_attr($args['rel']).'">';

				foreach (jigoshop_countries::get_allowed_countries() as $key=>$value) :
					$field .= '<option value="'.esc_attr($key).'"';
					if (self::get_value($args['name'])==$key) $field .= 'selected="selected"';
					elseif (self::get_value($args['name']) && $current_c==$key) $field .= 'selected="selected"';
					$field .= '>'.__($value, 'jigoshop').'</option>';
				endforeach;

				$field .= '</select></p>'.$after;

			break;
			case "state" :
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="' . esc_attr( $args['name'] ) . '" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>';

				$is_shipping_s = strpos($args['name'], 'shipping');
				$current_cc = self::get_value($args['rel']);
				if (!$current_cc) :
					if ($is_shipping_s === false) $current_cc = jigoshop_customer::get_country();
					else $current_cc = jigoshop_customer::get_shipping_country();
				endif;

				$current_r = self::get_value($args['name']);
				if (!$current_r) :
					if ($is_shipping_s === false) $current_r = jigoshop_customer::get_state();
					else $current_r = jigoshop_customer::get_shipping_state();
				endif;

				$states = jigoshop_countries::get_states( $current_cc );

				if (!empty( $states )) :
					// Dropdown
					$field .= '<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="'.esc_attr($input_required).'"><option value="">'.__('Select a state&hellip;', 'jigoshop').'</option>';
					foreach ($states as $key=>$value) :
						$field .= '<option value="'.esc_attr($key).'"';
						if ($current_r==$key) $field .= ' selected="selected"';
						$field .= '>'.__($value, 'jigoshop').'</option>';
					endforeach;
					$field .= '</select>';
				else :
					// Input
					$field .= '<input type="text" class="input-text" value="'.esc_attr($current_r).'" placeholder="'.__('State/County', 'jigoshop').'" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" />';
				endif;

				$field .= '</p>'.$after;

			break;
			case "postcode" :
				$current_pc = self::get_value($args['name']);
				$is_shipping_pc = strpos($args['name'], 'shipping');
				if (!$current_pc) :
					if ($is_shipping_pc === false) $current_pc = jigoshop_customer::get_postcode();
					else $current_pc = jigoshop_customer::get_shipping_postcode();
				endif;

				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="' . esc_attr( $args['name'] ) . '" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<input type="text" class="input-text" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" value="' . esc_attr( $current_pc ) . '" />
				</p>'.$after;
			break;
			case "textarea" :

				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="' . esc_attr( $args['name'] ) . '" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<textarea name="'.esc_attr($args['name']).'" class="input-text' . esc_attr( $input_required ) . '" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" cols="5" rows="2">'. esc_textarea(self::get_value( $args['name'] ) ).'</textarea>
				</p>'.$after;

			break;
			//Adds a drop down custom type
			case "select":
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
						  <label for="' . esc_attr( $args['name'] ) . '" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>';
				$field .= '<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="'.esc_attr($input_required).'">';
				foreach($option as $value=>$label){
					$field .= '<option value="'.esc_attr($value).'"';
					if (self::get_value($args['name'])==$value) $field .= 'selected="selected"';
					$field .= '>'.__($label, 'jigoshop').'</option>';
				};
				'</select></p>'.$after;
			break;
			default :

				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="' . esc_attr( $args['name'] ) . '" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<input type="'.$args['type'].'" class="input-text' . esc_attr( $input_required ) . '" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" value="'. self::get_value( $args['name'] ).'" />
				</p>'.$after;

			break;
		endswitch;

		$field = apply_filters('jigoshop_address_field_types', $field, $args);

		if ($args['return']) return $field; else echo $field;
	}
	/** Gets the value either from the posted data, or from the users meta data */
	function get_value( $input ) {
		if (isset( $_POST[$input] ) && !empty($_POST[$input])) :
			return $_POST[$input];
		elseif (is_user_logged_in()) :
			if (get_user_meta( get_current_user_id(), $input, true )) return get_user_meta( get_current_user_id(), $input, true );

			$current_user = wp_get_current_user();

			switch ( $input ) :

				case "billing-email" :
					return $current_user->user_email;
				break;

			endswitch;
		endif;
	}

}
