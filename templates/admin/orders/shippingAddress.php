<?php

/**
 * @var $order \Jigoshop\Entity\Order The order.
 */

$address = $order->getCustomer()->getShippingAddress();
?>
<address>
	<?php echo $address; ?>
</address>
<?php $google_address = $address->getGoogleAddress(); ?>
<?php if (!empty($google_address)): ?>
	<a target="_blank" href="http://maps.google.com/maps?&q=<?php echo $google_address; ?>&z=16"><?php _e('Map' ,'jigoshop'); ?></a>
<?php endif; ?>
