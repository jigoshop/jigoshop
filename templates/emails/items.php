<?php
/**
 * @var $order jigoshop_order Order to show items for.
 * @var $show_sku boolean Whether to show SKU field.
 */
?>
<?php foreach ($order->items as $item): $product = $order->get_product_from_item($item); ?>
<tr>
	<td><?php echo apply_filters('jigoshop_order_product_title', $item['name'], $product, $item); ?>
	<?php if ($show_sku): ?>
		<?php printf(_x(' (#%s)', 'emails', 'jigoshop'), $product->sku); ?>
	<?php endif; ?>
	<?php if ($product instanceof \jigoshop_product_variation): ?>
		<div class="variation">
			<?php echo jigoshop_get_formatted_variation($product, $item['variation']); ?>
		</div>
	<?php endif; ?>
	<?php if (!empty($item['customization'])): ?>
		<div class="customization">
			<?php echo apply_filters('jigoshop_customized_product_label', __('Personal:', 'jigoshop')); ?><br/
			<?php echo $item['customization']; ?>
		</div>
	<?php endif; ?>
	</td>
	<td><?php echo $item['qty']; ?></td>
	<td>
		<?php if ($use_inc_tax && $item['cost_inc_tax'] >= 0): ?>
			<?php echo jigoshop_price($item['cost_inc_tax'] * $item['qty'], array('ex_tax_label' => 0)); ?>
		<?php else: ?>
			<?php echo jigoshop_price($item['cost'], array('ex_tax_label' => 1)); ?>
		<?php endif; ?>
	</td>
</tr>
<?php if ($show_links && $product->is_type('downloadable') && $product->exists()):
	$product_id = (bool)$item['variation_id'] ? $product->variation_id : $product->id;
	$url = apply_filters('downloadable_file_url', $order->get_downloadable_file_url($product_id), $product, $order);
	?>
	<tr>
		<td><?php _ex('Download link:', 'emails', 'jigoshop'); ?></td>
		<td colspan="2"><a href="<?php echo $url; ?>"><?php echo $url; ?></a></td>
	</tr>
<?php endif; ?>
<?php endforeach; ?>
