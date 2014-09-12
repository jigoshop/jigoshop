<?php

/**
 * No payment gateway.
 */
class no_payment extends jigoshop_payment_gateway
{
	public function __construct()
	{
		$this->id = 'no_payment';
		$this->title = __('No payment required', 'jigoshop');
	}

	public function is_available()
	{
		return !is_jigoshop_single_page(JIGOSHOP_PAY);
	}

	/**
	 * provides functionality to tell checkout if
	 * the gateway should be processed or not. If false, the gateway will not be
	 * processed, otherwise the gateway will be processed.
	 *
	 * @param $subtotal
	 * @param $shipping_total
	 * @param int $discount
	 * @return boolean defaults to needs_payment from cart class. If overridden, the gateway will provide details as to when it should or shouldn't be processed.
	 */
	public function process_gateway($subtotal, $shipping_total, $discount = 0)
	{
		return false;
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param $order_id int Order ID to process.
	 * @return array
	 */
	public function process_payment($order_id)
	{
		return true;
	}
}

/**
 * Add the gateway to Jigoshop (only to be used on frontend for free orders!)
 */
add_filter('jigoshop_payment_gateways', function($methods){
	if ((is_admin() && !is_ajax())) {
		return $methods;
	}

	$methods[] = 'no_payment';
	return $methods;
});
