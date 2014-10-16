<?php
use Jigoshop\Entity\Order\CompanyAddress;

/**
 * @var $order \Jigoshop\Entity\Order The order.
 */

$address = $order->getShippingAddress();
?>
<ul class="list-unstyled">
	<li><strong><?php echo $address->getName(); ?></strong></li>
	<?php if ($address instanceof CompanyAddress): ?>
		<li><?php echo $address->getCompany(); ?></li>
	<?php endif; ?>
	<li>
		<address>
			<?php echo $address; ?>
		</address>
		<?php $google_address = $address->getGoogleAddress(); ?>
		<?php if (!empty($google_address)): ?>
			<a target="_blank" href="http://maps.google.com/maps?&q=<?php echo $google_address; ?>&z=16"><?php _e('Map' ,'jigoshop'); ?></a>
		<?php endif; ?>
	</li>
</ul>
