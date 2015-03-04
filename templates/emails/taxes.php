<?php
/**
 * @var $order jigoshop_order Order to show items for.
 */
?>
<?php foreach ($order->get_tax_classes() as $tax_class): ?>
<tr>
	<td colspan="2">
		<strong><?php printf(_x('%s (%s%%)', 'emails', 'jigoshop'), $order->get_tax_class_for_display($tax_class), $order->get_tax_rate($tax_class)); ?></strong>
	</td>
	<td>
		<?php echo $order->get_tax_amount($tax_class); ?>
	</td>
</tr>
<?php endforeach; ?>
