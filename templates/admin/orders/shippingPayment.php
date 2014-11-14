<?php
/**
 * @var $order \Jigoshop\Entity\Order The order.
 */
$hasShipping = $order->getShippingMethod() !== null;
$hasPayment = $order->getPaymentMethod() !== null;
?>
<?php if($hasPayment || $hasShipping): ?>
<dl>
	<?php if($hasShipping): ?>
	<dd class="shipping"><?php _e('Shipping', 'jigoshop'); ?></dd>
	<dt class="shipping"><?php echo $order->getShippingMethod()->getName(); ?></dt>
	<?php endif; ?>
	<?php if($hasPayment): ?>
	<dd class="payment"><?php _e('Payment', 'jigoshop'); ?></dd>
	<dt class="payment"><?php echo $order->getPaymentMethod()->getName(); ?></dt>
	<?php endif; ?>
</dl>
<?php endif; ?>
