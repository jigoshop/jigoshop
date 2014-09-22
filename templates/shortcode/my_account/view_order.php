<?php
/**
 * @var $order jigoshop_order Order to display.
 * @var $options Jigoshop_Options Options container.
 */
do_action('jigoshop_before_order_summary_details', $order->id);
?>
<h2><?php echo sprintf(__('Order <mark>%s</mark> made on <mark>%s</mark>.', 'jigoshop'), $order->get_order_number(), date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($order->order_date))); ?></h2>
<h3><?php echo sprintf(__('Order status: <mark class="%s">%s</mark>', 'jigoshop'), sanitize_title($order->status), __($order->status, 'jigoshop')); ?></h3>
<?php do_action('jigoshop_tracking_details_info', $order); ?>

<h2><?php _e('Order Details', 'jigoshop'); ?></h2>
<table class="shop_table">
	<thead>
	<tr>
		<th><?php _e('ID/SKU', 'jigoshop'); ?></th>
		<th><?php _e('Product', 'jigoshop'); ?></th>
		<th><?php _e('Qty', 'jigoshop'); ?></th>
		<th><?php _e('Totals', 'jigoshop'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<?php if (($options->get('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax()) || ($options->get('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0)): ?>
			<td colspan="3"><strong><?php _e('Retail Price', 'jigoshop'); ?></strong></td>
		<?php else: ?>
			<td colspan="3"><strong><?php _e('Subtotal', 'jigoshop'); ?></strong></td>
		<?php endif; ?>
		<td><strong><?php echo $order->get_subtotal_to_display(); ?></strong></td>
	</tr>
	<?php if ($order->order_shipping > 0): ?>
		<tr>
			<td colspan="3"><?php _e('Shipping', 'jigoshop'); ?></td>
			<td><?php echo $order->get_shipping_to_display(); ?></small></td>
		</tr>
	<?php endif; ?>
	<?php do_action('jigoshop_processing_fee_after_shipping'); ?>
	<?php if ($options->get('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0): ?>
		<tr class="discount">
		<td colspan="3"><?php _e('Discount', 'jigoshop'); ?></td>
		<td>-<?php echo jigoshop_price($order->order_discount); ?></td>
		</tr>
	<?php endif; ?>
	<?php if (($options->get('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax())
		|| ($options->get('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0)): ?>
		<tr>
			<td colspan="3"><strong><?php _e('Subtotal', 'jigoshop'); ?></strong></td>
			<td><strong><?php echo jigoshop_price($order->order_discount_subtotal); ?></strong></td>
		</tr>
	<?php endif; ?>
	<?php if ($options->get('jigoshop_calc_taxes') == 'yes'): ?>
		<?php foreach ($order->get_tax_classes() as $tax_class): ?>
			<?php if ($order->show_tax_entry($tax_class)): ?>
				<tr>
					<td colspan="3"><?php echo $order->get_tax_class_for_display($tax_class).' ('.(float)$order->get_tax_rate($tax_class).'%):'; ?></td>
					<td><?php echo $order->get_tax_amount($tax_class) ?></td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ($options->get('jigoshop_tax_after_coupon') == 'no' && $order->order_discount > 0): ?>
		<tr class="discount">
			<td colspan="3"><?php _e('Discount', 'jigoshop'); ?></td>
			<td>-<?php echo jigoshop_price($order->order_discount); ?></td>
		</tr>
	<?php endif; ?>
	<tr>
		<td colspan="3"><strong><?php _e('Grand Total', 'jigoshop'); ?></strong></td>
		<td><strong><?php echo jigoshop_price($order->order_total); ?></strong></td>
	</tr>
	<?php if ($order->customer_note): ?>
		<tr>
			<td><strong><?php _e('Note:', 'jigoshop'); ?></strong></td>
			<td colspan="3" style="text-align: left;"><?php echo wpautop(wptexturize($order->customer_note)); ?></td>
		</tr>
	<?php endif; ?>
	</tfoot>
	<tbody>
	<?php if (sizeof($order->items) > 0): ?>
		<?php foreach ($order->items as $item): ?>
			<?php
				if (isset($item['variation_id']) && $item['variation_id'] > 0){
					$product = new jigoshop_product_variation($item['variation_id']);

					if (is_array($item['variation'])) {
						$product->set_variation_attributes($item['variation']);
					}
				} else {
					$product = new jigoshop_product($item['id']);
				}
			?>
			<tr>
				<td><?php echo $product->get_sku(); ?></td>
				<td class="product-name">
					<?php echo $item['name']; ?>
			    <?php if ($product instanceof jigoshop_product_variation): ?>
						<?php echo jigoshop_get_formatted_variation($product, $item['variation']); ?>
					<?php endif; ?>
					<?php do_action('jigoshop_display_item_meta_data', $item); ?>
				</td>
				<td><?php echo $item['qty']; ?></td>
				<td><?php echo jigoshop_price($item['cost'], array('ex_tax_label' => 1)); ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	</tbody>
</table>
<?php do_action('jigoshop_before_order_customer_details', $order->id); ?>

<header>
	<h2><?php _e('Customer details', 'jigoshop'); ?></h2>
</header>
<dl>
	<?php if ($order->billing_email): ?>
		<dt><?php echo __('Email:', 'jigoshop'); ?></dt>
		<dd><?php echo $order->billing_email; ?></dd>
	<?php endif; ?>
	<?php if ($order->billing_phone): ?>
		<dt><?php echo __('Telephone:', 'jigoshop'); ?></dt>
		<dd><?php echo $order->billing_phone; ?></dd>
	<?php endif; ?>
</dl>
<?php do_action('jigoshop_after_order_customer_details', $order->id); ?>
<div class="col2-set addresses">
	<div class="col-1">
		<header class="title">
			<h3><?php _e('Shipping Address', 'jigoshop'); ?></h3>
		</header>
		<?php do_action('jigoshop_before_order_shipping_address', $order->id); ?>
		<address>
			<p>
				<?php if (!$order->formatted_shipping_address): ?>
					<?php _e('N/A', 'jigoshop'); ?>
				<?php else: ?>
					<?php echo $order->formatted_shipping_address; ?>
				<?php endif; ?>
			</p>
		</address>
		<?php do_action('jigoshop_after_order_shipping_address', $order->id); ?>
	</div>
	<div class="col-2">
		<header class="title">
			<?php do_action('jigoshop_before_order_billing_address', $order->id); ?>
			<h3><?php _e('Billing Address', 'jigoshop'); ?></h3>
		</header>
		<address>
			<p>
				<?php if (!$order->formatted_billing_address): ?>
					<?php _e('N/A', 'jigoshop'); ?>
				<?php else: ?>
					<?php echo $order->formatted_billing_address; ?>
				<?php endif; ?>
			</p>
		</address>
		<?php do_action('jigoshop_after_order_billing_address', $order->id); ?>
	</div>
</div>
<div class="clear"></div>
