<?php

use Jigoshop\Frontend\Page\Checkout;
use Jigoshop\Integration;

class jigoshop_checkout extends Jigoshop_Base
{
	private static $instance;
	public $posted;
	public $billing_fields;
	public $shipping_fields;
	private $must_register = true;
	private $show_signup = false;

	protected function __construct()
	{
		$options = Integration::getOptions();
		$this->must_register = !$options->get('shopping.guest_purchases') && !is_user_logged_in();
		$this->show_signup = $options->get('shopping.allow_registration') && !is_user_logged_in();

		add_filter('jigoshop\checkout\billing_fields', function($fields){
			$fields = apply_filters('jigoshop_billing_fields', $fields);
			// TODO: Properly "translate" old to new fields
			return $fields;
		});
		add_filter('jigoshop\checkout\shipping_fields', function($fields){
			$fields = apply_filters('jigoshop_shipping_fields', $fields);
			// TODO: Properly "translate" old to new fields
			return $fields;
		});
	}

	/**
	 * @return array List of billing fields.
	 */
	public static function get_billing_fields()
	{
		$cart = Integration::getCart();
		return Checkout::getDefaultBillingFields($cart->getCustomer()->getBillingAddress());
	}

	/**
	 * @return array List of shipping fields.
	 */
	public static function get_shipping_fields()
	{
		$cart = Integration::getCart();
		return Checkout::getDefaultShippingFields($cart->getCustomer()->getShippingAddress());
	}

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

	public static function get_shipping_dropdown()
	{
		_deprecated_function('get_shipping_dropdown', '2.0');
	}

	public static function render_shipping_dropdown()
	{
		_deprecated_function('render_shipping_dropdown', '2.0');
	}

	public function __clone()
	{
		trigger_error("Cloning Singleton's is not allowed.", E_USER_ERROR);
	}

	public function __wakeup()
	{
		trigger_error("Unserializing Singleton's is not allowed.", E_USER_ERROR);
	}

	/**
	 *  Output the billing information block
	 */
	public function checkout_form_billing()
	{
		_deprecated_function('checkout_form_billing', '2.0');
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

		if ($args['return']) {
			$args['echo'] = false;
		}

		// TODO: Translate old field into new one

		ob_start();
		\Jigoshop\Helper\Forms::field($args['type'], $args);
		$result = ob_end_clean();

		if ($args['echo']) {
			echo $result;
		}

		return $result;
	}

	function get_value($input)
	{
		_deprecated_function('get_value', '2.0');
		return '';
	}

	function checkout_form_shipping()
	{
		_deprecated_function('checkout_form_shipping', '2.0');
	}

	public function checkout_form_payment_methods()
	{
		_deprecated_function('checkout_form_payment_methods', '2.0');
	}

	public function process_checkout()
	{
		_deprecated_function('process_checkout', '2.0');
	}

	public function validate_checkout()
	{
		_deprecated_function('validate_checkout', '2.0');
	}

	public static function process_gateway($gateway)
	{
		_deprecated_function('process_gateway', '2.0');
	}
}
