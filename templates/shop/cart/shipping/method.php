<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\Method Method to display.
 * @var $cart \Jigoshop\Entity\Cart Current cart.
 */
?>
<li class="list-group-item shipping-<?php echo $method->getId(); ?>">
	<label>
		<input type="radio" name="jigoshop_order[shipping_method]" value="<?php echo $method->getId(); ?>" <?php echo Forms::checked($cart->hasShippingMethod($method), true); ?> />
		<?php echo $method->getName(); ?>
	</label>
	<span class="pull-right"><?php echo Product::formatPrice($method->calculate($cart)); ?></span>
</li>
