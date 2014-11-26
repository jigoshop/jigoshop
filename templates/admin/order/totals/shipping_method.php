<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\Method
 * @var $order \Jigoshop\Entity\Order
 */
?>
<li class="list-group-item" id="shipping-<?php echo $method->getId(); ?>">
	<label>
		<input type="radio" name="order[shipping]" value="<?php echo $method->getId(); ?>" <?php echo Forms::checked($order->hasShippingMethod($method), true); ?> />
		<?php echo $method->getName(); ?>
	</label>
	<span class="pull-right"><?php echo Product::formatPrice($method->calculate($order)); ?></span>
</li>
