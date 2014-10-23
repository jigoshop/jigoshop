<?php
/**
 * @var $order \Jigoshop\Entity\Order The order.
 */
$hasShipping = $order->getShippingMethod() !== null;
$hasPayment = $order->getPayment() !== null;
?>
<?php if($hasPayment || $hasShipping): ?>
<dl>
	<?php if($hasShipping): ?>
	<dd class="shipping"><?php _e('Shipping', 'jigoshop'); ?></dd>
	<dt class="shipping"><?php $order->getShippingMethod()->getName(); ?></dt>
	<?php endif; ?>
	<?php if($hasPayment): ?>
	<dd class="payment"><?php _e('Payment', 'jigoshop'); ?></dd>
	<dt class="payment"><?php $order->getPayment()->getName(); ?></dt>
	<?php endif; ?>
</dl>
<?php endif; ?>
