<?php
/**
 * Jigoshop Emails
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
/**
 * Hooks for emails
 * */
add_action('jigoshop_low_stock_notification', 'jigoshop_low_stock_notification');
add_action('jigoshop_no_stock_notification', 'jigoshop_no_stock_notification');
add_action('jigoshop_product_on_backorder_notification', 'jigoshop_product_on_backorder_notification', 1, 3);


/**
 * New order notification email template
 * */
add_action('order_status_pending_to_processing', 'jigoshop_new_order_notification');
add_action('order_status_pending_to_completed', 'jigoshop_new_order_notification');
add_action('order_status_pending_to_on-hold', 'jigoshop_new_order_notification');

function jigoshop_new_order_notification($order_id) {

	$jigoshop_options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);

	$subject = html_entity_decode(sprintf(__('[%s] New Customer Order (%s)', 'jigoshop'), get_bloginfo('name'), $order->get_order_number()), ENT_QUOTES, 'UTF-8');

	ob_start();

	echo __("You have received an order from ", 'jigoshop') . $order->billing_first_name . ' ' . $order->billing_last_name . __(". Their order is as follows:", 'jigoshop') . PHP_EOL . PHP_EOL;

	add_header_info($order);

	add_order_totals($order, false, true);

	add_customer_details($order);

	add_billing_address_details($order);

	add_shipping_address_details($order);

	$message = ob_get_clean();

	$message = apply_filters('jigoshop_change_new_order_email_contents', $message, $order);
	$message = html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8');

	wp_mail($jigoshop_options->get_option('jigoshop_email'), $subject, $message, "From: " . $jigoshop_options->get_option('jigoshop_email') . "\r\n");
}

/**
 * Processing order notification email template
 * */
add_action('order_status_pending_to_processing', 'jigoshop_processing_order_customer_notification');
add_action('order_status_pending_to_on-hold', 'jigoshop_processing_order_customer_notification');

function jigoshop_processing_order_customer_notification($order_id) {

	$jigoshop_options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);

	$subject = html_entity_decode('[' . get_bloginfo('name') . '] ' . __('Order Received', 'jigoshop'), ENT_QUOTES, 'UTF-8');

	ob_start();
	echo __("Thank you, we are now processing your order. Your order's details are below:", 'jigoshop') . PHP_EOL . PHP_EOL;

	add_header_info($order);

	add_order_totals($order, false, true);

	if (strtolower($order->payment_method) == "bank_transfer") :

		echo add_email_separator( '-' ) . PHP_EOL;
		echo __('BANK PAYMENT DETAILS', 'jigoshop') . PHP_EOL;
		echo add_email_separator( '-' ) . PHP_EOL;

		echo jigoshop_bank_transfer::get_bank_details();

		echo PHP_EOL;

		do_action('jigoshop_after_email_bank_payment_details', $order->id);

	endif;

	add_customer_details($order);

	add_billing_address_details($order);

	add_shipping_address_details($order);

	$message = ob_get_clean();

	$message = apply_filters('jigoshop_change_processing_order_email_contents', $message, $order);
	$message = html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8');

	wp_mail($order->billing_email, $subject, $message, "From: " . $jigoshop_options->get_option('jigoshop_email') . "\r\n");
}

/**
 * Completed order notification email template - this one includes download links for downloadable products
 * */
add_action('order_status_completed', 'jigoshop_completed_order_customer_notification');

function jigoshop_completed_order_customer_notification($order_id) {

	$jigoshop_options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);

	$subject = html_entity_decode('[' . get_bloginfo('name') . '] ' . __('Order Complete', 'jigoshop'), ENT_QUOTES, 'UTF-8');

	ob_start();
	echo __("Your order is complete. Your order's details are below:", 'jigoshop') . PHP_EOL . PHP_EOL;

	add_header_info($order);

	$download_links =  apply_filters('jigoshop_download_links_on_completed',true);
	add_order_totals($order, true, true);

	add_customer_details($order);

	add_billing_address_details($order);

	add_shipping_address_details($order);

	$message = ob_get_clean();


	$message = apply_filters('jigoshop_change_completed_order_email_contents', $message, $order);
	$message = html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8');
	$message = apply_filters('jigoshop_completed_order_customer_notification_mail_message', $message);

	wp_mail($order->billing_email, $subject, $message, "From: " . $jigoshop_options->get_option('jigoshop_email') . "\r\n");
}

/**
 * Refunded order notification email template - this one does not include download links for downloadable products
 * */
add_action('order_status_refunded', 'jigoshop_refunded_order_customer_notification');

function jigoshop_refunded_order_customer_notification($order_id) {

	$jigoshop_options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);

	$subject = html_entity_decode('[' . get_bloginfo('name') . '] ' . __('Order Refunded', 'jigoshop'), ENT_QUOTES, 'UTF-8');

	ob_start();
	echo __("Your order has been refunded. Your order's details are below:", 'jigoshop') . PHP_EOL . PHP_EOL;

	add_header_info($order);

	add_order_totals($order, false, true);

	add_customer_details($order);

	add_billing_address_details($order);

	add_shipping_address_details($order);

	$message = ob_get_clean();

	$message = apply_filters('jigoshop_change_refunded_email_message', $message, $order);
	$message = html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8');
	$message = apply_filters('jigoshop_refunded_order_customer_notification_mail_message', $message);

	wp_mail($order->billing_email, $subject, $message, "From: " . $jigoshop_options->get_option('jigoshop_email') . "\r\n");
}

/**
 * Customer invoice for an order.
 *
 * Displays link for payment if the order is marked pending.
 * Includes download link if order is completed.
 * */
function jigoshop_send_customer_invoice($order_id) {

	$jigoshop_options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);

	$subject = html_entity_decode('[' . get_bloginfo('name') . '] ' . sprintf(__('Invoice for Order %s', 'jigoshop'), $order->get_order_number()), ENT_QUOTES, 'UTF-8');

	$customer_message = '';
	if ($order->status == 'pending') :
		$customer_message = sprintf(__("An order has been created for you on &quot;%s&quot;. To pay for this order please use the following link: %s", 'jigoshop') . PHP_EOL . PHP_EOL, get_bloginfo('name'), $order->get_checkout_payment_url());
	endif;

	ob_start();
	add_header_info($order);

	if ($order->status == 'completed') :
		$download_links = apply_filters('jigoshop_download_links_on_invoice',true);
		add_order_totals($order, true, true);
	else :
		add_order_totals($order, false, true);
	endif;

	$message = ob_get_clean();

	$message = apply_filters('jigoshop_change_pay_order_email_contents', $message, $order);
	$customer_message = html_entity_decode(strip_tags($customer_message . $message), ENT_QUOTES, 'UTF-8');

	wp_mail($order->billing_email, $subject, $customer_message, "From: " . $jigoshop_options->get_option('jigoshop_email') . "\r\n");
}

function add_header_info($order) {

	echo add_email_separator( '=' ) . PHP_EOL;
	add_company_information();
	
	$info = __('ORDER ', 'jigoshop') . $order->get_order_number();
	$date = __('Date: ','jigoshop') . date_i18n( get_option('date_format') );
	$info .= add_padding_to_email_lines( 80 - strlen( $date ) - strlen( $info ) );
	$info .= $date;
	echo $info . PHP_EOL;
	echo add_email_separator( '=' ) . PHP_EOL;

}

function add_email_separator( $char ) {
	$sep = '';
	for ( $i = 0 ; $i < 80 ; $i++ ) {
		$sep .= $char;
	}
	return $sep;
}

function add_padding_to_email_lines( $amount ) {
	$padding = '';
	for ( $i = 0 ; $i < $amount ; $i++ ) {
		$padding .= ' ';
	}
	return $padding;
}

function add_company_information() {

	$jigoshop_options = Jigoshop_Base::get_options();
	$add_eol = false;

	if ($jigoshop_options->get_option('jigoshop_company_name')) :
		echo $jigoshop_options->get_option('jigoshop_company_name') . PHP_EOL;
		$add_eol = true;
	endif;

	if ($jigoshop_options->get_option('jigoshop_address_line1')) :
		$add_eol = true;
		echo $jigoshop_options->get_option('jigoshop_address_line1') . PHP_EOL;
		if ($jigoshop_options->get_option('jigoshop_address_line2')) :
			echo $jigoshop_options->get_option('jigoshop_address_line2') . PHP_EOL;
		endif;
	endif;

	if ($jigoshop_options->get_option('jigoshop_company_phone')) :
		$add_eol = true;
		echo $jigoshop_options->get_option('jigoshop_company_phone') . PHP_EOL;
	endif;

	if ($jigoshop_options->get_option('jigoshop_company_email')) :
		$add_eol = true;
		echo '<a href="mailto:' . $jigoshop_options->get_option('jigoshop_company_email') . '">' . $jigoshop_options->get_option('jigoshop_company_email') . '</a>' . PHP_EOL;
	endif;

	if ($add_eol) echo PHP_EOL;

}

function add_order_totals($order, $show_download_links, $show_sku) {

	do_action('jigoshop_before_email_order_info', $order->id);  
  
	$jigoshop_options = Jigoshop_Base::get_options();
	$inc_tax = ($jigoshop_options->get_option('jigoshop_calc_taxes') == 'no')||($jigoshop_options->get_option('jigoshop_prices_include_tax') == 'yes');
	
	echo PHP_EOL;
	echo $order->email_order_items_list($show_download_links, $show_sku, $inc_tax);

	if ( $order->customer_note ) {
		echo PHP_EOL . __('Note:', 'jigoshop') . $order->customer_note . PHP_EOL;
	}

	if (   ( $jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax() )
		|| ( $jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0) ) {
		
		echo PHP_EOL;
		$info = __('Retail Price:', 'jigoshop');
		$info .= add_padding_to_email_lines( 30 - strlen( $info ) );
		$info .= html_entity_decode($order->get_subtotal_to_display(), ENT_QUOTES, 'UTF-8');
		echo $info . PHP_EOL;

	} else {
		
		echo PHP_EOL;
		$info = __('Subtotal:', 'jigoshop');
		$info .= add_padding_to_email_lines( 30 - strlen( $info ) );
		$info .= html_entity_decode($order->get_subtotal_to_display(), ENT_QUOTES, 'UTF-8');
		echo $info . PHP_EOL;
		
	}
	
	if ( $order->order_shipping > 0 ) {
		$info = __('Shipping:', 'jigoshop');
		$info .= add_padding_to_email_lines( 30 - strlen( $info ) );
		$info .= html_entity_decode($order->get_shipping_to_display(), ENT_QUOTES, 'UTF-8');
		echo $info . PHP_EOL;
		
	}
	
	do_action('jigoshop_email_order_professing_fee_info', $order->id);
	
	if ( $jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0 ) {
		$info = __('Discount:', 'jigoshop');
		$info .= add_padding_to_email_lines( 30 - strlen( $info ) );
		$info .= html_entity_decode(jigoshop_price($order->order_discount), ENT_QUOTES, 'UTF-8');
		echo $info . PHP_EOL;
		
	}
	
// 	if (   ($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax())
// 		|| ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0)) {
// 		
// 		$info = __('Subtotal:', 'jigoshop');
// 		$info .= add_padding_to_email_lines( 30 - strlen( $info ) );
// 		$info .= html_entity_decode(jigoshop_price($order->order_discount_subtotal), ENT_QUOTES, 'UTF-8');
// 		echo $info . PHP_EOL;
// 
// 	}
	
	if ( $jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes') {
		foreach ($order->get_tax_classes() as $tax_class) {
			if ($order->show_tax_entry($tax_class)) {
			
				$info = $order->get_tax_class_for_display($tax_class) . ' (' . (float) $order->get_tax_rate($tax_class) . '%):';
				$info .= add_padding_to_email_lines( 30 - strlen( $info ) );
				$info .= html_entity_decode($order->get_tax_amount($tax_class), ENT_QUOTES, 'UTF-8');
				echo $info . PHP_EOL;

			}
		}
	}
	
	if ( $jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'no' && $order->order_discount > 0 ) {
		
		$info = __('Discount:', 'jigoshop');
		$info .= add_padding_to_email_lines( 30 - strlen( $info ) );
		$info .= html_entity_decode(jigoshop_price($order->order_discount), ENT_QUOTES, 'UTF-8');
		echo $info . PHP_EOL;

	}
	
	$method = $order->payment_method_title <> '' ? ucwords($order->payment_method_title) : __("Free",'jigoshop');
	$info = __('Total:', 'jigoshop');
	$info .= add_padding_to_email_lines( 30 - strlen( $info ) );
	$info .= html_entity_decode(jigoshop_price($order->order_total), ENT_QUOTES, 'UTF-8');
	$info .= ' - ' . __('via', 'jigoshop') . ' ' . $method;
	echo $info . PHP_EOL . PHP_EOL;

	if ($jigoshop_options->get_option('jigoshop_calc_taxes') && $jigoshop_options->get_option('jigoshop_tax_number')) :
		echo $jigoshop_options->get_option('jigoshop_tax_number') . PHP_EOL . PHP_EOL;
	endif;

	do_action('jigoshop_after_email_order_info', $order->id);

}

function add_customer_details($order) {

	echo add_email_separator( '-' ) . PHP_EOL;
	echo __('CUSTOMER DETAILS', 'jigoshop') . PHP_EOL;
	echo add_email_separator( '-' ) . PHP_EOL;

	if ($order->billing_email) {
		$temp = __('Email:', 'jigoshop');
		echo $temp . add_padding_to_email_lines( 30 - strlen( $temp ) ) . $order->billing_email . PHP_EOL;
	}
	if ($order->billing_phone) {
		$temp = __('Tel:', 'jigoshop');
		echo $temp . add_padding_to_email_lines( 30 - strlen( $temp ) ) . $order->billing_phone . PHP_EOL;
	}
	if ( $order->billing_euvatno ) {
		$temp = __('EU VAT Number:', 'jigoshop');
		echo $temp . add_padding_to_email_lines( 30 - strlen( $temp ) ) . $order->billing_euvatno . PHP_EOL;
	}
	echo PHP_EOL;

	do_action('jigoshop_after_email_customer_details', $order->id);

}

function add_billing_address_details($order) {

	echo add_email_separator( '-' ) . PHP_EOL;
	echo __('BILLING ADDRESS', 'jigoshop') . PHP_EOL;
	echo add_email_separator( '-' ) . PHP_EOL;

	echo $order->billing_first_name . ' ' . $order->billing_last_name . PHP_EOL;
	if ($order->billing_company)
		echo $order->billing_company . PHP_EOL;
	echo $order->formatted_billing_address . PHP_EOL . PHP_EOL;
	
	do_action('jigoshop_after_email_billing_address', $order->id);

}

function add_shipping_address_details($order) {

	echo add_email_separator( '-' ) . PHP_EOL;
	echo __('SHIPPING ADDRESS', 'jigoshop') . PHP_EOL;
	echo add_email_separator( '-' ) . PHP_EOL;

	if ( $order->shipping_method != 'local_pickup' ) {

		echo $order->shipping_first_name . ' ' . $order->shipping_last_name . PHP_EOL;
		if ($order->shipping_company) echo $order->shipping_company . PHP_EOL;
		echo $order->formatted_shipping_address . PHP_EOL . PHP_EOL;

		echo __('Shipping: ','jigoshop') . html_entity_decode(ucwords($order->shipping_service), ENT_QUOTES, 'UTF-8') . PHP_EOL . PHP_EOL;

		do_action('jigoshop_after_email_shipping_address', $order->id);

	} else {

		echo __('To be picked up by:', 'jigoshop') . PHP_EOL;
		echo $order->shipping_first_name . ' ' . $order->shipping_last_name . PHP_EOL;
		if ($order->shipping_company) echo $order->shipping_company . PHP_EOL;
		echo PHP_EOL;
		echo __('At location:', 'jigoshop') . PHP_EOL;
		echo add_company_information() . PHP_EOL . PHP_EOL;

	}

}

/**
 * Low stock notification email
 * */
function jigoshop_low_stock_notification($_product) {
	$jigoshop_options = Jigoshop_Base::get_options();
	$subject = html_entity_decode('[' . get_bloginfo('name') . '] ' . __('Product low in stock', 'jigoshop'), ENT_QUOTES, 'UTF-8');
	$message = '#' . $_product->id . ' ' . $_product->get_title() . ' (' . $_product->sku . ') ' . __('is low in stock.', 'jigoshop');
	$message = wordwrap(html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8'), 70);
	wp_mail($jigoshop_options->get_option('jigoshop_email'), $subject, $message, "From: " . $jigoshop_options->get_option('jigoshop_email') . "\r\n");
}

/**
 * No stock notification email
 * */
function jigoshop_no_stock_notification($_product) {
	$jigoshop_options = Jigoshop_Base::get_options();
	$subject = html_entity_decode('[' . get_bloginfo('name') . '] ' . __('Product out of stock', 'jigoshop'), ENT_QUOTES, 'UTF-8');
	$message = '#' . $_product->id . ' ' . $_product->get_title() . ' (' . $_product->sku . ') ' . __('is out of stock.', 'jigoshop');
	$message = wordwrap(html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8'), 70);
	wp_mail($jigoshop_options->get_option('jigoshop_email'), $subject, $message, "From: " . $jigoshop_options->get_option('jigoshop_email') . "\r\n");
}

/**
 * Backorder notification emails
 * an email is sent to the admin notifying which product is backordered with an amount needed to fill the order
 * an email -may- be sent to the customer notifying them of the same
 * if sent, an email is sent to the customer for each item backordered in the order
 *
 * @param string $order_id - the System Order number (ID)
 * @param string $product - the Product ID on backorder
 * @param string $amount - the count of the product needed to fill the order
 * */
function jigoshop_product_on_backorder_notification($order_id, $_product, $amount) {
	$jigoshop_options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);

	// notify the admin
	$subject = html_entity_decode('[' . get_bloginfo('name') . '] ' . sprintf(__('Product Backorder on Order %s', 'jigoshop'), $order->get_order_number()), ENT_QUOTES, 'UTF-8');
	$message = sprintf(__("%s units of #%s %s (#%s) are needed to fill Order %s.", 'jigoshop'), abs($amount), $_product->id, $_product->get_title(), $_product->sku, $order->get_order_number());
	$message = wordwrap(html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8'), 70);
	wp_mail($jigoshop_options->get_option('jigoshop_email'), $subject, $message, "From: " . $jigoshop_options->get_option('jigoshop_email') . "\r\n");

	// notify the customer if required
	if ($_product->meta['backorders'][0] == 'notify') :

		$subject = html_entity_decode('[' . get_bloginfo('name') . '] ' . sprintf(__('Product Backorder on Order %s', 'jigoshop'), $order->get_order_number()), ENT_QUOTES, 'UTF-8');

		ob_start();
		echo sprintf(__("Thank you for your Order %s. Unfortunately, the following item was found to be on backorder.", 'jigoshop'), $order->get_order_number()) . PHP_EOL . PHP_EOL;

		add_header_info($order);

		echo sprintf(__("%d units of #%d %s (#%s) have been backordered.", 'jigoshop'), abs($amount), $_product->id, $_product->get_title(), $_product->sku);

		echo PHP_EOL . PHP_EOL;
		if ($order->customer_note) :
			echo PHP_EOL . __('Note:', 'jigoshop') . $order->customer_note . PHP_EOL;
		endif;

		do_action('jigoshop_after_email_order_info', $order->id);

		add_customer_details($order);

		add_billing_address_details($order);

		add_shipping_address_details($order);

		$message = ob_get_clean();
		$message = html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8');

		wp_mail($order->billing_email, $subject, $message, "From: " . $jigoshop_options->get_option('jigoshop_email') . "\r\n");
	endif;
}
