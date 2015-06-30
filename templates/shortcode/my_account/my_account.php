<?php
/**
 * @var $options Jigoshop_Options Options container.
 * @var $recent_orders int Number of recent orders to show.
 */
?>
<?php jigoshop::show_messages(); ?>
<?php if (is_user_logged_in()): ?>
<p><?php echo sprintf( __('Hello, <strong>%s</strong>. From your account dashboard you can view your recent orders, manage your shipping and billing addresses and <a href="%s">change your password</a>.', 'jigoshop'), $current_user->display_name, apply_filters('jigoshop_get_change_password_page_id', get_permalink(jigoshop_get_page_id('change_password')))); ?></p>

<?php do_action('jigoshop_before_my_account'); ?>

<?php if ($downloads = jigoshop_customer::get_downloadable_products()) : ?>
	<h2><?php _e('Available downloads', 'jigoshop'); ?></h2>
	<ul class="digital-downloads">
		<?php foreach ($downloads as $download) : ?>
			<li><?php if (is_numeric($download['downloads_remaining'])) : ?><span class="count"><?php echo $download['downloads_remaining'] . _n(' download Remaining', ' downloads Remaining', 'jigoshop'); ?></span><?php endif; ?> <a href="<?php echo esc_url( $download['download_url'] ); ?>"><?php echo $download['download_name']; ?></a></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<h2><?php _e('Recent Orders', 'jigoshop'); ?></h2>
<table class="shop_table my_account_orders">
	<thead>
	<tr>
		<th><span class="nobr"><?php _e('#', 'jigoshop'); ?></span></th>
		<th><span class="nobr"><?php _e('Date', 'jigoshop'); ?></span></th>
		<?php if ( $options->get( 'jigoshop_calc_shipping' ) == 'yes' ) : ?>
			<th><span class="nobr"><?php _e('Ship to', 'jigoshop'); ?></span></th>
		<?php endif; ?>
		<th><span class="nobr"><?php _e('Total', 'jigoshop'); ?></span></th>
		<th colspan="2"><span class="nobr"><?php _e('Status', 'jigoshop'); ?></span></th>
		<?php do_action('jigoshop_my_account_orders_thead'); ?>
	</tr>
	</thead>
	<tbody><?php
	$orders = new jigoshop_orders();
	$orders->get_customer_orders(get_current_user_id(), $recent_orders);
	if ($orders->orders) foreach ($orders->orders as $order):
		/** @var $order jigoshop_order */
		if ($order->status=='pending') {
			foreach ($order->items as $item) {
				$_product = $order->get_product_from_item( $item );
				$temp = new jigoshop_product( $_product->ID );
				if ($temp->managing_stock() && (!$temp->is_in_stock() || !$temp->has_enough_stock($item['qty']))) {
					$order->cancel_order( sprintf(__("Product - %s - is now out of stock -- Canceling Order", 'jigoshop'), $_product->get_title() ) );
					ob_get_clean();
					wp_safe_redirect(apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id('myaccount'))));
					exit;
				}
			}
		}
		?><tr class="order">
		<td><?php echo $order->get_order_number(); ?></td>
		<td><time title="<?php echo esc_attr( date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($order->order_date)) ); ?>"><?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($order->order_date)); ?></time></td>
		<?php if ( $options->get( 'jigoshop_calc_shipping' ) == 'yes' ) : ?>
			<td><address>
					<?php if ($order->formatted_shipping_address) echo $order->formatted_shipping_address; else echo '&ndash;'; ?>
				</address></td>
		<?php endif; ?>
		<td><?php echo apply_filters( 'jigoshop_display_order_total', jigoshop_price($order->order_total), $order); ?></td>
		<td class="nobr"><?php _e($order->status, 'jigoshop'); ?></td>
		<td class="nobr alignright">
			<?php if ($order->status=='pending'): ?>
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e('Pay', 'jigoshop'); ?></a>
				<a href="<?php echo esc_url( $order->get_cancel_order_url() ); ?>" class="button cancel"><?php _e('Cancel', 'jigoshop'); ?></a>
			<?php endif; ?>
			<a href="<?php echo esc_url( add_query_arg('order', $order->id, apply_filters('jigoshop_get_view_order_page_id', get_permalink(jigoshop_get_page_id('view_order')))) ); ?>" class="button"><?php _e('View', 'jigoshop'); ?></a>
		</td>
		<?php do_action('jigoshop_my_account_orders_tbody', $order); ?>
		</tr><?php
	endforeach;
	?></tbody>
</table>

<h2><?php _e('My Addresses', 'jigoshop'); ?></h2>
<p><?php _e('The following addresses will be used on the checkout page by default.', 'jigoshop'); ?></p>
<div class="col2-set addresses">
	<div class="col-1">
		<header class="title">
			<h3><?php _e('Billing Address', 'jigoshop'); ?></h3>
			<a href="<?php echo esc_url(add_query_arg('address', 'billing', apply_filters('jigoshop_get_edit_address_page_id', get_permalink(jigoshop_get_page_id('edit_address'))))); ?>" class="edit"><?php _e('Edit', 'jigoshop'); ?></a>
		</header>
		<address>
			<?php
			$country = get_user_meta(get_current_user_id(), 'billing_country', true);
			$country = jigoshop_countries::has_country($country) ? jigoshop_countries::get_country($country) : '';
			$address = array(
				get_user_meta(get_current_user_id(), 'billing_first_name', true).' '.get_user_meta(get_current_user_id(), 'billing_last_name', true),
				get_user_meta(get_current_user_id(), 'billing_company', true),
				get_user_meta(get_current_user_id(), 'billing_euvatno', true),
				get_user_meta(get_current_user_id(), 'billing_address_1', true),
				get_user_meta(get_current_user_id(), 'billing_address_2', true),
				get_user_meta(get_current_user_id(), 'billing_city', true),
				get_user_meta(get_current_user_id(), 'billing_state', true),
				get_user_meta(get_current_user_id(), 'billing_postcode', true),
				$country,
			);

			$address = array_map('trim', $address);
			$formatted_address = implode(', ', array_filter($address));

			if (!$formatted_address) {
				_e('You have not set up a billing address yet.', 'jigoshop');
			} else {
				echo $formatted_address;
			}
			?>
		</address>
	</div>
	<div class="col-2">
		<header class="title">
			<h3><?php _e('Shipping Address', 'jigoshop'); ?></h3>
			<a href="<?php echo esc_url( add_query_arg('address', 'shipping', apply_filters('jigoshop_get_edit_address_page_id', get_permalink(jigoshop_get_page_id('edit_address')))) ); ?>" class="edit"><?php _e('Edit', 'jigoshop'); ?></a>
		</header>
		<address>
			<?php
			$country = get_user_meta(get_current_user_id(), 'shipping_country', true);
			$country = jigoshop_countries::has_country($country) ? jigoshop_countries::get_country($country) : '';
			$address = array(
				get_user_meta(get_current_user_id(), 'shipping_first_name', true).' '.get_user_meta(get_current_user_id(), 'shipping_last_name', true),
				get_user_meta(get_current_user_id(), 'shipping_company', true),
				get_user_meta(get_current_user_id(), 'shipping_address_1', true),
				get_user_meta(get_current_user_id(), 'shipping_address_2', true),
				get_user_meta(get_current_user_id(), 'shipping_city', true),
				get_user_meta(get_current_user_id(), 'shipping_state', true),
				get_user_meta(get_current_user_id(), 'shipping_postcode', true),
				$country,
			);

			$address = array_map('trim', $address);
			$formatted_address = implode(', ', array_filter($address));

			if (!$formatted_address) {
				_e('You have not set up a shipping address yet.', 'jigoshop');
			} else {
				echo $formatted_address;
			}
			?>
		</address>
	</div>
</div>
<?php
do_action('jigoshop_after_my_account');
else :
jigoshop_login_form();
endif;
