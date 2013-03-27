<?php
/**
 * Checkout Class
 *
 * The JigoShop checkout class handles the checkout process, collecting user data and processing the payment.
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
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class jigoshop_checkout extends Jigoshop_Singleton {

	public $posted;
	public $billing_fields;
	public $shipping_fields;
	private $must_register = true;
	private $show_signup = false;
	private $valid_euvatno = false;
	
	/** constructor */
	protected function __construct () {

		$this->must_register = ( self::get_options()->get_option('jigoshop_enable_guest_checkout') != 'yes' && !is_user_logged_in() );
		$this->show_signup = ( self::get_options()->get_option('jigoshop_enable_signup_form') == 'yes' && !is_user_logged_in() );

		add_action('jigoshop_checkout_billing',array(&$this,'checkout_form_billing'));
		add_action('jigoshop_checkout_shipping',array(&$this,'checkout_form_shipping'));

		$this->billing_fields = self::get_billing_fields();
		$this->billing_fields = apply_filters( 'jigoshop_billing_fields', $this->billing_fields );

		$this->shipping_fields = self::get_shipping_fields();
		$this->shipping_fields = apply_filters( 'jigoshop_shipping_fields', $this->shipping_fields );
	}

	public static function get_billing_fields() {
		$billing_fields = array(
			array(
				'name'          => 'billing-country',
				'type'          => 'country',
				'label'         => __('Country', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-first'),
				'rel'           => 'billing-state' ),
			array(
				'name'          => 'billing-state',
				'type'          => 'state',
				'label'         => __('State/County', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-last'),
				'rel'           => 'billing-country' ),
			array(
				'name'          => 'billing-first_name',
				'label'         => __('First Name', 'jigoshop'),
				'placeholder'   => __('First Name', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-first') ),
			array(
				'name'          => 'billing-last_name',
				'label'         => __('Last Name', 'jigoshop'),
				'placeholder'   => __('Last Name', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-last') ),
			array(
				'name'          => 'billing-company',
				'label'         => __('Company', 'jigoshop'),
				'placeholder'   => __('Company', 'jigoshop') ),
			array(
				'name'          => 'billing-address',
				'label'         => __('Address', 'jigoshop'),
				'placeholder'   => __('Address 1', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-first') ),
			array(
				'name'          => 'billing-address-2',
				'label'         => __('Address 2', 'jigoshop'),
				'placeholder'   => __('Address 2', 'jigoshop'),
				'class'         => array('form-row-last'),
				'label_class'   => array('hidden') ),
			array(
				'name'          => 'billing-city',
				'label'         => __('City', 'jigoshop'),
				'placeholder'   => __('City', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-first') ),
			array(
				'name'          =>'billing-postcode',
				'type'          => 'postcode',
				'validate'      => 'postcode',
				'format'        => 'postcode',
				'label'         => __('Postcode', 'jigoshop'),
				'placeholder'   => __('Postcode', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-last') ),
			array(
				'name'          => 'billing-email',
				'validate'      => 'email',
				'label'         => __('Email Address', 'jigoshop'),
				'placeholder'   => __('you@yourdomain.com', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-first') ),
			array(
				'name'          => 'billing-phone',
				'validate'      => 'phone',
				'label'         => __('Phone', 'jigoshop'),
				'placeholder'   => __('Phone number', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-last') )
		);

		if ( jigoshop_countries::is_eu_country( jigoshop_countries::get_base_country() ) ) {
			$start = $billing_fields;
			array_splice( $start, 5 );
			$end = $billing_fields;
			array_splice( $end, 0, 5 );
			$billing_fields = array_merge(
				$start,
				array( array(
					'name'          => 'billing-euvatno',
					'label'         => __('EU VAT Number', 'jigoshop'),
					'placeholder'   => __('EU VAT Number', 'jigoshop'),
					/*'class'         => array('form-row-last')*/
				)),
				$end
			);
		}

		return $billing_fields;
	}

	public static function get_shipping_fields() {
		$shipping_fields = array(
			array(
				'name'          =>'shipping-country',
				'type'          => 'country',
				'label'         => __('Country', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-first'),
				'rel'           => 'shipping-state' ),
			array(
				'name'          => 'shipping-state',
				'type'          => 'state',
				'label'         => __('State/County', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-last'),
				'rel'           => 'shipping-country' ),
			array(
				'name'          => 'shipping-first_name',
				'label'         => __('First Name', 'jigoshop'),
				'placeholder'   => __('First Name', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-first') ),
			array(
				'name'          => 'shipping-last_name',
				'label'         => __('Last Name', 'jigoshop'),
				'placeholder'   => __('Last Name', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-last') ),
			array(
				'name'          => 'shipping-company',
				'label'         => __('Company', 'jigoshop'),
				'placeholder'   => __('Company', 'jigoshop') ),
			array(
				'name'          => 'shipping-address',
				'label'         => __('Address', 'jigoshop'),
				'placeholder'   => __('Address 1', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-first') ),
			array(
				'name'          => 'shipping-address-2',
				'label'         => __('Address 2', 'jigoshop'),
				'placeholder'   => __('Address 2', 'jigoshop'),
				'class'         => array('form-row-last'),
				'label_class'   => array('hidden') ),
			array(
				'name'          => 'shipping-city',
				'label'         => __('City', 'jigoshop'),
				'placeholder'   => __('City', 'jigoshop'),
				'required'      => true,
				'class'         => array('form-row-first') ),
			array(
				'name'          =>'shipping-postcode',
				'type'          => 'postcode',
				'validate'      => 'postcode',
				'format'        => 'postcode',
				'label'         => __('Postcode', 'jigoshop'),
				'placeholder'   => __('Postcode', 'jigoshop'),
				'required'      => true,
				'class'=> array('form-row-last') ),
		);

		return $shipping_fields;
	}

	/** Output the billing information form */
	function checkout_form_billing() {

		if (jigoshop_cart::ship_to_billing_address_only()) :

			echo '<h3>'.__('Billing &amp; Shipping', 'jigoshop').'</h3>';

		else :

			echo '<h3>'.__('Billing Address', 'jigoshop').'</h3>';

		endif;

		// Billing Details
		foreach ($this->billing_fields as $field) :
			$field = apply_filters( 'jigoshop_billing_field', $field );
			$this->checkout_form_field( $field );
		endforeach;

		// Registration Form
		if ($this->show_signup) :

			echo '<p class="form-row"><input class="input-checkbox" id="createaccount" ';
			if ($this->get_value('createaccount')) echo 'checked="checked" ';
			echo 'type="checkbox" name="createaccount" /> <label for="createaccount" class="checkbox">'.__('Create an account?', 'jigoshop').'</label></p>';

			echo '<div class="create-account">';

			$this->checkout_form_field( array( 'type'=> 'password', 'name'=> 'account-password', 'label'  => __('Account password', 'jigoshop'), 'placeholder'=> __('Password', 'jigoshop'),'class'      => array('form-row-first')) );
			$this->checkout_form_field( array( 'type'=> 'password', 'name'=> 'account-password-2', 'label'=> __('Account password', 'jigoshop'), 'placeholder'=> __('Password again', 'jigoshop'),'class'=> array('form-row-last'), 'label_class'=> array('hidden')) );
			$this->checkout_form_field( array( 'type'=> 'text', 'name'    => 'account-username', 'label'  => __('Account username', 'jigoshop'), 'placeholder'=> __('Username', 'jigoshop') ) );

			echo '<p><small>'.__('Save time in the future and check the status of your order by creating an account.', 'jigoshop').'</small></p></div>';

		endif;

	}

	/** Output the shipping information form */
	function checkout_form_shipping() {

		// Shipping Details
		if (!jigoshop_cart::ship_to_billing_address_only() && self::get_options()->get_option('jigoshop_calc_shipping') == 'yes') :

			$shiptobilling = !$_POST ? apply_filters('shiptobilling_default', 1) : $this->get_value('shiptobilling');
			$shiptodisplay = self::get_options()->get_option('jigoshop_show_checkout_shipping_fields') == 'no' ? 'checked="checked"' : '';
			?>
			
			<p class="form-row" id="shiptobilling"><input class="input-checkbox" type="checkbox" name="shiptobilling" id="shiptobilling-checkbox" <?php if ($shiptobilling) : echo $shiptodisplay; endif; ?> /> <label for="shiptobilling-checkbox" class="checkbox"><?php _e('Ship to billing address?', 'jigoshop'); ?></label> </p>

			<h3><?php _e('Shipping Address', 'jigoshop'); ?></h3>

			<div class="shipping-address">
			<?php foreach ($this->shipping_fields as $field) :
					$field = apply_filters( 'jigoshop_shipping_field', $field );
					$this->checkout_form_field( $field );
				endforeach; ?>
			</div>

		<?php elseif (jigoshop_cart::ship_to_billing_address_only()) : ?>

			<h3><?php _e('Notes/Comments', 'jigoshop'); ?></h3>

		<?php endif;

		$this->checkout_form_field( array( 'type' => 'textarea', 'class' => array('notes'),  'name' => 'order_comments', 'label' => __('Order Notes', 'jigoshop'), 'placeholder' => __('Notes about your order.', 'jigoshop') ) );

	}

	/**
	 * Outputs a form field
	 *
	 * @param   array	args	contains a list of args for showing the field, merged with defaults (below)
	 */
	function checkout_form_field( $args ) {

		$defaults = array(
			'type'       => 'text',
			'name'       => '',
			'label'      => '',
			'placeholder'=> '',
			'required'   => false,
			'class'      => array(),
			'label_class'=> array(),
			'options'    => array(),
			'selected'   => '',
			'rel'        => '',
			'return'     => false
		);

		$args           = wp_parse_args( $args, $defaults );
		$required       = '';
		$input_required = '';
		$after          = '';
		$field          = '';

		if ( $args['name'] == 'billing-state' || $args['name'] == 'shipping-state' ) {
			if ( jigoshop_customer::has_valid_shipping_state() ) $args['required'] = false;
		}
		if ($args['required']) {
			$required = ' <span class="required">*</span>';
			$input_required = ' input-required';
		}

		if (in_array('form-row-last', $args['class'])) {
			$after = '<div class="clear"></div>';
		}

		switch ($args['type']) :
			case "country" :

				/**
				 * Remove 'Select a Country' option from drop-down menu for countries.
				 * There is no need to have it, because was assume when user hasn't selected
				 * a country that they are from the shop base country.
				 */
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
				<label for="'.esc_attr($args['name']).'" class="'.esc_attr(implode(' ', $args['label_class'])).'">'.$args['label'].$required.'</label>
				<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="country_to_state" rel="'.esc_attr($args['rel']).'">';

				foreach(jigoshop_countries::get_allowed_countries() as $key=>$value) :
					$field .= '<option value="'.esc_attr($key).'"';
					if ($this->get_value($args['name'])==$key) $field .= 'selected="selected"';
					elseif (!$this->get_value($args['name']) && jigoshop_customer::get_country()==$key) $field .= 'selected="selected"';
					$field .= '>'.__($value, 'jigoshop').'</option>';
				endforeach;

				$field .= '</select></p>'.$after;

			break;
			case "state" :

				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="' . esc_attr( $args['name'] ) . '" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>';

				$current_cc = $this->get_value($args['rel']);
				if (!$current_cc) $current_cc = jigoshop_customer::get_country();

				$current_r = $this->get_value($args['name']);
				if (!$current_r) $current_r = jigoshop_customer::get_state();

				$states = jigoshop_countries::get_states( $current_cc );

				if (isset( $states[$current_r] )) :
					// Dropdown
					$field .= '<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="'.esc_attr($input_required).'"><option value="">'.__('Select a state&hellip;', 'jigoshop').'</option>';
					foreach($states as $key=>$value) :
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
				$current_pc = $this->get_value($args['name']);
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
					<textarea name="'.esc_attr($args['name']).'" class="input-text' . esc_attr( $input_required ) . '" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" cols="5" rows="2">'. esc_textarea( $this->get_value( $args['name'] ) ).'</textarea>
				</p>'.$after;

			break;
			case "select" :
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="' . esc_attr( $args['name'] ) . '" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="input-text" rel="'.esc_attr($args['rel']).'">';

				foreach($args['options'] as $key=>$value) :
					$field .= '<option value="'.esc_attr($key).'"';
					if (esc_attr($args['selected'])==$key) $field .= 'selected="selected"';
					$field .= '>'.__($value, 'jigoshop').'</option>';
				endforeach;

				$field .= '</select></p>'.$after;

			break;
			default :

				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="' . esc_attr( $args['name'] ) . '" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<input type="'.$args['type'].'" class="input-text' . esc_attr( $input_required ) . '" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" value="'. $this->get_value( $args['name'] ).'" />
				</p>'.$after;

			break;
		endswitch;

		if ($args['return']) return $field; else echo $field;
	}

	/** Process the checkout after the confirm order button is pressed */
	function process_checkout() {

		global $wpdb;

		if (!defined('JIGOSHOP_CHECKOUT')) define('JIGOSHOP_CHECKOUT', true);

        // always calculate totals when coming to checkout, as we need the total calculated on the cart here
        jigoshop_cart::get_cart(); // calls get_cart_from_session() if required
        jigoshop_cart::calculate_totals();

		if (isset($_POST) && $_POST && !isset($_POST['login'])) :

			jigoshop::verify_nonce('process_checkout');

			if ( sizeof(jigoshop_cart::$cart_contents) == 0 ) :
				jigoshop::add_error( sprintf(__('Sorry, your session has expired. <a href="%s">Return to homepage &rarr;</a>','jigoshop'), home_url()) );
			endif;

			// Process Discount Codes
			if ( !empty( $_POST['coupon_code'] ) ) {
				$coupon_code = sanitize_title( $_POST['coupon_code'] );
				jigoshop_cart::add_discount($coupon_code);
			}

			/* Check if the payment method is allowed for our coupons. */
			if ( !empty(jigoshop_cart::$applied_coupons) ) {
				foreach ( jigoshop_cart::$applied_coupons as $coupon_code )
					jigoshop_cart::valid_coupon($coupon_code);
			}

			// Checkout fields
			if (isset($_POST['shipping_method'])) :
				$shipping_method = jigowatt_clean($_POST['shipping_method']);
				$shipping_data = explode(":", $shipping_method);
				$this->posted['shipping_method'] = $shipping_data[0];
				$this->posted['shipping_service']= $shipping_data[1];
			else :
				$this->posted['shipping_method'] = '';
				$this->posted['shipping_service']= '';
			endif;

			$this->posted['shiptobilling']     = isset($_POST['shiptobilling'])     ? jigowatt_clean($_POST['shiptobilling'])     : '';
			$this->posted['payment_method']    = isset($_POST['payment_method'])    ? jigowatt_clean($_POST['payment_method'])    : '';
			$this->posted['order_comments']    = isset($_POST['order_comments'])    ? jigowatt_clean($_POST['order_comments'])    : '';
			$this->posted['terms']             = isset($_POST['terms'])             ? jigowatt_clean($_POST['terms'])             : '';
			$this->posted['createaccount']     = isset($_POST['createaccount'])     ? jigowatt_clean($_POST['createaccount'])     : '';
			$this->posted['account-username']  = isset($_POST['account-username'])  ? jigowatt_clean($_POST['account-username'])  : '';
			$this->posted['account-password']  = isset($_POST['account-password'])  ? jigowatt_clean($_POST['account-password'])  : '';
			$this->posted['account-password-2']= isset($_POST['account-password-2'])? jigowatt_clean($_POST['account-password-2']): '';

			// establish customer billing and shipping locations
			if (jigoshop_cart::ship_to_billing_address_only()) $this->posted['shiptobilling'] = 'true';
			$country  = isset($_POST['billing-country']) ? jigowatt_clean($_POST['billing-country']) : '';
			$state    = isset($_POST['billing-state']) ? jigowatt_clean($_POST['billing-state']) : '';
			$postcode = isset($_POST['billing-postcode']) ? jigowatt_clean($_POST['billing-postcode']) : '';
            jigoshop_customer::set_location($country, $state, $postcode);
            if ( $this->posted['shiptobilling'] ) {
            	jigoshop_customer::set_shipping_location($country, $state, $postcode);
            } else {
				$country  = isset($_POST['shipping-country']) ? jigowatt_clean($_POST['shipping-country']) : '';
				$state    = isset($_POST['shipping-state']) ? jigowatt_clean($_POST['shipping-state']) : '';
				$postcode = isset($_POST['shipping-postcode']) ? jigowatt_clean($_POST['shipping-postcode']) : '';
				jigoshop_customer::set_shipping_location($country, $state, $postcode);
			}
			
			// Billing Information
			foreach ($this->billing_fields as $field) :
				$field = apply_filters( 'jigoshop_billing_field', $field );

				$this->posted[$field['name']] = isset($_POST[$field['name']]) ? jigowatt_clean($_POST[$field['name']]) : '';

				// Format
				if (isset($field['format'])) switch ( $field['format'] ) :
					case 'postcode' : $this->posted[$field['name']] = strtolower(str_replace(' ', '', $this->posted[$field['name']])); break;
				endswitch;

				// Required
				if ( $field['name'] == 'billing-state' ) {
					if ( jigoshop_customer::has_valid_shipping_state() ) $field['required'] = false;
				}
				if ( isset($field['required']) && $field['required'] && empty($this->posted[$field['name']]) ) jigoshop::add_error( $field['label'] . __(' (billing) is a required field.','jigoshop') );

				if ( $field['name'] == 'billing-euvatno' ) {
					$vatno = !empty( $this->posted[$field['name']] ) ? $this->posted[$field['name']] : '';
					$vatno = str_replace( ' ', '', $vatno );
					// strip any country code from the beginning of the number
					if ( strtolower( substr( $vatno, 0, strlen($country) )) == strtolower( $country ) ) {
						$vatno = substr( $vatno, strlen($country) );
					}
					if ( $vatno <> '' ) {
						$url = 'http://isvat.appspot.com/' . $country . '/' . $vatno . '/';
						$httpRequest = curl_init();
						curl_setopt( $httpRequest, CURLOPT_FAILONERROR, true );
						curl_setopt( $httpRequest, CURLOPT_RETURNTRANSFER, true );
						curl_setopt( $httpRequest, CURLOPT_HEADER, false );
						curl_setopt( $httpRequest, CURLOPT_URL, $url );
						$result = curl_exec( $httpRequest );
						curl_close( $httpRequest );
						if ( $result === 'false' ) {
							jigoshop_log( "EU VAT validation error with URL: " . $url );
							jigoshop::add_error( $field['label'] . __(' (billing) is not a valid VAT Number.  Leave it blank to disable VAT validation.  (VAT may be charged depending on your location)','jigoshop') );
						} else {
							if ( $country != jigoshop_countries::get_base_country() ) $this->valid_euvatno = true;
						}
    				}
				}
				
				// Validation
				if (isset($field['validate']) && !empty($this->posted[$field['name']])) {

					switch ( $field['validate'] ) {
						case 'phone' :
							if (!jigoshop_validation::is_phone( $this->posted[$field['name']] ))
								jigoshop::add_error( $field['label'] . __(' (billing) is not a valid number.','jigoshop') );
							break;

						case 'email' :
							if (!jigoshop_validation::is_email( $this->posted[$field['name']] ))
								jigoshop::add_error( $field['label'] . __(' (billing) is not a valid email address.','jigoshop') );
							break;

						case 'postcode' :
							if (!jigoshop_validation::is_postcode( $this->posted[$field['name']], $_POST['billing-country'] ))
								jigoshop::add_error( $field['label'] . __(' (billing) is not a valid postcode/ZIP.','jigoshop') );
							else
								$this->posted[$field['name']] = jigoshop_validation::format_postcode( $this->posted[$field['name']], $_POST['billing-country'] );
							break;
					}

				}
				
			endforeach;

			// Shipping Information
			if (jigoshop_shipping::is_enabled() && !jigoshop_cart::ship_to_billing_address_only() && empty($this->posted['shiptobilling'])) :

				foreach ($this->shipping_fields as $field) :
					$field = apply_filters( 'jigoshop_shipping_field', $field );

					if (isset( $_POST[$field['name']] )) $this->posted[$field['name']] = jigowatt_clean($_POST[$field['name']]); else $this->posted[$field['name']] = '';

					// Format
					if (isset($field['format'])) switch ( $field['format'] ) :
						case 'postcode' : $this->posted[$field['name']] = strtolower(str_replace(' ', '', $this->posted[$field['name']])); break;
					endswitch;

					// Required
					if ( $field['name'] == 'shipping-state' ) {
						if ( jigoshop_customer::has_valid_shipping_state() ) $field['required'] = false;
					}
					if ( isset($field['required']) && $field['required'] && empty($this->posted[$field['name']]) ) jigoshop::add_error( $field['label'] . __(' (shipping) is a required field.','jigoshop') );

					// Validation
					if (isset($field['validate']) && !empty($this->posted[$field['name']])) switch ( $field['validate'] ) :
						case 'postcode' :
							if (!jigoshop_validation::is_postcode( $this->posted[$field['name']], $this->posted['shipping-country'] )) : jigoshop::add_error( $field['label'] . __(' (shipping) is not a valid postcode/ZIP.','jigoshop') );
							else :
								$this->posted[$field['name']] = jigoshop_validation::format_postcode( $this->posted[$field['name']], $this->posted['shipping-country'] );
							endif;
						break;
					endswitch;

				endforeach;

			endif;

			if ( $this->must_register && empty($this->posted['createaccount']) ) jigoshop::add_error( __('Sorry, you must agree to creating an account', 'jigoshop') );

			if ($this->must_register || ( empty($user_id) && ($this->posted['createaccount'])) ) :

				if ( !$this->show_signup ) jigoshop::add_error( __('Sorry, the shop owner has disabled guest purchases.','jigoshop') );

				if ( empty($this->posted['account-username']) ) jigoshop::add_error( __('Please enter an account username.','jigoshop') );
				if ( empty($this->posted['account-password']) ) jigoshop::add_error( __('Please enter an account password.','jigoshop') );
				if ( $this->posted['account-password-2'] !== $this->posted['account-password'] ) jigoshop::add_error( __('Passwords do not match.','jigoshop') );

				// Check the username
				if ( !validate_username( $this->posted['account-username'] ) ) :
					jigoshop::add_error( __('Invalid email/username.','jigoshop') );
				elseif ( username_exists( $this->posted['account-username'] ) ) :
					jigoshop::add_error( __('An account is already registered with that username. Please choose another.','jigoshop') );
				endif;

				// Check the e-mail address
				if ( email_exists( $this->posted['billing-email'] ) ) :
					jigoshop::add_error( __('An account is already registered with your email address. Please login.','jigoshop') );
				endif;
			endif;

			// Terms
			if (!isset($_POST['update_totals']) && empty($this->posted['terms']) && jigoshop_get_page_id('terms')>0 ) jigoshop::add_error( __('You must accept our Terms &amp; Conditions.','jigoshop') );

			if (jigoshop_cart::needs_shipping()) :

				// Shipping Method
				$available_methods = jigoshop_shipping::get_available_shipping_methods();
				if (!isset($available_methods[$this->posted['shipping_method']]))
					jigoshop::add_error( __('Invalid shipping method.','jigoshop') );

			endif;

            // Payment method
            $available_gateways = jigoshop_payment_gateways::get_available_payment_gateways();

            // can't just simply check needs_payment() here, as paypal may have force payment set to true
            if (!empty($this->posted['payment_method']) && self::process_gateway($available_gateways[$this->posted['payment_method']])) :
                // Payment Method Field Validation
                $available_gateways[$this->posted['payment_method']]->validate_fields();
            endif;

			// hook, to be able to use the validation, but to be able to do something different afterwards
			do_action( 'jigoshop_after_checkout_validation', $this->posted, $_POST, sizeof(jigoshop::$errors) );

			if (!isset($_POST['update_totals']) && !jigoshop::has_errors()) :

				$user_id = get_current_user_id();

				while (1) :

					// Create customer account and log them in
					if ($this->show_signup && !$user_id && $this->posted['createaccount']) :

						$reg_errors = new WP_Error();
						do_action('register_post', $this->posted['billing-email'], $this->posted['billing-email'], $reg_errors);
						$errors = apply_filters( 'registration_errors', $reg_errors, $this->posted['billing-email'], $this->posted['billing-email'] );

		                // if there are no errors, let's create the user account
						if ( !$reg_errors->get_error_code() ) :

			                $user_pass = $this->posted['account-password'];
			                $user_id = wp_create_user( $this->posted['account-username'], $user_pass, $this->posted['billing-email'] );
			                if ( !$user_id ) {
			                	jigoshop::add_error( sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', 'jigoshop'), self::get_options()->get_option('jigoshop_email')));
			                    break;
							}
		                    // Change role
		                    wp_update_user( array ('ID' => $user_id, 'role' => 'customer', 'first_name' => $this->posted['billing-first_name'], 'last_name' => $this->posted['billing-last_name']) ) ;

	                    	do_action( 'jigoshop_created_customer', $user_id );

		                    // send the user a confirmation and their login details
		                    wp_new_user_notification( $user_id, $user_pass );

		                    // set the WP login cookie
		                    $secure_cookie = is_ssl() ? true : false;
		                    wp_set_auth_cookie($user_id, true, $secure_cookie);

						else :
							jigoshop::add_error( $reg_errors->get_error_message() );
		                	break;
						endif;

					endif;

					$shipping_first_name = $shipping_last_name = $shipping_company = $shipping_euvatno = $shipping_address_1 =
					$shipping_address_2 = $shipping_city = $shipping_state = $shipping_postcode = $shipping_country = '';

					// Get shipping/billing
					if ( !empty($this->posted['shiptobilling']) ) {

						$shipping_first_name= $this->posted['billing-first_name'];
						$shipping_last_name = $this->posted['billing-last_name'];
						$shipping_company   = $this->posted['billing-company'];
						$shipping_address_1 = $this->posted['billing-address'];
						$shipping_address_2 = $this->posted['billing-address-2'];
						$shipping_city      = $this->posted['billing-city'];
						$shipping_state     = $this->posted['billing-state'];
						$shipping_postcode  = $this->posted['billing-postcode'];
						$shipping_country   = $this->posted['billing-country'];
						if ( $this->valid_euvatno ) {
							$vatno = $this->posted['billing-euvatno'];
							$vatno = str_replace( ' ', '', $vatno );
							// some may enter the 2 character country code, some may not
							// strip any country code from the beginning of the number
							if ( strtolower( substr( $vatno, 0, strlen($shipping_country) )) == strtolower( $shipping_country ) ) {
								$vatno = substr( $vatno, strlen($shipping_country) );
							}
							// now add the country code back into the beginning
							$shipping_euvatno = $shipping_country . $vatno;
							$this->posted['billing-euvatno'] = $shipping_euvatno;
						}

					} elseif ( jigoshop_shipping::is_enabled() ) {

						$shipping_first_name= $this->posted['shipping-first_name'];
						$shipping_last_name = $this->posted['shipping-last_name'];
						$shipping_company   = $this->posted['shipping-company'];
						$shipping_address_1 = $this->posted['shipping-address'];
						$shipping_address_2 = $this->posted['shipping-address-2'];
						$shipping_city      = $this->posted['shipping-city'];
						$shipping_state     = $this->posted['shipping-state'];
						$shipping_postcode  = $this->posted['shipping-postcode'];
						$shipping_country   = $this->posted['shipping-country'];

					}

					// Save billing/shipping to user meta fields
					if ($user_id>0) :
						update_user_meta( $user_id, 'billing-first_name', $this->posted['billing-first_name'] );
						update_user_meta( $user_id, 'billing-last_name' , $this->posted['billing-last_name'] );
						update_user_meta( $user_id, 'billing-company'   , $this->posted['billing-company'] );
						if ( $this->valid_euvatno ) update_user_meta( $user_id, 'billing-euvatno', $this->posted['billing-euvatno'] );
						update_user_meta( $user_id, 'billing-email'     , $this->posted['billing-email'] );
						update_user_meta( $user_id, 'billing-address'   , $this->posted['billing-address'] );
						update_user_meta( $user_id, 'billing-address-2' , $this->posted['billing-address-2'] );
						update_user_meta( $user_id, 'billing-city'      , $this->posted['billing-city'] );
						update_user_meta( $user_id, 'billing-postcode'  , $this->posted['billing-postcode'] );
						update_user_meta( $user_id, 'billing-country'   , $this->posted['billing-country'] );
						update_user_meta( $user_id, 'billing-state'     , $this->posted['billing-state'] );
						update_user_meta( $user_id, 'billing-phone'     , $this->posted['billing-phone'] );

						if ( empty($this->posted['shiptobilling']) && jigoshop_shipping::is_enabled() ) :
							update_user_meta( $user_id, 'shipping-first_name', $this->posted['shipping-first_name'] );
							update_user_meta( $user_id, 'shipping-last_name', $this->posted['shipping-last_name'] );
							update_user_meta( $user_id, 'shipping-company', $this->posted['shipping-company'] );
							update_user_meta( $user_id, 'shipping-address', $this->posted['shipping-address'] );
							update_user_meta( $user_id, 'shipping-address-2', $this->posted['shipping-address-2'] );
							update_user_meta( $user_id, 'shipping-city', $this->posted['shipping-city'] );
							update_user_meta( $user_id, 'shipping-postcode', $this->posted['shipping-postcode'] );
							update_user_meta( $user_id, 'shipping-country', $this->posted['shipping-country'] );
							update_user_meta( $user_id, 'shipping-state', $this->posted['shipping-state'] );
						elseif ( $this->posted['shiptobilling'] && jigoshop_shipping::is_enabled() ) :
							update_user_meta( $user_id, 'shipping-first_name', $this->posted['billing-first_name'] );
							update_user_meta( $user_id, 'shipping-last_name' , $this->posted['billing-last_name'] );
							update_user_meta( $user_id, 'shipping-company'   , $this->posted['billing-company'] );
							update_user_meta( $user_id, 'shipping-address'   , $this->posted['billing-address'] );
							update_user_meta( $user_id, 'shipping-address-2' , $this->posted['billing-address-2'] );
							update_user_meta( $user_id, 'shipping-city'      , $this->posted['billing-city'] );
							update_user_meta( $user_id, 'shipping-postcode'  , $this->posted['billing-postcode'] );
							update_user_meta( $user_id, 'shipping-country'   , $this->posted['billing-country'] );
							update_user_meta( $user_id, 'shipping-state'     , $this->posted['billing-state'] );
						endif;

					endif;

					// Create Order (send cart variable so we can record items and reduce inventory). Only create if this is a new order, not if the payment was rejected last time.
					$order_data = array(
						'post_type'   => 'shop_order',
						'post_title'  => 'Order &ndash; '.date('F j, Y @ h:i A'),
						'post_status' => 'publish',
						'post_excerpt'=> $this->posted['order_comments'],
						'post_author' => 1
					);

					// Order meta data
					$data = array();
					$applied_coupons = array();

					foreach ( jigoshop_cart::$applied_coupons as $coupon )
						$applied_coupons[] = JS_Coupons::get_coupon( $coupon );

					do_action('jigoshop_checkout_update_order_total', $this->posted);
					
					$data['order_discount_coupons'] = $applied_coupons;
					$data['billing_first_name']     = $this->posted['billing-first_name'];
					$data['billing_last_name']      = $this->posted['billing-last_name'];
					$data['billing_company']        = $this->posted['billing-company'];
					if ( $this->valid_euvatno ) $data['billing_euvatno'] = $this->posted['billing-euvatno'];
					$data['billing_address_1']      = $this->posted['billing-address'];
					$data['billing_address_2']      = $this->posted['billing-address-2'];
					$data['billing_city']           = $this->posted['billing-city'];
					$data['billing_postcode']       = $this->posted['billing-postcode'];
					$data['billing_country']        = $this->posted['billing-country'];
					$data['billing_state']          = $this->posted['billing-state'];
					$data['billing_email']          = $this->posted['billing-email'];
					$data['billing_phone']          = $this->posted['billing-phone'];
					$data['shipping_first_name']    = $shipping_first_name;
					$data['shipping_last_name']     = $shipping_last_name;
					$data['shipping_company']       = $shipping_company;
					$data['shipping_address_1']     = $shipping_address_1;
					$data['shipping_address_2']     = $shipping_address_2;
					$data['shipping_city']          = $shipping_city;
					$data['shipping_postcode']      = $shipping_postcode;
					$data['shipping_country']       = $shipping_country;
					$data['shipping_state']         = $shipping_state;
					$data['shipping_method']        = $this->posted['shipping_method'];
					$data['shipping_service']       = $this->posted['shipping_service'];
					$data['payment_method']         = $this->posted['payment_method'];
					$data['payment_method_title']   = !empty($available_gateways[$this->posted['payment_method']]) ?$available_gateways[$this->posted['payment_method']]->title : '';
					$data['order_subtotal']         = jigoshop_cart::get_cart_subtotal(false, false, true);/* no display, no shipping, no tax */
					$data['order_discount_subtotal']= jigoshop_cart::get_cart_subtotal(false, false, true)
						+ jigoshop_cart::get_cart_shipping_total(false, true)
						- jigoshop_cart::$discount_total;
					$data['order_shipping']         = jigoshop_cart::get_cart_shipping_total(false, true);
					$data['order_discount']         = number_format(jigoshop_cart::$discount_total, 2, '.', '');
					$data['order_tax']              = jigoshop_cart::get_taxes_as_string();
					$data['order_tax_divisor']      = jigoshop_cart::get_tax_divisor();
					$data['order_shipping_tax']     = number_format(jigoshop_cart::$shipping_tax_total, 2, '.', '');
					$data['order_total']            = jigoshop_cart::get_total(false);
					$data['order_total_prices_per_tax_class_ex_tax'] = jigoshop_cart::get_price_per_tax_class_ex_tax();
					
					if ( $this->valid_euvatno ) {
						$data['order_tax'] = '';
						$temp = jigoshop_cart::get_total_cart_tax_without_shipping_tax();
						$data['order_total'] -= ($temp + $data['order_shipping_tax']);
						$data['order_shipping_tax'] = 0;
					}
					
					// Cart items
					$order_items = array();

					foreach (jigoshop_cart::$cart_contents as $cart_item_key => $values) :

						$_product = $values['data'];

					 	// Check stock levels
					 	if ( $_product->managing_stock() && (!$_product->is_in_stock() || !$_product->has_enough_stock($values['quantity']) ) ) :
							
							$temp = new jigoshop_product( $_product->ID );
							$errormsg = (self::get_options()->get_option('jigoshop_show_stock') == 'yes')
							? (sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order.  We have %d available at this time. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $_product->get_title(), $temp->get_stock() ))
							: (sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $_product->get_title() ));
							jigoshop::add_error($errormsg);
							break;
						endif;

						// Calc item tax to store
                        //TODO: need to change this so that the admin pages can use all tax data on the page
//						$rate = jigoshop_cart::get_total_tax_rate();
						// the above line was changed for 1.3, whatever this was doesn't give the correct tax rate
						// what follows may not be right either, but seems to work
						$rates = $_product->get_tax_destination_rate();
						$rates = current( $rates );
						if ( isset( $rates['rate'] ) ) {
							$rate = $rates['rate'];
						} else {
							$rate = 0.00;
						}
						if ( $this->valid_euvatno ) $rate = 0.00;
						
						$price_inc_tax = (
							self::get_options()->get_option('jigoshop_calc_taxes') == 'yes'
							&& self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes'
							? $_product->get_price()
							: -1
						);

						if ( !empty( $values['variation_id'] )) {
							$product_id = $values['variation_id'];
						} else {
							$product_id = $values['product_id'];
						}

						$custom_products = (array) jigoshop_session::instance()->customized_products;
						$custom = isset( $custom_products[$product_id] ) ? $custom_products[$product_id] : '';
						if ( ! empty( $custom_products[$product_id] ) ) :
							$custom = $custom_products[$product_id];
							unset( $custom_products[$product_id] );
							jigoshop_session::instance()->customized_products = $custom_products;
						endif;

                        $order_items[] = apply_filters('new_order_item', array(
					 		'id' 			=> $values['product_id'],
					 		'variation_id' 	=> $values['variation_id'],
                            'variation'     => $values['variation'],
                            'customization' => $custom,
					 		'name' 			=> $_product->get_title(),
					 		'qty' 			=> (int) $values['quantity'],
					 		'cost'          => $_product->get_price_excluding_tax() * (int) $values['quantity'],
                            'cost_inc_tax'  => $price_inc_tax, // if less than 0 don't use this
					 		'taxrate' 		=> $rate
					 	), $values);

					endforeach;

					if ( jigoshop::has_errors() ) break;

					// Insert or update the post data
					// @TODO: This first bit over-writes an existing uncompleted order.  Do we want this?  -JAP-
					// UPDATE: commenting out for now. multiple orders now created.
// 					if (isset($_SESSION['order_awaiting_payment']) && $_SESSION['order_awaiting_payment'] > 0) :
//
// 						$order_id = (int) $_SESSION['order_awaiting_payment'];
// 						$order_data['ID'] = $order_id;
// 						wp_update_post( $order_data );
//
// 					else :
						$order_id = wp_insert_post( $order_data );

						if (is_wp_error($order_id)) :
							jigoshop::add_error( 'Error: Unable to create order. Please try again.' );
			                break;
						endif;
//					endif;

					// Update post meta
					update_post_meta( $order_id, 'order_data', $data );
					update_post_meta( $order_id, 'order_key', uniqid('order_') );
					update_post_meta( $order_id, 'customer_user', (int) $user_id );
					update_post_meta( $order_id, 'order_items', $order_items );
					wp_set_object_terms( $order_id, 'pending', 'shop_order_status' );

					$order = new jigoshop_order($order_id);

					/* Coupon usage limit */
					foreach ( $data['order_discount_coupons'] as $coupon ) :
						$coupon_id = JS_Coupons::get_coupon_post_id( $coupon['code'] );
						if ( $coupon_id !== false ) {
							$usage_count = get_post_meta( $coupon_id, 'usage', true );
							$usage_count = empty( $usage_count ) ? 1 : $usage_count + 1;
							update_post_meta( $coupon_id, 'usage', $usage_count );
						}
					endforeach;

					// Inserted successfully
					do_action('jigoshop_new_order', $order_id);

					do_action('jigoshop_checkout_update_order_meta', $order_id, $this->posted );

                    // can't just simply check needs_payment() here, as paypal may have force payment set to true
					if (!empty($this->posted['payment_method']) && self::process_gateway($available_gateways[$this->posted['payment_method']])) :

						// Store Order ID in session so it can be re-used after payment failure
						jigoshop_session::instance()->order_awaiting_payment = $order_id;

						// Process Payment
						$result = $available_gateways[$this->posted['payment_method']]->process_payment( $order_id );

						// Redirect to success/confirmation/payment page
						if ($result['result']=='success') :

							if (is_ajax()) :
								echo json_encode(apply_filters('jigoshop_is_ajax_payment_successful', $result));
								exit;
							else :
								wp_safe_redirect( apply_filters('jigoshop_is_ajax_payment_successful', $result['redirect']) );
								exit;
							endif;

						endif;

					else :

						// No payment was required for order
						$order->payment_complete();

						// Empty the Cart
						jigoshop_cart::empty_cart();

						// Redirect to success/confirmation/payment page
						$checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks') );
						if (is_ajax()) :
							echo json_encode( array( 'result' => 'success', 'redirect' => get_permalink( $checkout_redirect ) ) );
							exit;
						else :
							wp_safe_redirect( get_permalink( $checkout_redirect ) );
							exit;
						endif;

					endif;

					// Break out of loop
					break;

				endwhile;

			endif;

			// If we reached this point then there were errors
			if (is_ajax()) :
				jigoshop::show_messages();
				exit;
			else :
				jigoshop::show_messages();
			endif;

		endif;
	}

	/** Gets the value either from the posted data, or from the users meta data */
	function get_value( $input ) {
		if (isset( $this->posted[$input] ) && !empty($this->posted[$input])) :
			return $this->posted[$input];
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

    static function get_shipping_dropdown() {

        if (jigoshop_cart::needs_shipping()) :
          ?><tr>
                <td colspan="2"><?php _e('Shipping', 'jigoshop'); ?><br /><small><?php echo _x('To: ','shipping destination','jigoshop') . __(jigoshop_customer::get_shipping_country_or_state(), 'jigoshop'); ?></small>
                </td>
                <td>
                    <?php
                    $available_methods = jigoshop_shipping::get_available_shipping_methods();

                    if (sizeof($available_methods) > 0) :

                        echo '<select name="shipping_method" id="shipping_method">';

                        foreach ($available_methods as $method) :

                            $selected_service = NULL;
                            if ($method->is_chosen()) :

                                if (is_numeric( jigoshop_session::instance()->selected_rate_id )) :
                                    $selected_service = $method->get_selected_service( jigoshop_session::instance()->selected_rate_id );
                                else :
                                    $selected_service = $method->get_cheapest_service();
                                endif;
                            endif;
                            for ($i = 0; $i < $method->get_rates_amount(); $i++) :
                                echo '<option value="' . esc_attr( $method->id . ':' . $method->get_selected_service($i) . ':' . $i ) . '" ';
                                if ($method->get_selected_service($i) == $selected_service) :
                                    echo 'selected="selected"';
                                endif;
								
								echo '>' . $method->get_selected_service($i) . ' &ndash; ';
									
                                if ($method->get_selected_price($i) > 0) :

                                    $tax_label = 0; // 0 no label, 1 ex. tax, 2 inc. tax
                                    $price = 0;
                                    if (self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes' ) {

                                        // check that shipping is indeed taxed.
                                        if (jigoshop_cart::$shipping_tax_total > 0) {
                                            $tax_label = 1; // inc. tax
                                        }

//                                        $price = $method->get_selected_price($i) + $method->get_selected_tax($i);
                                        $price = $method->get_selected_price($i);

                                    }
                                    else {

                                        // check that shipping is indeed taxed.
                                        if (jigoshop_cart::$shipping_tax_total > 0) {
                                            $tax_label = 1; // ex. tax
                                        }

                                        $price = $method->get_selected_price($i);

                                    }
                                    echo jigoshop_price($price, array('ex_tax_label' => $tax_label));
                                else :
                                    echo __('Free', 'jigoshop');
                                endif;
                                echo '</option>';
                            endfor;

                        endforeach;

                        echo '</select>';

                    else :

                        echo '<p>' . __(jigoshop_shipping::get_shipping_error_message(), 'jigoshop') . '</p>';

                    endif;
                    ?></td>
            </tr><?php
        endif;

    }

    /**
     * This method makes sure we require payment for the particular gateway being used.
     * @param jigoshop_payment_gateway $payment_gateway the payment gateway
     * that is being used during checkout
     * @return boolean true when the gateway should be processed, otherwise false
     * @since 1.2
     */
    public static function process_gateway($payment_gateway) {
        if (!isset($payment_gateway)) :


            jigoshop::add_error( __('Invalid payment method.','jigoshop') );
            return false;
        else :
            $shipping_total = (self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes' ? jigoshop_cart::$shipping_tax_total + jigoshop_cart::$shipping_total : jigoshop_cart::$shipping_total);
            return $payment_gateway->process_gateway(number_format(jigoshop_cart::$subtotal, 2, '.', ''), number_format($shipping_total, 2, '.', ''), number_format(jigoshop_cart::$discount_total, 2, '.', ''));
        endif;
    }
}
