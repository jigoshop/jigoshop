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
	jigoshop_emails::register_mail('admin_order_status_pending_to_waiting-for-payment', __('Order Pending to Waiting for Payment for admin'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('admin_order_status_on-hold_to_processing', __('Order On-Hold to Processing for admin'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('admin_order_status_completed', __('Order Completed for admin'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('admin_order_status_refunded', __('Order Refunded for admin'), get_order_email_arguments_description());

	jigoshop_emails::register_mail('customer_order_status_pending_to_processing', __('Order Pending to Processing for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_pending_to_completed', __('Order Pending to Completed for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_pending_to_on-hold', __('Order Pending to On-Hold for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_pending_to_waiting-for-payment', __('Order Pending to Waiting for Payment for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_on-hold_to_processing', __('Order On-Hold to Processing for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_completed', __('Order Completed for customer'), get_order_email_arguments_description());
	jigoshop_emails::register_mail('customer_order_status_refunded', __('Order Refunded for customer'), get_order_email_arguments_description());

	jigoshop_emails::register_mail('low_stock_notification', __('Low Stock Notification'), get_stock_email_arguments_description());
	jigoshop_emails::register_mail('no_stock_notification', __('No Stock Notification'), get_stock_email_arguments_description());
	jigoshop_emails::register_mail('product_on_backorder_notification', __('Backorder Notification'), array_merge(get_stock_email_arguments_description(), get_order_email_arguments_description(), array('amount' => __('Amount', 'jigoshop'))));
	jigoshop_emails::register_mail('send_customer_invoice', __('Send Customer Invoice'), get_order_email_arguments_description());
}, 999);

$jigoshopOrderEmailGenerator = function($action) {
	return function ($order_id) use ($action) {
		$options = Jigoshop_Base::get_options();
		$order = new jigoshop_order($order_id);
		jigoshop_emails::send_mail('admin_order_status_'.$action, get_order_email_arguments($order_id), $options->get('jigoshop_email'));
		jigoshop_emails::send_mail('customer_order_status_'.$action, get_order_email_arguments($order_id), $order->billing_email);
	};
};

add_action('order_status_pending_to_processing', $jigoshopOrderEmailGenerator('pending_to_processing'));
add_action('order_status_pending_to_completed', $jigoshopOrderEmailGenerator('pending_to_completed'));
add_action('order_status_pending_to_on-hold', $jigoshopOrderEmailGenerator('pending_to_on-hold'));
add_action('order_status_pending_to_waiting-for-payment', $jigoshopOrderEmailGenerator('pending_to_waiting-for-payment'));
add_action('order_status_on-hold_to_processing', $jigoshopOrderEmailGenerator('on-hold_to_processing'));
add_action('order_status_completed', $jigoshopOrderEmailGenerator('completed'));
add_action('order_status_refunded', $jigoshopOrderEmailGenerator('refunded'));

$jigoshopStockEmailGenerator = function($action){
	return function ($product) use ($action){
		$options = Jigoshop_Base::get_options();
		jigoshop_emails::send_mail($action, get_stock_email_arguments($product), $options->get('jigoshop_email'));
	};
};
add_action('jigoshop_low_stock_notification', $jigoshopStockEmailGenerator('low_stock_notification'));
add_action('jigoshop_no_stock_notification', $jigoshopStockEmailGenerator('no_stock_notification'));

add_action('jigoshop_product_on_backorder_notification', function ($order_id, $product, $amount){
	$options = Jigoshop_Base::get_options();
	jigoshop_emails::send_mail('product_on_backorder_notification', array_merge(get_order_email_arguments($order_id), get_stock_email_arguments($product), array('amount' => $amount)), $options->get('jigoshop_email'));
	if ($product->meta['backorders'][0] == 'notify') {
		$order = new jigoshop_order($order_id);
		jigoshop_emails::send_mail('product_on_backorder_notification', array_merge(get_order_email_arguments($order_id), get_stock_email_arguments($product), array('amount' => $amount)), $order->billing_email);
	}
}, 1, 3);

add_filter('downloadable_file_url', function($link){
	return '<a href="' .$link. '">' .$link. '</a>';
}, 10, 1);

function get_order_email_arguments($order_id)
{
	$options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($order_id);
	$inc_tax = ($options->get('jigoshop_calc_taxes') == 'no') || ($options->get('jigoshop_prices_include_tax') == 'yes');
	$can_show_links = ($order->status == 'completed' || $order->status == 'processing');
	$statuses = $order->get_order_statuses_and_names();

	$variables = array(
		'blog_name' => get_bloginfo('name'),
		'order_number' => $order->get_order_number(),
		'order_date' => date_i18n(get_option('date_format')),
		'order_status' => $statuses[$order->status],
		'shop_name' => $options->get('jigoshop_company_name'),
		'shop_address_1' => $options->get('jigoshop_address_1'),
		'shop_address_2' => $options->get('jigoshop_address_2'),
		'shop_tax_number' => $options->get('jigoshop_tax_number'),
		'shop_phone' => $options->get('jigoshop_company_phone'),
		'shop_email' => $options->get('jigoshop_company_email'),
		'customer_note' => $order->customer_note,
		'order_items' => jigoshop_get_order_items_list($order, $can_show_links, true, $inc_tax),//$order->email_order_items_list($can_show_links, true, $inc_tax),
		'order_taxes' => jigoshop_get_order_taxes_list($order),
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
		'is_bank_transfer' => $order->payment_method == 'bank_transfer' ? true : null,
		'is_cash_on_delivery' => $order->payment_method == 'cod' ? true : null,
		'is_cheque' => $order->payment_method == 'cheque' ? true : null,
		'bank_info' => str_replace(PHP_EOL, '', jigoshop_bank_transfer::get_bank_details()),
		'cheque_info' => str_replace(PHP_EOL, '', $options->get('jigoshop_cheque_description')),
		'billing_first_name' => $order->billing_first_name,
		'billing_last_name' => $order->billing_last_name,
		'billing_company' => $order->billing_company,
		'billing_euvatno' => $order->billing_euvatno,
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
	);

	if ($options->get('jigoshop_calc_taxes') == 'yes') {
		$variables['all_tax_classes'] = $variables['order_taxes'];
	} else {
		unset($variables['order_taxes']);
	}

	return apply_filters('jigoshop_order_email_variables', $variables, $order_id);
}

function get_order_email_arguments_description()
{
	return apply_filters('jigoshop_order_email_variables_description', array(
		'blog_name' => __('Blog Name', 'jigoshop'),
		'order_number' => __('Order Number', 'jigoshop'),
		'order_date' => __('Order Date', 'jigoshop'),
		'order_status' => __('Order Status', 'jigoshop'),
		'shop_name' => __('Shop Name', 'jigoshop'),
		'shop_address_1' => __('Shop Address part 1', 'jigoshop'),
		'shop_address_2' => __('Shop Address part 2', 'jigoshop'),
		'shop_tax_number' => __('Shop TaxNumber', 'jigoshop'),
		'shop_phone' => __('Shop_Phone', 'jigoshop'),
		'shop_email' => __('Shop Email', 'jigoshop'),
		'customer_note' => __('Customer Note', 'jigoshop'),
		'order_items' => __('Ordered Items', 'jigoshop'),
		'order_taxes' => __('Taxes of the order', 'jigoshop'),
		'subtotal' => __('Subtotal', 'jigoshop'),
		'shipping' => __('Shipping Price and Method', 'jigoshop'),
		'shipping_cost' => __('Shipping Cost', 'jigoshop'),
		'shipping_method' => __('Shipping Method', 'jigoshop'),
		'discount' => __('Discount Price', 'jigoshop'),
		'total_tax' => __('Total Tax', 'jigoshop'),
		'total' => __('Total Price', 'jigoshop'),
		'payment_method' => __('Payment Method Title', 'jigoshop'),
		'is_bank_transfer' => __('Is payment method Bank Transfer?', 'jigoshop'),
		'is_cash_on_delivery' => __('Is payment method Cash on Delivery?', 'jigoshop'),
		'is_cheque' => __('Is payment method Cheque?', 'jigoshop'),
		'is_local_pickup' => __('Is Local Pickup?', 'jigoshop'),
		'bank_info' => __('Company bank transfer details', 'jigoshop'),
		'cheque_info' => __('Company cheque details', 'jigoshop'),
		'checkout_url' => __('If order is pending, show checkout url', 'jigoshop'),
		'billing_first_name' => __('Billing First Name', 'jigoshop'),
		'billing_last_name' => __('Billing Last Name', 'jigoshop'),
		'billing_company' => __('Billing Company', 'jigoshop'),
		'billing_euvatno' => __('Billing EU Vat number', 'jigoshop'),
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
	));
}

/**
 * @param \jigoshop_product $product
 * @return array
 */
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
	$order = new jigoshop_order($order_id);
	jigoshop_emails::send_mail('send_customer_invoice', get_order_email_arguments($order_id), $order->billing_email);
}

/**
 * @param jigoshop_order $order
 * @param bool $show_links
 * @param bool $show_sku
 * @param bool $includes_tax
 * @return string
 */
function jigoshop_get_order_items_list($order, $show_links = false, $show_sku = false, $includes_tax = false)
{
	if (\Jigoshop_Base::get_options()->get('jigoshop_emails_html', 'no') == 'no') {
		return $order->email_order_items_list($show_links, $show_sku, $includes_tax);
	}

	$use_inc_tax = $includes_tax;
	if ($use_inc_tax) {
		foreach ($order->items as $item) {
			$use_inc_tax = ($item['cost_inc_tax'] >= 0);
			if (!$use_inc_tax) {
				break;
			}
		}
	}

	$path = locate_template(array('jigoshop/emails/items.php'));
	if (empty($path)) {
		$path = JIGOSHOP_DIR.'/templates/emails/items.php';
	}

	ob_start();
	include($path);

	return ob_get_clean();
}

/**
 * @param jigoshop_order $order
 * @return string
 */
function jigoshop_get_order_taxes_list($order)
{
	$path = locate_template(array('jigoshop/emails/taxes.php'));
	if (empty($path)) {
		$path = JIGOSHOP_DIR.'/templates/emails/taxes.php';
	}

	ob_start();
	include($path);

	return ob_get_clean();
}

add_action('jigoshop_install_emails', 'jigoshop_install_emails');

function jigoshop_install_emails()
{
	$default_emails = array(
		'new_order_admin_notification',
		'customer_order_status_pending_to_processing',
		'customer_order_status_pending_to_on-hold',
		'customer_order_status_pending_to_waiting-for-payment',
		'customer_order_status_on-hold_to_processing',
		'customer_order_status_completed',
		'customer_order_status_refunded',
		'send_customer_invoice',
		'low_stock_notification',
		'no_stock_notification',
		'product_on_backorder_notification'
	);
	$invoice = '
		[is_cheque]
			<p>'._x('We are waiting for your cheque before we can start processing this order.', 'emails', 'jigoshop').'</p>
			<p>[cheque_info]</p>
			<p>Total value: [total]</p>
		[else]
		[is_bank_transfer]
			<p>'._x('We are waiting for your payment before we can start processing this order.', 'emails', 'jigoshop').'</p>
			<h4>'._x('Bank details', 'emails', 'jigoshop').'</h4>
			[bank_info]
			<p>Total value: [total]</p>
		[else]
		[is_local_pickup]
		<h4>'._x('Your order is being prepared', 'emails', 'jigoshop').'</h4>
		<p>'._x('We are preparing your order, you will receive another email when we will be ready and awaiting for you to pick it up.', 'emails', 'jigoshop').'</p>
		[else]
		[is_cash_on_delivery]
		<h4>'._x('Order will be dispatched shortly', 'emails', 'jigoshop').'</h4>
		<p>'._x('Your order is being processed and will be dispatched to you as soon as possible. Please prepare exact change to pay when package arrives.', 'emails', 'jigoshop').'</p>
		[/is_cash_on_delivery]
		[/is_local_pickup]
		[/is_bank_transfer]
		[/is_cheque]
		<h4>'._x('Order [order_number] on [order_date]', 'emails', 'jigoshop').'</h4>
		<table class="cart" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th>'._x('Product', 'emails', 'jigoshop').'</th>
					<th>'._x('Quantity', 'emails', 'jigoshop').'</th>
					<th>'._x('Price', 'emails', 'jigoshop').'</th>
				</tr>
			</thead>
			<tbody>
				[order_items]
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2"><strong>'._x('Subtotal', 'emails', 'jigoshop').'</strong></td>
					<td>[subtotal]</td>
				</tr>
				<tr>
					<td colspan="2"><strong>'._x('Shipping via [shipping_method]', 'emails', 'jigoshop').'</strong></td>
					<td>[shipping_cost]</td>
				</tr>
				[order_taxes]
				<tr>
					<td colspan="2"><strong>'._x('Grand total', 'emails', 'jigoshop').'</strong></td>
					<td>[total]</td>
				</tr>
			</tfoot>
		</table>
		<h4>'._x('Customer details', 'emails', 'jigoshop').'</h4>
		<p>'._x('Email:', 'emails', 'jigoshop').' <a href="mailto:[billing_email]">[billing_email]</a></p>
		<p>'._x('Phone:', 'emails', 'jigoshop').' [billing_phone]</p>
		<table class="customer">
			<thead>
				<tr>
					<td><strong>'._x('Billing address', 'emails', 'jigoshop').'</strong></td>
					<td><strong>'._x('Shipping address', 'emails', 'jigoshop').'</strong></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						[billing_first_name] [billing_last_name]<br />
						[billing_address_1][billing_address_2], [value][/billing_address_2]<br />
						[billing_city], [billing_postcode]<br />
						[billing_state]<br />
						[billing_country]
					</td>
					<td>
						[shipping_first_name] [shipping_last_name]<br />
						[shipping_address_1][shipping_address_2], [value][/shipping_address_2]<br />
						[shipping_city], [shipping_postcode]<br />
						[shipping_state]<br />
						[shipping_country]
					</td>
				</tr>
			</tbody>
		</table>
		[customer_note]
		<h4>'._x('Customer note', 'emails', 'jigoshop').'</h4>
		<p>[value]</p>
		[/customer_note]
	';

	$title = '';
	$message = '';
	$post_title = '';
	foreach ($default_emails as $email) {
		switch ($email) {
			case 'new_order_admin_notification':
				$post_title = __('New order admin notification', 'jigoshop');
				$title = __('[[shop_name]] New Customer Order', 'jigoshop');
				$message = __('<p>You have received an order from [billing_first_name] [billing_last_name].</p><p>Current order status: <strong>[order_status]</strong></p>', 'jigoshop').$invoice;
				break;
			case 'customer_order_status_pending_to_on-hold':
				$post_title = __('Customer order status pending to on-hold', 'jigoshop');
				$title = __('[[shop_name]] Order Received', 'jigoshop');
				$message = __('<p>Thank you, we have received your order.</p>', 'jigoshop').$invoice;
				break;
			case 'customer_order_status_pending_to_waiting-for-payment':
				$post_title = __('Customer order status pending to waiting for payment', 'jigoshop');
				$title = __('[[shop_name]] Order Received - waiting for payment', 'jigoshop');
				$message = __('<p>Thank you, we have received your order.</p>', 'jigoshop').$invoice;
				break;
			case 'customer_order_status_pending_to_processing' :
				$post_title = __('Customer order status pending to processing', 'jigoshop');
				$title = __('[[shop_name]] Order Received', 'jigoshop');
				$message = __('<p>Thank you, we are now processing your order.<br/>', 'jigoshop').$invoice;
				break;
			case 'customer_order_status_on-hold_to_processing' :
				$post_title = __('Customer order status on-hold to processing', 'jigoshop');
				$title = __('[[shop_name]] Order Received', 'jigoshop');
				$message = __('<p>Thank you, we are now processing your order.<br/>', 'jigoshop').$invoice;
				break;
			case 'customer_order_status_completed' :
				$post_title = __('Customer order status completed', 'jigoshop');
				$title = __('[[shop_name]] Order Complete', 'jigoshop');
				$message = __('<p>Your order is complete.<br/>', 'jigoshop').$invoice;
				break;
			case 'customer_order_status_refunded' :
				$post_title = __('Customer order status refunded', 'jigoshop');
				$title = __('[[shop_name]] Order Refunded', 'jigoshop');
				$message = __('<p>Your order has been refunded.</p>', 'jigoshop').$invoice;
				break;
			case 'send_customer_invoice' :
				$post_title = __('Send customer invoice', 'jigoshop');
				$title = __('Invoice for Order: [order_number]', 'jigoshop');
				$message = $invoice;
				break;
			case 'low_stock_notification' :
				$post_title = __('Low stock notification', 'jigoshop');
				$title = __('[[shop_name]] Product low in stock', 'jigoshop');
				$message = __('<p>#[product_id] [product_name] ([sku]) is low in stock.</p>', 'jigoshop');
				break;
			case 'no_stock_notification' :
				$post_title = __('No stock notification', 'jigoshop');
				$title = __('[[shop_name]] Product out of stock', 'jigoshop');
				$message = __('<p>#[product_id] [product_name] ([sku]) is out of stock.</p>', 'jigoshop');
				break;
			case 'product_on_backorder_notification' :
				$post_title = __('Product on backorder notification', 'jigoshop');
				$title = __('[[shop_name]] Product Backorder on Order: [order_number].', 'jigoshop');
				$message = __('<p>#[product_id] [product_name] ([sku]) was found to be on backorder.</p>', 'jigoshop').$invoice;
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
				'admin_order_status_pending_to_on-hold',
				'admin_order_status_pending_to_waiting-for-payment',
			));
			update_post_meta($post_id, 'jigoshop_email_actions', array(
				'admin_order_status_pending_to_processing',
				'admin_order_status_pending_to_completed',
				'admin_order_status_pending_to_on-hold',
				'admin_order_status_pending_to_waiting-for-payment',
			));
		} else {
			jigoshop_emails::set_actions($post_id, array($email));
			update_post_meta($post_id, 'jigoshop_email_actions', array($email));
		}
	}

	\Jigoshop_Base::get_options()->set('jigoshop_emails_html', 'yes');
}
