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
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
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

		$this->must_register =
			( self::get_options()->get('jigoshop_enable_guest_checkout') != 'yes'
			&& ! is_user_logged_in() );
		$this->show_signup =
			( self::get_options()->get('jigoshop_enable_signup_form') == 'yes'
			&& ! is_user_logged_in() );

		add_action( 'jigoshop_checkout_billing', array( $this, 'checkout_form_billing' ));
		add_action( 'jigoshop_checkout_shipping', array( $this, 'checkout_form_shipping' ));
		add_action( 'jigoshop_checkout_payment_methods', array( $this, 'checkout_form_payment_methods' ));

		$this->billing_fields = self::get_billing_fields();
		$this->billing_fields = apply_filters( 'jigoshop_billing_fields', $this->billing_fields );

		$this->shipping_fields = self::get_shipping_fields();
		$this->shipping_fields = apply_filters( 'jigoshop_shipping_fields', $this->shipping_fields );

	}

	/**
	 * @return array List of billing fields.
	 */
	public static function get_billing_fields()
	{
		$billing_fields = array(
			array(
				'name' => 'billing_first_name',
				'label' => __('First Name', 'jigoshop'),
				'placeholder' => __('First Name', 'jigoshop'),
				'required' => true,
				'class' => array('form-row-first')
			),
			array(
				'name' => 'billing_last_name',
				'label' => __('Last Name', 'jigoshop'),
				'placeholder' => __('Last Name', 'jigoshop'),
				'required' => true,
				'class' => array('form-row-last')
			),
			array(
				'name' => 'billing_company',
				'label' => __('Company', 'jigoshop'),
				'placeholder' => __('Company', 'jigoshop')
			),
			array(
				'name' => 'billing_euvatno',
				'label' => __('EU VAT Number', 'jigoshop'),
				'placeholder' => __('EU VAT Number', 'jigoshop'),
			),
			array(
				'name' => 'billing_address_1',
				'label' => __('Address', 'jigoshop'),
				'placeholder' => __('Address 1', 'jigoshop'),
				'required' => true,
				'class' => array('form-row-first')
			),
			array(
				'name' => 'billing_address_2',
				'label' => __('Address 2', 'jigoshop'),
				'placeholder' => __('Address 2', 'jigoshop'),
				'class' => array('form-row-last'),
				'label_class' => array('hidden')
			),
			array(
				'name' => 'billing_city',
				'label' => __('City', 'jigoshop'),
				'placeholder' => __('City', 'jigoshop'),
				'required' => true
			),
			array(
				'name' => 'billing_state',
				'type' => 'state',
				'label' => __('State/Province', 'jigoshop'),
				'required' => true,
				'rel' => 'billing_country'
			),
			array(
				'name' => 'billing_country',
				'type' => 'country',
				'label' => __('Country', 'jigoshop'),
				'required' => true,
				'rel' => 'billing_state',
			),
			array(
				'name' => 'billing_postcode',
				'type' => 'postcode',
				'validate' => 'postcode',
				'format' => 'postcode',
				'label' => __('Postcode', 'jigoshop'),
				'placeholder' => __('Postcode', 'jigoshop'),
				'rel' => 'billing_country',
				'required' => true,
				'class' => array('form-row-first')
			),
			array(
				'name' => 'billing_phone',
				'validate' => 'phone',
				'label' => __('Phone', 'jigoshop'),
				'placeholder' => __('Phone number', 'jigoshop'),
				'required' => true,
				'class' => array('form-row-last'),
			),
			array(
				'name' => 'billing_email',
				'validate' => 'email',
				'label' => __('Email Address', 'jigoshop'),
				'placeholder' => __('you@yourdomain.com', 'jigoshop'),
				'required' => true,
			),
		);

		if (!jigoshop_countries::is_eu_country(jigoshop_countries::get_base_country())) {
			unset($billing_fields[3]);
		}

		return $billing_fields;
	}

	/**
	 * @return array List of shipping fields.
	 */
	public static function get_shipping_fields()
	{
		$shipping_fields = array(
			array(
				'name' => 'shipping_first_name',
				'label' => __('First Name', 'jigoshop'),
				'placeholder' => __('First Name', 'jigoshop'),
				'required' => true,
				'class' => array('form-row-first')
			),
			array(
				'name' => 'shipping_last_name',
				'label' => __('Last Name', 'jigoshop'),
				'placeholder' => __('Last Name', 'jigoshop'),
				'required' => true,
				'class' => array('form-row-last')
			),
			array(
				'name' => 'shipping_company',
				'label' => __('Company', 'jigoshop'),
				'placeholder' => __('Company', 'jigoshop')
			),
			array(
				'name' => 'shipping_address_1',
				'label' => __('Address', 'jigoshop'),
				'placeholder' => __('Address 1', 'jigoshop'),
				'required' => true,
				'class' => array('form-row-first')
			),
			array(
				'name' => 'shipping_address_2',
				'label' => __('Address 2', 'jigoshop'),
				'placeholder' => __('Address 2', 'jigoshop'),
				'class' => array('form-row-last'),
				'label_class' => array('hidden')
			),
			array(
				'name' => 'shipping_city',
				'label' => __('City', 'jigoshop'),
				'placeholder' => __('City', 'jigoshop'),
				'required' => true
			),
			array(
				'name' => 'shipping_state',
				'type' => 'state',
				'label' => __('State/Province', 'jigoshop'),
				'required' => true,
				'rel' => 'shipping_country'
			),
			array(
				'name' => 'shipping_country',
				'type' => 'country',
				'label' => __('Country', 'jigoshop'),
				'required' => true,
				'rel' => 'shipping_state'
			),
			array(
				'name' => 'shipping_postcode',
				'type' => 'postcode',
				'validate' => 'postcode',
				'format' => 'postcode',
				'label' => __('Postcode', 'jigoshop'),
				'placeholder' => __('Postcode', 'jigoshop'),
				'required' => true,
				'rel' => 'shipping_country',
			),
		);

		return $shipping_fields;
	}

	/** @deprecated Use jigoshop_checkout::render_shipping_dropdown() instead */
	public static function get_shipping_dropdown()
	{
		self::render_shipping_dropdown();
	}

	/**
	 * Renders table row with shipping dropdown.
	 *
	 * Used in checkout.
	 */
	public static function render_shipping_dropdown()
	{
		if (jigoshop_cart::needs_shipping()){
			/** @noinspection PhpUnusedLocalVariableInspection */
			jigoshop_get_template('checkout/shipping_dropdown.php');
		}
	}

	/**
	 *  Output the billing information block
	 */
	public function checkout_form_billing() {
		if ( jigoshop_cart::ship_to_billing_address_only() ) {
			echo '<h3>'.__('Billing &amp; Shipping', 'jigoshop').'</h3>';
		} else {
			echo '<h3>'.__('Billing Address', 'jigoshop').'</h3>';
		}

		// Billing Details
		do_action( 'jigoshop_before_billing_fields' );
		foreach ( $this->billing_fields as $field ) {
			$field = apply_filters( 'jigoshop_billing_field', $field );
			$this->field( $field );
		}

		// Registration Form
		if ( $this->show_signup ) {
			echo '<div class="checkout-signup" style="margin-top:20px;">';

			$guests_allowed = self::get_options()->get('jigoshop_enable_guest_checkout') == 'yes';
			$account_label = $guests_allowed
				? __('Would you like to create an account?', 'jigoshop')
				: __('You must check this to agree to creating an account', 'jigoshop'
			);

			echo '<p class="form-row"><input class="input-checkbox" id="create_account" ';

			if ( $this->get_value('create_account')) echo 'checked="checked" ';
			echo 'type="checkbox" name="create_account" /> <label for="create_account" class="checkbox">'.$account_label.'</label></p>';

			echo '<div class="create-account">';

			$field = array(
				'type' => 'text',
				'name' => 'account_username',
				'label' => __('Account Username', 'jigoshop'),
				'placeholder' => __('Username', 'jigoshop')
			);
			$this->field($field);
			$field = array(
				'type' => 'password',
				'name' => 'account_password',
				'label' => __('Account Password', 'jigoshop'),
				'placeholder' => __('Password', 'jigoshop'),
				'class' => array('form-row-first')
			);
			$this->field($field);
			$field = array(
				'type' => 'password',
				'name' => 'account_password_2',
				'label' => __('Account Password', 'jigoshop'),
				'placeholder' => __('Password again', 'jigoshop'),
				'class' => array('form-row-last'),
				'label_class' => array('hidden')
			);
			$this->field($field);

			echo '<p><small>'.__('Save time in the future and check the status of your orders by creating an account.', 'jigoshop').'</small></p></div>';
			echo '</div>';
		}
	}

	/**
	 * Outputs a form field
	 *
	 * @param array $args contains a list of args for showing the field, merged with defaults (below)
	 * @return string
	 */
	public function field($args)
	{
		$defaults = array(
			'type' => 'text',
			'name' => '',
			'label' => '',
			'placeholder' => '',
			'required' => false,
			'class' => array(),
			'label_class' => array(),
			'options' => array(),
			'selected' => '',
			'rel' => '',
			'echo' => true,
			'return' => false,
		);

		$args = wp_parse_args($args, $defaults);

		if($args['return']){
			$args['echo'] = false;
		}

		$required = '';
		$input_required = '';
		$after = '';

		if ($args['name'] == 'billing_state' || $args['name'] == 'shipping_state') {
			if (jigoshop_customer::has_valid_shipping_state()) {
				$args['required'] = false;
			}
		}
		if ($args['required']) {
			$required = ' <span class="required">*</span>';
			$input_required = ' input-required';
		}

		if (in_array('form-row-last', $args['class'])) {
			$after = '<div class="clear"></div>';
		}

		switch ($args['type']) {
			case 'country':
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.esc_attr(implode(' ', $args['label_class'])).'">'.$args['label'].$required.'</label>
					<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="country_to_state'.esc_attr($input_required).'" rel="'.esc_attr($args['rel']).'">';

				$countries = jigoshop_countries::get_allowed_countries();
				if (Jigoshop_Base::get_options()->get('jigoshop_default_country_for_customer') == -1) {
					$countries = array_merge(array(-1 => __('Select your country', 'jigoshop')), $countries);
				}

				foreach ($countries as $key => $value) {
					$field .= '<option value="'.esc_attr($key).'"';
					if ($this->get_value($args['name']) == $key) {
						$field .= ' selected="selected"';
					} elseif (!$this->get_value($args['name']) && jigoshop_customer::get_country() == $key) {
						$field .= ' selected="selected"';
					}
					$field .= '>'.__($value, 'jigoshop').'</option>';
				}

				$field .= '</select></p>'.$after;
				break;
			case 'state':
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>';

				$allowed_countries = Jigoshop_Base::get_options()->get('jigoshop_allowed_countries');
				$current_cc = $this->get_value($args['rel']);

				if (!$current_cc) {
					$current_cc = jigoshop_customer::get_country();
				}

				if ($allowed_countries === 'specific') {
					$specific_countries = Jigoshop_Base::get_options()->get('jigoshop_specific_allowed_countries');
					$base_cc = jigoshop_countries::get_base_country();
					if (!in_array($current_cc, $specific_countries)) {
						if (in_array($base_cc, $specific_countries)) {
							$current_cc = $base_cc;
						} else {
							$current_cc = array_shift($specific_countries);
						}
					}
				}

				$current_r = $this->get_value($args['name']);
				if (!$current_r) {
					$current_r = jigoshop_customer::get_state();
				}

				$states = jigoshop_countries::get_states($current_cc);
				$state_keys = array_keys($states);
				if (jigoshop_countries::country_has_states($current_cc) && !in_array($current_r, $state_keys)) {
					$base_r = jigoshop_countries::get_base_state();
					if (in_array($base_r, $state_keys)) {
						$current_r = $base_r;
					} else {
						$current_r = array_shift($state_keys);
					}
				}

				if (jigoshop_countries::country_has_states($current_cc)) {
					// Dropdown
					$field .= '<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="'.esc_attr($input_required).'"><option value="">'.__('Select a state&hellip;', 'jigoshop').'</option>';
					foreach ($states as $key => $value) {
						$field .= '<option value="'.esc_attr($key).'"';
						if ($current_r == $key) {
							$field .= ' selected="selected"';
						}
						$field .= '>'.__($value, 'jigoshop').'</option>';
					}
					$field .= '</select>';
				} else {
					// Input
					$field .= '<input type="text" class="input-text'.esc_attr($input_required).'" value="'.esc_attr($current_r).'" placeholder="'.__('State/Province', 'jigoshop').'" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" />';
				}

				$field .= '</p>'.$after;
				break;
			case 'postcode':
				$current_pc = $this->get_value($args['name']);
				if (!$current_pc) {
					$current_pc = $args['rel'] == 'shipping_country' ? jigoshop_customer::get_shipping_postcode() : jigoshop_customer::get_postcode();
				}

				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<input type="text" class="input-text'.esc_attr($input_required).'" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" value="'.esc_attr($current_pc).'" rel="'.esc_attr($args['rel']).'" />
				</p>'.$after;
				break;
			case 'textarea':
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<textarea name="'.esc_attr($args['name']).'" class="input-text'.esc_attr($input_required).'" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" cols="5" rows="2" rel="'.esc_attr($args['rel']).'">'.esc_textarea($this->get_value($args['name'])).'</textarea>
				</p>'.$after;
				break;
			case 'select':
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<select name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" class="input-text'.esc_attr($input_required).'" rel="'.esc_attr($args['rel']).'">';

				foreach ($args['options'] as $key => $value) {
					$field .= '<option value="'.esc_attr($key).'"';
					if (esc_attr($args['selected']) == $key) {
						$field .= ' selected="selected"';
					}
					$field .= '>'.__($value, 'jigoshop').'</option>';
				}

				$field .= '</select></p>'.$after;
				break;
			case 'text':
			case 'password':
				$field = '<p class="form-row '.implode(' ', $args['class']).'">
					<label for="'.esc_attr($args['name']).'" class="'.implode(' ', $args['label_class']).'">'.$args['label'].$required.'</label>
					<input type="'.$args['type'].'" class="input-text'.esc_attr($input_required).'" name="'.esc_attr($args['name']).'" id="'.esc_attr($args['name']).'" placeholder="'.$args['placeholder'].'" value="'.$this->get_value($args['name']).'" rel="'.esc_attr($args['rel']).'" />
				</p>'.$after;
				break;
			default :
				ob_start();
				do_action('jigoshop_display_checkout_field', $args['type'], $args, $this->get_value($args['name']));
				echo $after;
				$field = ob_get_clean();
				break;
		}

		if ($args['echo']) {
			echo $field;
		}

		return $field;
	}

	/**
	 *  Gets the value either from the posted data, or from the users meta data
	 */
	function get_value($input)
	{
		if (isset($this->posted[$input]) && !empty($this->posted[$input])) {
			return $this->posted[$input];
		} elseif (is_user_logged_in()) {
			$value = get_user_meta(get_current_user_id(), $input, true);
			if ($value) {
				return $value;
			}

			$current_user = wp_get_current_user();

			switch ($input) {
				case 'billing_email':
					return $current_user->user_email;
			}
		}

		return '';
	}

	/**
	 *  Output the shipping information block
	 */
	function checkout_form_shipping() {
		// Shipping Details
		if ( ! jigoshop_cart::ship_to_billing_address_only() && self::get_options()->get( 'jigoshop_calc_shipping') == 'yes' ) :
			$shiptobilling = ! $_POST ? apply_filters('shiptobilling_default', 1) : $this->get_value('shiptobilling');
			$shiptodisplay = self::get_options()->get('jigoshop_show_checkout_shipping_fields') == 'no' ? 'checked="checked"' : '';
			?>
			<p class="form-row" id="shiptobilling">
				<input class="input-checkbox" type="checkbox" name="shiptobilling" id="shiptobilling-checkbox" <?php if ($shiptobilling) : echo $shiptodisplay; endif; ?> />
				<label for="shiptobilling-checkbox" class="checkbox"><?php _e('Ship to billing address?', 'jigoshop'); ?></label>
			</p>
			<h3><?php _e('Shipping Address', 'jigoshop'); ?></h3>
			<div class="shipping-address">
				<?php do_action( 'jigoshop_before_shipping_fields' ); ?>
				<?php
					foreach ( $this->shipping_fields as $field ) :
						$field = apply_filters( 'jigoshop_shipping_field', $field );
						$this->field( $field );
					endforeach;
				?>
			</div>
		<?php elseif ( jigoshop_cart::ship_to_billing_address_only() ) : ?>
			<h3><?php _e('Notes/Comments', 'jigoshop'); ?></h3>
		<?php endif;

		$this->field(array(
			'type' => 'textarea',
			'class' => array('notes'),
			'name' => 'order_comments',
			'label' => __('Order Notes', 'jigoshop'),
			'placeholder' => __('Notes about your order.', 'jigoshop')
		));
	}

	/**
	 * Output the payment methods block
	 */
	public function checkout_form_payment_methods()
	{
		jigoshop_get_template('checkout/payment_methods.php');
	}

	/**
	 * Process the checkout after the confirm order button is pressed
	 */
	public function process_checkout()
	{
		if (!defined('JIGOSHOP_CHECKOUT')) {
			define('JIGOSHOP_CHECKOUT', true);
		}

		// Initialize cart
		jigoshop_cart::get_cart();
		jigoshop_cart::calculate_totals();

		if (isset($_POST) && $_POST && !isset($_POST['login'])) {
			jigoshop::verify_nonce('process_checkout');
			// this will fill in our $posted array with validated data
			self::validate_checkout();

			$gateway = jigoshop_payment_gateways::get_gateway($this->posted['payment_method']);
			if (self::process_gateway($gateway)) {
				$gateway->validate_fields();
			}

			do_action('jigoshop_after_checkout_validation', $this->posted, $_POST, sizeof(jigoshop::$errors));

			if(jigoshop::has_errors()){
				return false;
			}

			if (!isset($_POST['update_totals'])) {
				$user_id = get_current_user_id();

				// Create customer account and log them in
				if ($this->show_signup && !$user_id && $this->posted['create_account']) {
					$user_id = $this->create_user_account();

					if($user_id === 0){
						return false;
					}
				}

				$billing = array(
					'first_name' => $this->posted['billing_first_name'],
					'last_name' => $this->posted['billing_last_name'],
					'company' => $this->posted['billing_company'],
					'address_1' => $this->posted['billing_address_1'],
					'address_2' => $this->posted['billing_address_2'],
					'city' => $this->posted['billing_city'],
					'state' => $this->posted['billing_state'],
					'postcode' => $this->posted['billing_postcode'],
					'country' => $this->posted['billing_country'],
					'phone' => $this->posted['billing_phone'],
					'email' => $this->posted['billing_email'],
				);

				jigoshop_customer::set_country($billing['country']);
				jigoshop_customer::set_state($billing['state']);
				jigoshop_customer::set_postcode($billing['postcode']);

				if(isset($this->posted['billing_euvatno']) && $this->valid_euvatno){
					$billing['euvatno'] = $this->posted['billing_euvatno'];
					$billing['euvatno'] = str_replace(' ', '', $billing['euvatno']);

					// If country code is not provided - add one.
					if(strpos($billing['euvatno'], $billing['country']) === false){
						$billing['euvatno'] = $billing['country'].$billing['euvatno'];
					}
				}

				// Get shipping/billing
				if (!empty($this->posted['shiptobilling'])) {
					$shipping = $billing;
					unset($shipping['phone'], $shipping['email']);
				} elseif (jigoshop_shipping::is_enabled()) {
					$shipping = array(
						'first_name' => $this->posted['shipping_first_name'],
						'last_name' => $this->posted['shipping_last_name'],
						'company' => $this->posted['shipping_company'],
						'address_1' => $this->posted['shipping_address_1'],
						'address_2' => $this->posted['shipping_address_2'],
						'city' => $this->posted['shipping_city'],
						'state' => $this->posted['shipping_state'],
						'postcode' => $this->posted['shipping_postcode'],
						'country' => $this->posted['shipping_country'],
					);
				}

				jigoshop_customer::set_shipping_country($shipping['country']);
				jigoshop_customer::set_shipping_state($shipping['state']);
				jigoshop_customer::set_shipping_postcode($shipping['postcode']);

				// Update totals based on processed customer address
				jigoshop_cart::calculate_totals();

				// Save billing/shipping to user meta fields
				if ($user_id > 0) {
					foreach($billing as $field => $value)
					{
						update_user_meta($user_id, 'billing_'.$field, $value);
					}
					if(isset($shipping))
					{
						foreach($shipping as $field => $value)
						{
							update_user_meta($user_id, 'shipping_'.$field, $value);
						}
					}
				}

				// Order meta data
				$data = array();
				$applied_coupons = array_map(function($coupon){
					return JS_Coupons::get_coupon($coupon);
				}, jigoshop_cart::get_coupons());

				do_action('jigoshop_checkout_update_order_total', $this->posted);

				foreach($billing as $field => $value){
					$data['billing_'.$field] = $value;
				}

				if(isset($shipping)){
					foreach($shipping as $field => $value){
						$data['shipping_'.$field] = $value;
					}
				}

				$data['order_discount_coupons'] = $applied_coupons;
				$data['shipping_method'] = $this->posted['shipping_method'];
				$data['shipping_service'] = $this->posted['shipping_service'];
				$data['payment_method'] = $this->posted['payment_method'];
				$data['payment_method_title'] = $gateway->title;

				$data['order_subtotal'] = jigoshop_cart::get_subtotal();
				$data['order_discount_subtotal'] = jigoshop_cart::get_discount_subtotal();
				$data['order_shipping'] = jigoshop_cart::get_shipping_total();
				$data['order_discount'] = jigoshop_cart::get_total_discount(false);
				$data['order_tax'] = jigoshop_cart::get_taxes_as_string();
				$data['order_tax_no_shipping_tax'] = jigoshop_cart::get_total_cart_tax_without_shipping_tax();
				$data['order_tax_divisor'] = jigoshop_cart::get_tax_divisor();
				$data['order_shipping_tax'] = jigoshop_cart::get_shipping_tax();
				$data['order_total'] = jigoshop_cart::get_total(false);
				$data['order_total_prices_per_tax_class_ex_tax'] = jigoshop_cart::get_price_per_tax_class_ex_tax();

				if ($this->valid_euvatno) {
					$data['order_tax'] = '';
					$temp = jigoshop_cart::get_total_cart_tax_without_shipping_tax();
					$data['order_total'] -= $data['order_shipping_tax'] + $temp;
					$data['order_shipping_tax'] = 0;
				}

				// Cart items
				$order_items = array();
				foreach (jigoshop_cart::get_cart() as $values) {
					/** @var jigoshop_product $product */
					$product = $values['data'];

					// Check stock levels
					if (!$product->has_enough_stock($values['quantity'])) {
						jigoshop::add_error(sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $product->get_title()));
						if(self::get_options()->get('jigoshop_show_stock') == 'yes'){
							jigoshop::add_error(sprintf(__('We have only %d available at this time.', 'jigoshop'), $product->get_stock()));
						}
						break;
					}

					// Calc item tax to store
					$rates = $product->get_tax_destination_rate();
					$rates = current($rates);

					if (isset($rates['rate'])) {
						$rate = $rates['rate'];
					} else {
						$rate = 0.00;
					}

					if ($this->valid_euvatno) {
						$rate = 0.00;
					}

					$price_inc_tax = $product->get_price_with_tax();

					if (!empty($values['variation_id'])) {
						$product_id = $values['variation_id'];
					} else {
						$product_id = $values['product_id'];
					}

					$custom_products = (array)jigoshop_session::instance()->customized_products;
					$custom = isset($custom_products[$product_id]) ? $custom_products[$product_id] : '';
					if (!empty($custom)) {
						unset($custom_products[$product_id]);
						jigoshop_session::instance()->customized_products = $custom_products;
					}

					$order_items[] = apply_filters('new_order_item', array(
						'id' => $values['product_id'],
						'variation_id' => $values['variation_id'],
						'variation' => $values['variation'],
						'customization' => $custom,
						'name' => $product->get_title(),
						'qty' => (int)$values['quantity'],
						'cost' => $product->get_price_excluding_tax(),
						'cost_inc_tax' => $price_inc_tax,
						'taxrate' => $rate
					), $values);
				}

				if (jigoshop::has_errors()) {
					return false;
				}

				// Insert or update the post data
				$create_new_order = true;
				$order_data = array(
					'post_type' => 'shop_order',
					'post_title' => 'Order &ndash; '.date('F j, Y @ h:i A'),
					'post_status' => 'publish',
					'post_excerpt' => $this->posted['order_comments'],
					'post_author' => 1
				);
				$order_id = 0;

				if (isset(jigoshop_session::instance()->order_awaiting_payment) && jigoshop_session::instance()->order_awaiting_payment > 0) {
					$order_id = absint(jigoshop_session::instance()->order_awaiting_payment);
					$terms = wp_get_object_terms($order_id, 'shop_order_status', array('fields' => 'slugs'));
					$order_status = isset($terms[0]) ? $terms[0] : 'pending';

					// Resume the unpaid order if its pending
					if ($order_status == 'pending' || $order_status == 'failed') {
						$create_new_order = false;
						$order_data['ID'] = $order_id;
						wp_update_post($order_data);
					}
				}

				if ($create_new_order) {
					$order_id = wp_insert_post($order_data);
				}

				if (is_wp_error($order_id) || $order_id === 0) {
					jigoshop::add_error(__('Error: Unable to create order. Please try again.', 'jigoshop'));

					return false;
				}

				// Update post meta
				update_post_meta($order_id, 'order_data', $data);
				update_post_meta($order_id, 'order_key', uniqid('order_'));
				update_post_meta($order_id, 'customer_user', (int)$user_id);
				update_post_meta($order_id, 'order_items', $order_items);
				wp_set_object_terms($order_id, 'pending', 'shop_order_status');

				$order = new jigoshop_order($order_id);

				/* Coupon usage limit */
				foreach ($data['order_discount_coupons'] as $coupon) {
					$coupon_id = JS_Coupons::get_coupon_post_id($coupon['code']);
					if ($coupon_id !== false) {
						$usage_count = get_post_meta($coupon_id, 'usage', true);
						$usage_count = empty($usage_count) ? 1 : $usage_count + 1;
						update_post_meta($coupon_id, 'usage', $usage_count);
					}
				}

				if ($create_new_order) {
					do_action('jigoshop_new_order', $order_id);
				} else {
					do_action('jigoshop_resume_order', $order_id);
				}

				do_action('jigoshop_checkout_update_order_meta', $order_id, $this->posted);

				// can't just simply check needs_payment() here, as paypal may have force payment set to true
				if (self::process_gateway($gateway)) {
					// Store Order ID in session so it can be re-used after payment failure
					jigoshop_session::instance()->order_awaiting_payment = $order_id;

					// Process Payment
					$result = $gateway->process_payment($order_id);

					// Redirect to success/confirmation/payment page
					if ($result['result'] == 'success') {
						return $result;
					}

					return false;
				} else {
					// No payment was required for order
					$order->payment_complete();

					// Empty the Cart
					jigoshop_cart::empty_cart();

					// Redirect to success/confirmation/payment page
					$checkout_redirect = apply_filters('jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks'));
					return array(
						'result' => 'redirect',
						'redirect' => $checkout_redirect,
					);
				}
			}
		}

		return true;
	}

	/**
	 * Validate the checkout
	 */
	public function validate_checkout()
	{
		if (jigoshop_cart::is_empty()) {
			jigoshop::add_error(sprintf(__('Sorry, your session has expired. <a href="%s">Return to homepage &rarr;</a>', 'jigoshop'), home_url()));
		}

		// Process Discount Codes
		if (!empty($_POST['coupon_code'])) {
			$coupon = sanitize_title($_POST['coupon_code']);
			jigoshop_cart::add_discount($coupon);
		}

		foreach (jigoshop_cart::get_coupons() as $coupon) {
			jigoshop_cart::is_valid_coupon($coupon);
		}

		// Checkout fields
		$this->posted['shipping_method'] = '';
		$this->posted['shipping_service'] = '';
		if (isset($_POST['shipping_method'])) {
			$shipping_method = jigowatt_clean($_POST['shipping_method']);
			$shipping_data = explode(':', $shipping_method);
			$this->posted['shipping_method'] = $shipping_data[0];
			$this->posted['shipping_service'] = $shipping_data[1];
		}

		$this->posted['shiptobilling'] = isset($_POST['shiptobilling']) ? jigowatt_clean($_POST['shiptobilling']) : '';
		$this->posted['payment_method'] = isset($_POST['payment_method']) ? jigowatt_clean($_POST['payment_method']) : '';
		$this->posted['order_comments'] = isset($_POST['order_comments']) ? jigowatt_clean($_POST['order_comments']) : '';
		$this->posted['terms'] = isset($_POST['terms']) ? jigowatt_clean($_POST['terms']) : '';
		$this->posted['create_account'] = isset($_POST['create_account']) ? jigowatt_clean($_POST['create_account']) : '';
		$this->posted['account_username'] = isset($_POST['account_username']) ? jigowatt_clean($_POST['account_username']) : '';
		$this->posted['account_password'] = isset($_POST['account_password']) ? jigowatt_clean($_POST['account_password']) : '';
		$this->posted['account_password_2'] = isset($_POST['account_password_2']) ? jigowatt_clean($_POST['account_password_2']) : '';

		if (jigoshop_cart::get_total(false) == 0) {
			$this->posted['payment_method'] = 'no_payment';
		}

		// establish customer billing and shipping locations
		if (jigoshop_cart::ship_to_billing_address_only()) {
			$this->posted['shiptobilling'] = 'true';
		}

		$country = isset($_POST['billing_country']) ? jigowatt_clean($_POST['billing_country']) : '';
		$state = isset($_POST['billing_state']) ? jigowatt_clean($_POST['billing_state']) : '';
		$allowed_countries = Jigoshop_Base::get_options()->get('jigoshop_allowed_countries');

		if ($allowed_countries === 'specific') {
			$specific_countries = Jigoshop_Base::get_options()->get('jigoshop_specific_allowed_countries');
			if (!in_array($country, $specific_countries)) {
				jigoshop::add_error(__('Invalid billing country.', 'jigoshop'));

				return;
			}
		}

		if (jigoshop_countries::country_has_states($country)) {
			$states = jigoshop_countries::get_states($country);
			if (!in_array($state, array_keys($states))) {
				jigoshop::add_error(__('Invalid billing state.', 'jigoshop'));

				return;
			}
		}

		$postcode = isset($_POST['billing_postcode']) ? jigowatt_clean($_POST['billing_postcode']) : '';
		$ship_to_billing = Jigoshop_Base::get_options()->get('jigoshop_ship_to_billing_address_only') == 'yes';
		jigoshop_customer::set_location($country, $state, $postcode);
		if (Jigoshop_Base::get_options()->get('jigoshop_calc_shipping') == 'yes') {
			if ($ship_to_billing || !empty($_POST['shiptobilling'])) {
				jigoshop_customer::set_shipping_location($country, $state, $postcode);
			} else {
				$country = isset($_POST['shipping_country']) ? jigowatt_clean($_POST['shipping_country']) : '';
				$state = isset($_POST['shipping_state']) ? jigowatt_clean($_POST['shipping_state']) : '';
				$postcode = isset($_POST['shipping_postcode']) ? jigowatt_clean($_POST['shipping_postcode']) : '';

				if ($allowed_countries === 'specific') {
					$specific_countries = Jigoshop_Base::get_options()->get('jigoshop_specific_allowed_countries');
					if (!in_array($country, $specific_countries)) {
						jigoshop::add_error(__('Invalid shipping country.', 'jigoshop'));

						return;
					}
				}

				if (jigoshop_countries::country_has_states($country)) {
					$states = jigoshop_countries::get_states($country);
					if (!in_array($state, array_keys($states))) {
						jigoshop::add_error(__('Invalid shipping state.', 'jigoshop'));

						return;
					}
				}

				jigoshop_customer::set_shipping_location($country, $state, $postcode);
			}
		}

		// Billing Information
		foreach ($this->billing_fields as $field) {
			$field = apply_filters('jigoshop_billing_field', $field);
			$this->posted[$field['name']] = isset($_POST[$field['name']]) ? jigowatt_clean($_POST[$field['name']]) : '';

			// Format
			if (isset($field['format'])) {
				switch ($field['format']) {
					case 'postcode' :
						$this->posted[$field['name']] = strtolower(str_replace(' ', '', $this->posted[$field['name']]));
						break;
				}
			}

			// Required
			if ($field['name'] == 'billing_state' && jigoshop_customer::has_valid_shipping_state()) {
				$field['required'] = false;
			}

			if (isset($field['required']) && $field['required'] && empty($this->posted[$field['name']])) {
				jigoshop::add_error($field['label'].__(' (billing) is a required field.', 'jigoshop'));
			}

			if ($field['name'] == 'billing_euvatno') {
				$vatno = isset($this->posted['billing_euvatno']) ? $this->posted['billing_euvatno'] : '';
				$vatno = str_replace(' ', '', $vatno);
				$country = jigoshop_tax::get_customer_country();
				// strip any country code from the beginning of the number
				if (strpos($vatno, $country) === 0) {
					$vatno = substr($vatno, strlen($country));
				}

				if ($vatno != '') {
					$url = 'http://isvat.appspot.com/'.$country.'/'.$vatno.'/';
					$httpRequest = curl_init();
					curl_setopt($httpRequest, CURLOPT_FAILONERROR, true);
					curl_setopt($httpRequest, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($httpRequest, CURLOPT_HEADER, false);
					curl_setopt($httpRequest, CURLOPT_URL, $url);
					$result = curl_exec($httpRequest);
					curl_close($httpRequest);

					if ($result === 'false') {
						jigoshop_log('EU VAT validation error with URL: '.$url);
						jigoshop::add_error($field['label'].__(' (billing) is not a valid VAT Number.  Leave it blank to disable VAT validation. (VAT may be charged depending on your location)', 'jigoshop'));
					} else {
						$this->valid_euvatno = jigoshop_countries::get_base_country() != jigoshop_tax::get_customer_country() && jigoshop_countries::is_eu_country(jigoshop_tax::get_customer_country());
					}
				}
			}

			// Validation
			if (isset($field['validate']) && !empty($this->posted[$field['name']])) {
				switch ($field['validate']) {
					case 'phone' :
						if (!jigoshop_validation::is_phone($this->posted[$field['name']])) {
							jigoshop::add_error($field['label'].__(' (billing) is not a valid number.', 'jigoshop'));
						}
						break;
					case 'email' :
						if (!jigoshop_validation::is_email($this->posted[$field['name']])) {
							jigoshop::add_error($field['label'].__(' (billing) is not a valid email address.', 'jigoshop'));
						}
						break;
					case 'postcode' :
						if (!jigoshop_validation::is_postcode($this->posted[$field['name']], $_POST['billing_country'])) {
							jigoshop::add_error($field['label'].__(' (billing) is not a valid postcode/ZIP.', 'jigoshop'));
						} else {
							$this->posted[$field['name']] = jigoshop_validation::format_postcode($this->posted[$field['name']], $_POST['billing_country']);
						}
						break;
				}
			}
		}

		// Shipping Information
		if (jigoshop_shipping::is_enabled() && !jigoshop_cart::ship_to_billing_address_only() && empty($this->posted['shiptobilling'])) {
			foreach ($this->shipping_fields as $field) {
				$field = apply_filters('jigoshop_shipping_field', $field);

				if (isset($_POST[$field['name']])) {
					$this->posted[$field['name']] = jigowatt_clean($_POST[$field['name']]);
				} else {
					$this->posted[$field['name']] = '';
				}

				// Format
				if (isset($field['format'])) {
					switch ($field['format']) {
						case 'postcode' :
							$this->posted[$field['name']] = strtolower(str_replace(' ', '', $this->posted[$field['name']]));
							break;
					}
				}

				// Required
				if ($field['name'] == 'shipping_state' && jigoshop_customer::has_valid_shipping_state()) {
					$field['required'] = false;
				}
				if (isset($field['required']) && $field['required'] && empty($this->posted[$field['name']])) {
					jigoshop::add_error($field['label'].__(' (shipping) is a required field.', 'jigoshop'));
				}

				// Validation
				if (isset($field['validate']) && !empty($this->posted[$field['name']])) {
					switch ($field['validate']) {
						case 'postcode' :
							if (!jigoshop_validation::is_postcode($this->posted[$field['name']], $country)) {
								jigoshop::add_error($field['label'].__(' (shipping) is not a valid postcode/ZIP.', 'jigoshop'));
							} else {
								$this->posted[$field['name']] = jigoshop_validation::format_postcode($this->posted[$field['name']], $country);
							}
							break;
					}
				}
			}
		}

		if ($this->must_register && empty($this->posted['create_account'])) {
			jigoshop::add_error(__('Sorry, you must agree to creating an account', 'jigoshop'));
		}

		if ($this->must_register || (empty($user_id) && ($this->posted['create_account']))) {
			if (!$this->show_signup) {
				jigoshop::add_error(__('Sorry, the shop owner has disabled guest purchases.', 'jigoshop'));
			}
			if (empty($this->posted['account_username'])) {
				jigoshop::add_error(__('Please enter an account username.', 'jigoshop'));
			}
			if (empty($this->posted['account_password'])) {
				jigoshop::add_error(__('Please enter an account password.', 'jigoshop'));
			}
			if ($this->posted['account_password_2'] !== $this->posted['account_password']) {
				jigoshop::add_error(__('Passwords do not match.', 'jigoshop'));
			}

			// Check the username
			if (!validate_username($this->posted['account_username'])) {
				jigoshop::add_error(__('Invalid email/username.', 'jigoshop'));
			} elseif (username_exists($this->posted['account_username'])) {
				jigoshop::add_error(__('An account is already registered with that username. Please choose another.', 'jigoshop'));
			}

			// Check the e-mail address
			if (email_exists($this->posted['billing_email'])) {
				jigoshop::add_error(__('An account is already registered with your email address. Please login.', 'jigoshop'));
			};
		}

		// Terms
		if (!isset($_POST['update_totals']) && empty($this->posted['terms']) && jigoshop_get_page_id('terms') > 0) {
			jigoshop::add_error(__('You must accept our Terms &amp; Conditions.', 'jigoshop'));
		}

		if (jigoshop_cart::needs_shipping()) {
			// Shipping Method
			$available_methods = jigoshop_shipping::get_available_shipping_methods();
			if (!isset($available_methods[$this->posted['shipping_method']])) {
				jigoshop::add_error(__('Invalid shipping method.', 'jigoshop'));
			}
		}
	}

	/**
	 * This method makes sure we require payment for the particular gateway being used.
	 *
	 * @param jigoshop_payment_gateway $gateway the payment gateway
	 * that is being used during checkout
	 * @return boolean true when the gateway should be processed, otherwise false
	 * @since 1.2
	 */
	public static function process_gateway($gateway)
	{
		if ($gateway === null) {
			if (jigoshop_cart::$subtotal > 0) {
				jigoshop::add_error(__('Invalid payment method.', 'jigoshop'));
			}

			return false;
		}

		$shipping_total = jigoshop_cart::$shipping_total;
		if(self::get_options()->get('jigoshop_prices_include_tax') == 'yes'){
			$shipping_total += jigoshop_cart::$shipping_tax_total;
		}

		return $gateway->process_gateway(number_format(jigoshop_cart::$subtotal, 2, '.', ''), number_format($shipping_total, 2, '.', ''), number_format(jigoshop_cart::$discount_total, 2, '.', ''));
	}

	private function create_user_account()
	{
		$reg_errors = new WP_Error();
		do_action('register_post', $this->posted['billing_email'], $this->posted['billing_email'], $reg_errors);

		if ($reg_errors->get_error_code()) {
			jigoshop::add_error($reg_errors->get_error_message());
			return 0;
		}

		$user_pass = $this->posted['account_password'];
		$user_id = wp_create_user($this->posted['account_username'], $user_pass, $this->posted['billing_email']);

		if (!$user_id) {
			jigoshop::add_error(sprintf(
				__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', 'jigoshop'),
				self::get_options()->get('jigoshop_email')
			));
			return 0;
		}

		wp_update_user(array('ID' => $user_id, 'role' => 'customer', 'first_name' => $this->posted['billing_first_name'], 'last_name' => $this->posted['billing_last_name']));
		do_action('jigoshop_created_customer', $user_id);

		// send the user a confirmation and their login details
		if (apply_filters('jigoshop_new_user_notification', true, $user_id, $user_pass)) {
			wp_new_user_notification($user_id, $user_pass);
		}

		wp_set_auth_cookie($user_id, true, is_ssl());
		return $user_id;
	}
}
