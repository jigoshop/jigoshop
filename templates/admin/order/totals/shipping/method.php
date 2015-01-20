<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\Method Method to display.
 * @var $order \Jigoshop\Entity\Order Order to display.
 */
?>
<li class="list-group-item shipping-<?php echo $method->getId(); ?> clearfix">
	<label>
		<input type="radio" name="order[shipping]" value="<?php echo $method->getId(); ?>" <?php echo Forms::checked($order->hasShippingMethod($method), true); ?> />
		<?php echo $method->getName(); ?>
	</label>
	<span class="pull-right"><?php echo Product::formatPrice($method->calculate($order)); ?></span>
</li>
