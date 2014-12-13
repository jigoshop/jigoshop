<?php
/**
 * Jigoshop Emails
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

add_action('admin_init', function(){
	jigoshop_emails::register_mail('admin_order_status_pending_to_processing', __('Order Pending to Processing for admin'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('admin_order_status_pending_to_completed', __('Order Pending to Completed for admin'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('admin_order_status_pending_to_on-hold', __('Order Pending to On-Hold for admin'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_pending_to_on-hold', __('Order Pending to On-Hold for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_pending_to_processing', __('Order Pending to Processing for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_on-hold_to_processing', __('Order On-Hold to Processing for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_completed', __('Order Completed for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_refunded', __('Order Refunded for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('low_stock_notification', __('Low Stock Notification'), get_stock_email_arguments_description());
	jigoshop_emails::register_mail('no_stock_notification', __('No Stock Notification'), get_stock_email_arguments_description());
	jigoshop_emails::register_mail('product_on_backorder_notification', __('Backorder Notification'), array_merge(get_stock_email_arguments_description(), get_order_email_arguments_description(), array('amount' => __('Amount', 'jigoshop'))));
	jigoshop_emails::register_mail('send_customer_invoice', __('Send Customer Invoice'), get_order_email_arguments_description());
}, 999);

add_action('order_status_pending_to_processing', function ($order_id){
	$options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);
	jigoshop_emails::send_mail('admin_order_status_pending_to_processing', get_order_email_arguments($order_id), $options->get('jigoshop_email'));
	jigoshop_emails::send_mail('customer_order_status_pending_to_processing', get_order_email_arguments($order_id), $order->billing_email);
});
add_action('order_status_pending_to_completed', function ($order_id){
	$options = Jigoshop_Base::get_options();
	jigoshop_emails::send_mail('admin_order_status_pending_to_completed', get_order_email_arguments($order_id), $options->get('jigoshop_email'));
});
add_action('order_status_pending_to_on-hold', function ($order_id){
	$options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);
	jigoshop_emails::send_mail('admin_order_status_pending_to_on-hold', get_order_email_arguments($order_id), $options->get('jigoshop_email'));
	jigoshop_emails::send_mail('customer_order_status_pending_to_on-hold', get_order_email_arguments($order_id), $order->billing_email);
});
add_action('order_status_on-hold_to_processing', function ($order_id){
	$order = new jigoshop_order($order_id);
	jigoshop_emails::send_mail('customer_order_status_on-hold_to_processing', get_order_email_arguments($order_id), $order->billing_email);
});

add_action('order_status_completed', function ($order_id){
	$order = new jigoshop_order($order_id);
	jigoshop_emails::send_mail('customer_order_status_completed', get_order_email_arguments($order_id), $order->billing_email);
});

add_action('order_status_refunded', function ($order_id){
	$order = new jigoshop_order($order_id);
	jigoshop_emails::send_mail('customer_order_status_refunded', get_order_email_arguments($order_id), $order->billing_email);
});

add_action('jigoshop_low_stock_notification', function ($product){
	$options = Jigoshop_Base::get_options();
	jigoshop_emails::send_mail('low_stock_notification', get_stock_email_arguments($product), $options->get('jigoshop_email'));
});
add_action('jigoshop_no_stock_notification', function ($product){
	$options = Jigoshop_Base::get_options();
	jigoshop_emails::send_mail('no_stock_notification', get_stock_email_arguments($product), $options->get('jigoshop_email'));
});
add_action('jigoshop_product_on_backorder_notification', function ($order_id, $product, $amount){
	$options = Jigoshop_Base::get_options();
	jigoshop_emails::send_mail('product_on_backorder_notification', array_merge(get_order_email_arguments($order_id), get_stock_email_arguments($product), array('amount' => $amount)), $options->get('jigoshop_email'));
	if ($product->meta['backorders'][0] == 'notify') {
		$order = new jigoshop_order($order_id);
		jigoshop_emails::send_mail('product_on_backorder_notification', array_merge(get_order_email_arguments($order_id), get_stock_email_arguments($product), array('amount' => $amount)), $order->billing_email);
	}
}, 1, 3);

function get_order_email_arguments($order_id)
{
	$options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);
	$inc_tax = ($options->get('jigoshop_calc_taxes') == 'no') || ($options->get('jigoshop_prices_include_tax') == 'yes');

	return apply_filters('jigoshop_order_email_variables', array(
		'blog_name' => get_bloginfo('name'),
		'order_number' => $order->get_order_number(),
		'order_date' => date_i18n(get_option('date_format')),
		'shop_name' => $options->get('jigoshop_company_name'),
		'shop_address_1' => $options->get('jigoshop_address_1'),
		'shop_address_2' => $options->get('jigoshop_address_2'),
		'shop_tax_number' => $options->get('jigoshop_tax_number'),
		'shop_phone' => $options->get('jigoshop_company_phone'),
		'shop_email' => $options->get('jigoshop_company_email'),
		'customer_note' => $order->customer_note,
		'order_items' => $order->email_order_items_list(true, true, $inc_tax),
		'subtotal' => $order->get_subtotal_to_display(),
		'shipping' => $order->get_shipping_to_display(),
		'shipping_cost' => jigoshop_price($order->order_shipping),
		'shipping_method' => $order->shipping_service,
		'discount' => jigoshop_price($order->order_discount),
		'total_tax' => jigoshop_price($order->get_total_tax()),
		'total' => jigoshop_price($order->order_total),
		'is_local_pickup' => $order->shipping_method == 'local_pickup' ? true : null,
		'checkout_url' => $order->status == 'pending' ? $order->get_checkout_payment_url() : null,
		'payment_method' => $order->payment_method_title,
		'billing_first_name' => $order->billing_first_name,
		'billing_last_name' => $order->billing_last_name,
		'billing_company' => $order->billing_company,
		'billing_address_1' => $order->billing_address_1,
		'billing_address_2' => $order->billing_address_2,
		'billing_postcode' => $order->billing_postcode,
		'billing_city' => $order->billing_city,
		'billing_country' => jigoshop_countries::get_country($order->billing_country),
		'billing_state' => strlen($order->billing_state) == 2 ? jigoshop_countries::get_state($order->billing_country, $order->billing_state) : $order->billing_state,
		'billing_country_raw' => $order->billing_country,
		'billing state_raw' => $order->billing_state,
		'billing_email' => $order->billing_email,
		'billing_phone' => $order->billing_phone,
		'shipping_first_name' => $order->shipping_first_name,
		'shipping_last_name' => $order->shipping_last_name,
		'shipping_company' => $order->shipping_company,
		'shipping_address_1' => $order->shipping_address_1,
		'shipping_address_2' => $order->shipping_address_2,
		'shipping_postcode' => $order->shipping_postcode,
		'shipping_city' => $order->shipping_city,
		'shipping_country' => jigoshop_countries::get_country($order->shipping_country),
		'shipping_state' => strlen($order->shipping_state) == 2 ? jigoshop_countries::get_state($order->shipping_country, $order->shipping_state) : $order->shipping_state,
		'shipping_country_raw' => $order->shipping_country,
		'shipping_state_raw' => $order->shipping_state,
		'customer_note' => $order->customer_note,
	),$order_id);
}

function get_order_email_arguments_description()
{
	return apply_filters('jigoshop_order_email_variables_description', array(
		'blog_name' => __('Blog Name', 'jigoshop'),
		'order_number' => __('Order Number', 'jigoshop'),
		'order_date' => __('Order Date', 'jigoshop'),
		'shop_name' => __('Shop Name', 'jigoshop'),
		'shop_address_1' => __('Shop Address part 1', 'jigoshop'),
		'shop_address_2' => __('Shop Address part 2', 'jigoshop'),
		'shop_tax_number' => __('Shop TaxNumber', 'jigoshop'),
		'shop_phone' => __('Shop_Phone', 'jigoshop'),
		'shop_email' => __('Shop Email', 'jigoshop'),
		'customer_note' => __('Customer Note', 'jigoshop'),
		'order_items' => __('Ordered Items', 'jigoshop'),
		'subtotal' => __('Subtotal', 'jigoshop'),
		'shipping' => __('Shipping Price and Method', 'jigoshop'),
		'shipping_cost' => __('Shipping Cost', 'jigoshop'),
		'shipping_method' => __('Shipping Method', 'jigoshop'),
		'discount' => __('Discount Price', 'jigoshop'),
		'total_tax' => __('Total Tax', 'jigoshop'),
		'total' => __('Total Price', 'jigoshop'),
		'payment_method' => __('Payment Method Title', 'jigoshop'),
		'is_local_pickup' => __('Is Local Pickup?', 'jigoshop'),
		'checkout_url' => __('If order is pending, show checkout url', 'jigoshop'),
		'billing_first_name' => __('Billing First Name', 'jigoshop'),
		'billing_last_name' => __('Billing Last Name', 'jigoshop'),
		'billing_company' => __('Billing Company', 'jigoshop'),
		'billing_address_1' => __('Billing Address part 1', 'jigoshop'),
		'billing_address_2' => __('Billing Address part 2', 'jigoshop'),
		'billing_postcode' => __('Billing Postcode', 'jigoshop'),
		'billing_city' => __('Billing City', 'jigoshop'),
		'billing_country' => __('Billing Country', 'jigoshop'),
		'billing_state' => __('Billing State', 'jigoshop'),
		'billing_country_raw' => __('Raw Billing Country', 'jigoshop'),
		'billing state_raw' => __('Raw Billing State', 'jigoshop'),
		'billing_email' => __('Billing Email', 'jigoshop'),
		'billing_phone' => __('Billing Phone    ', 'jigoshop'),
		'shipping_first_name' => __('Shipping First Name', 'jigoshop'),
		'shipping_last_name' => __('Shipping Last Name', 'jigoshop'),
		'shipping_company' => __('Shipping Company', 'jigoshop'),
		'shipping_address_1' => __('Shipping Address part 1', 'jigoshop'),
		'shipping_address_2' => __('Shipping_Address part 2', 'jigoshop'),
		'shipping_postcode' => __('Shipping Postcode', 'jigoshop'),
		'shipping_city' => __('Shipping City', 'jigoshop'),
		'shipping_country' => __('Shipping Country', 'jigoshop'),
		'shipping_state' => __('Shipping State', 'jigoshop'),
		'shipping_country_raw' => __('Raw Shipping Country', 'jigoshop'),
		'shipping_state_raw' => __('Raw Shipping State', 'jigoshop'),
		'customer_note' => __('Customer Note', 'jigoshop'),
	));
}

function get_stock_email_arguments($product)
{
	$options = Jigoshop_Base::get_options();
	return array(
		'blog_name' => get_bloginfo('name'),
		'shop_name' => $options->get('jigoshop_company_name'),
		'shop_address_1' => $options->get('jigoshop_address_1'),
		'shop_address_2' => $options->get('jigoshop_address_2'),
		'shop_tax_number' => $options->get('jigoshop_tax_number'),
		'shop_phone' => $options->get('jigoshop_company_phone'),
		'shop_email' => $options->get('jigoshop_company_email'),
		'product_id' => $product->id,
		'product_name' => $product->get_title(),
		'sku' => $product->sku,
	);
}

function get_stock_email_arguments_description()
{
	return array(
		'blog_name' => __('Blog Name', 'jigoshop'),
		'shop_name' => __('Shop Name', 'jigoshop'),
		'shop_address_1' => __('Shop Address part 1', 'jigoshop'),
		'shop_address_2' => __('Shop Address part 2', 'jigoshop'),
		'shop_tax_number' => __('Shop TaxNumber', 'jigoshop'),
		'shop_phone' => __('Shop_Phone', 'jigoshop'),
		'shop_email' => __('Shop Email', 'jigoshop'),
		'product_id' => __('Product ID', 'jigoshop'),
		'product_name' => __('Product Name', 'jigoshop'),
		'sku' => __('SKU', 'jigoshop'),
	);
}

function jigoshop_send_customer_invoice($order_id)
{
	$options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);
	jigoshop_emails::send_mail('send_customer_invoice', get_order_email_arguments($order_id), $order->billing_email);
}

add_action('jigoshop_install_emails', 'install_emails');

function install_emails()
{
	$default_emails = array(
		'new_order_admin_notification',
		'customer_order_status_pending_to_processing',
		'customer_order_status_pending_to_on-hold',
		'customer_order_status_on-hold_to_processing',
		'customer_order_status_completed',
		'customer_order_status_refunded',
		'send_customer_invoice',
		'low_stock_notification',
		'no_stock_notification',
		'product_on_backorder_notification'
	);
	$invoice = '==============================<wbr />==============================
		Order details:
		<span class="il">ORDER</span> [order_number]                                              Date: [order_date]
		==============================<wbr />==============================

		[order_items]

		Subtotal:                     [subtotal]
		Shipping:                     [shipping_cost] via [shipping_method]
		Total:                        [total]

		------------------------------<wbr />------------------------------<wbr />--------------------
		CUSTOMER DETAILS
		------------------------------<wbr />------------------------------<wbr />--------------------
		Email:                        <a href="mailto:[billing_email]">[billing_email]</a>
		Tel:                          [billing_phone]

		------------------------------<wbr />------------------------------<wbr />--------------------
		BILLING ADDRESS
		------------------------------<wbr />------------------------------<wbr />--------------------
		[billing_first_name] [billing_last_name]
		[billing_address_1], [billing_address_2], [billing_city]
		[billing_state], [billing_country], [billing_postcode]

		------------------------------<wbr />------------------------------<wbr />--------------------
		SHIPPING ADDRESS
		------------------------------<wbr />------------------------------<wbr />--------------------
		[shipping_first_name] [shipping_last_name]
		[shipping_address_1], [shipping_address_2], [shipping_city]
		[shipping_state], [shipping_country], [shipping_postcode]
		[customer_note]
		------------------------------<wbr />------------------------------<wbr />--------------------
		CUSTOMER NOTE
		------------------------------<wbr />------------------------------<wbr />--------------------
		[value][/customer_note]';

	$title = '';
	$message = '';
	$post_title = '';
	foreach ($default_emails as $email) {
		switch ($email) {
			case 'new_order_admin_notification':
				$post_title = 'New order admin notification';
				$title = '[[shop_name]] New Customer Order - [order_number]';
				$message = 'You have received an order from [billing_first_name] [billing_last_name]. Their order is as follows:<br/>'.$invoice;
				break;
			case 'customer_order_status_pending_to_on-hold':
				$post_title = 'Customer order status pending to on-hold';
				$title = '[[shop_name]] Order Received';
				$message = 'Thank you, we have received your order. Your order\'s details are below:<br/>'.$invoice;
				break;
			case 'customer_order_status_pending_to_processing' :
				$post_title = 'Customer order status pending to processing';
				$title = '[[shop_name]] Order Received';
				$message = 'Thank you, we are now processing your order. Your order\'s details are below:<br/>'.$invoice;
				break;
			case 'customer_order_status_on-hold_to_processing' :
				$post_title = 'Customer order status on-hold to processing';
				$title = '[[shop_name]] Order Received';
				$message = 'Thank you, we are now processing your order. Your order\'s details are below:<br/>'.$invoice;
				break;
			case 'customer_order_status_completed' :
				$post_title = 'Customer order status completed';
				$title = '[[shop_name]] Order Complete';
				$message = 'Your order is complete. Your order\'s details are below:<br/>'.$invoice;
				break;
			case 'customer_order_status_refunded' :
				$post_title = 'Customer order status refunded';
				$title = '[[shop_name]] Order Refunded';
				$message = 'Your order has been refunded. Your order\'s details are below:<br/>'.$invoice;
				break;
			case 'send_customer_invoice' :
				$post_title = 'Send customer invoice';
				$title = 'Invoice for Order: [order_number]';
				$message = $invoice;
				break;
			case 'low_stock_notification' :
				$post_title = 'Low stock notification';
				$title = '[[shop_name]] Product low in stock';
				$message = '#[product_id] [product_name] ([sku]) is low in stock.';
				break;
			case 'no_stock_notification' :
				$post_title = 'No stock notification';
				$title = '[[shop_name]] Product out of stock';
				$message = '#[product_id] [product_name] ([sku]) is out of stock.';
				break;
			case 'product_on_backorder_notification' :
				$post_title = 'Product on backorder notification';
				$title = '[[shop_name]] Product Backorder on Order: [order_number].';
				$message = '#[product_id] [product_name] ([sku]) was found to be on backorder.<br/>'.$invoice;
				break;
		}
		$post_data = array(
			'post_content' => $message,
			'post_title' => $post_title,
			'post_status' => 'publish',
			'post_type' => 'shop_email',
			'post_author' => 1,
			'ping_status' => 'closed',
			'comment_status' => 'closed',
		);
		$post_id = wp_insert_post($post_data);
		update_post_meta($post_id, 'jigoshop_email_subject', $title);
		if ($email == 'new_order_admin_notification') {
			jigoshop_emails::set_actions($post_id, array(
				'admin_order_status_pending_to_processing',
				'admin_order_status_pending_to_completed',
				'admin_order_status_pending_to_on-hold'
			));
			update_post_meta($post_id, 'jigoshop_email_actions', array(
				'admin_order_status_pending_to_processing',
				'admin_order_status_pending_to_completed',
				'admin_order_status_pending_to_on-hold'
			));
		} else {
			jigoshop_emails::set_actions($post_id, array($email));
			update_post_meta($post_id, 'jigoshop_email_actions', array($email));
		}
	}
}
