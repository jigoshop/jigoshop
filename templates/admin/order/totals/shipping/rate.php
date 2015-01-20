<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\MultipleMethod Method to display.
 * @var $rate \Jigoshop\Shipping\Rate Rate to display.
 * @var $order \Jigoshop\Entity\Order Order to display.
 */
?>
<li
	class="list-group-item shipping-<?php echo $method->getId(); ?>-<?php echo $rate->getId(); ?> clearfix">
	<label>
		<input type="radio" name="order[shipping]" value="<?php echo $method->getId(); ?>" <?php echo Forms::checked($order->hasShippingMethod($method, $rate), true); ?> />
		<input type="hidden" class="shipping-method-rate" name="order[shipping_rate][<?php echo $method->getId(); ?>]" value="<?php echo $rate->getId(); ?>" />
		<?php echo $rate->getName(); ?>
	</label>
	<span class="pull-right"><?php echo Product::formatPrice($rate->calculate($order)); ?></span>
</li>
