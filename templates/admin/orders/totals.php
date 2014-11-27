<?php
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order
 * @var $getTaxLabel \Closure
 */
?>
<div class="jigoshop">
	<dl class="dl-horizontal">
		<?php if ($order->getProductSubtotal() != $order->getTotal()): ?>
			<dt scope="row"><?php _e('Product subtotal', 'jigoshop'); ?></dt>
			<dd><?php echo Product::formatPrice($order->getProductSubtotal()); ?></dd>
		<?php endif; ?>
		<?php if ($order->getShippingPrice() > 0): ?>
			<dt scope="row"><?php _e('Shipping', 'jigoshop'); ?></dt>
			<dd><?php echo Product::formatPrice($order->getShippingPrice()); ?></dd>
		<?php	endif; ?>
		<?php do_action('jigoshop\admin\orders\totals\after_shipping'); ?>
		<?php foreach ($order->getCombinedTax() as $taxClass => $tax): ?>
			<?php if ($tax > 0): ?>
				<dt scope="row"><?php echo $getTaxLabel($taxClass); ?></dt>
				<dd><?php echo Product::formatPrice($tax); ?></dd>
			<?php endif; ?>
		<?php	endforeach; ?>
		<dt scope="row"><?php _e('Total', 'jigoshop'); ?></dt>
		<dd><?php echo Product::formatPrice($order->getTotal()); ?></dd>
	</dl>
</div>
