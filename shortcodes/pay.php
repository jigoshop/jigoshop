<?php
/**
 * Payment page shortcode
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

function get_jigoshop_pay( $atts ) {
	return jigoshop_shortcode_wrapper('jigoshop_pay', $atts);
}

/**
 * Outputs the pay page - payment gateways can hook in here to show payment forms etc
 **/
function jigoshop_pay() {

	if ( isset($_GET['pay_for_order']) && isset($_GET['order']) && isset($_GET['order_id']) ) :

		// Pay for existing order
		$order_key = urldecode( $_GET['order'] );
		$order_id = (int) $_GET['order_id'];
		$order = new jigoshop_order( $order_id );

		if ($order->id == $order_id && $order->order_key == $order_key && $order->status=='pending') :

			// Set customer location to order location
			if ($order->billing_country) jigoshop_customer::set_country( $order->billing_country );
			if ($order->billing_state) jigoshop_customer::set_state( $order->billing_state );
			if ($order->billing_postcode) jigoshop_customer::set_postcode( $order->billing_postcode );

			// Pay form was posted - process payment
			if (isset($_POST['pay']) && jigoshop::verify_nonce('pay')) :

				// Update payment method
				if ($order->order_total > 0 ) :
					$payment_method 			= jigowatt_clean($_POST['payment_method']);
					$data 						= (array) maybe_unserialize( get_post_meta( $order_id, 'order_data', true ) );
					$data['payment_method']		= $payment_method;
					update_post_meta( $order_id, 'order_data', $data );

					$available_gateways = jigoshop_payment_gateways::get_available_payment_gateways();

					$result = $available_gateways[$payment_method]->process_payment( $order_id );

					// Redirect to success/confirmation/payment page
					if ($result['result']=='success') :
						wp_safe_redirect( $result['redirect'] );
						exit;
					endif;
				else :

					// No payment was required for order
					$order->payment_complete();
					// filter redirect page
					$checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks') );
					wp_safe_redirect( get_permalink( $checkout_redirect ) );
					exit;

				endif;

			endif;

			// Show messages
			jigoshop::show_messages();

			// Show form
			jigoshop_pay_for_existing_order( $order );

		elseif ($order->status!='pending') :

			jigoshop::add_error( __('Your order has already been paid for. Please contact us if you need assistance.', 'jigoshop') );

			jigoshop::show_messages();

		else :

			jigoshop::add_error( __('Invalid order.', 'jigoshop') );

			jigoshop::show_messages();

		endif;

	else :

		// Pay for order after checkout step
		if (isset($_GET['order'])) $order_id = $_GET['order']; else $order_id = 0;
		if (isset($_GET['key'])) $order_key = $_GET['key']; else $order_key = '';

		if ($order_id > 0) :

			$order = new jigoshop_order( $order_id );

			if ($order->order_key == $order_key && $order->status=='pending') :

				?>
				<ul class="order_details">
					<li class="order">
						<?php _e('Order:', 'jigoshop'); ?>
						<strong><?php echo $order->get_order_number(); ?></strong>
					</li>
					<li class="date">
						<?php _e('Date:', 'jigoshop'); ?>
						<strong><?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($order->order_date)); ?></strong>
					</li>
					<li class="total">
						<?php _e('Total:', 'jigoshop'); ?>
						<strong><?php echo jigoshop_price($order->order_total); ?></strong>
					</li>
					<li class="method">
						<?php _e('Payment method:', 'jigoshop'); ?>
						<strong><?php
							$gateways = jigoshop_payment_gateways::payment_gateways();
							if (isset($gateways[$order->payment_method])) echo $gateways[$order->payment_method]->title;
							else echo $order->payment_method;
						?></strong>
					</li>
				</ul>

				<?php do_action( 'receipt_' . $order->payment_method, $order_id ); ?>

				<div class="clear"></div>
				<?php

			else :

				wp_safe_redirect( apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id('myaccount'))) );
				exit;

			endif;

		else :

			wp_safe_redirect( apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id('myaccount'))) );
			exit;

		endif;

	endif;
}

/**
 * Outputs the payment page when a user comes to pay from a link (for an existing/past created order)
 **/
function jigoshop_pay_for_existing_order( $pay_for_order ) {

	global $order;

	$order = $pay_for_order;

	jigoshop_get_template('checkout/pay_for_order.php');

}