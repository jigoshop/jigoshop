<?php
/**
 * @var $order \Jigoshop\Entity\Order The order.
 */
$hasShipping = $order->getShippingMethod() !== null;
$hasPayment = $order->getPaymentMethod() !== null;
?>
<?php if($hasPayment || $hasShipping): ?>
<dl class="dl-horizontal">
	<?php if($hasShipping): ?>
	<dt class="shipping"><?php _e('Shipping', 'jigoshop'); ?></dt>
	<dd class="shipping"><?php echo $order->getShippingMethod()->getName(); ?></dd>
	<?php endif; ?>
	<?php if($hasPayment): ?>
	<dt class="payment"><?php _e('Payment', 'jigoshop'); ?></dt>
	<dd class="payment"><?php echo $order->getPaymentMethod()->getName(); ?></dd>
	<?php endif; ?>
</dl>
<?php endif; ?>
