<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\MultipleMethod Method to display.
 * @var $rate \Jigoshop\Shipping\Rate Rate to display.
 * @var $cart \Jigoshop\Entity\Cart Current cart.
 */
?>
<li
	class="list-group-item shipping-<?php echo $method->getId(); ?>-<?php echo $rate->getId(); ?> clearfix">
	<label>
		<input type="radio" name="jigoshop_order[shipping_method]" value="<?php echo $method->getId(); ?>" <?php echo Forms::checked($cart->hasShippingMethod($method, $rate), true); ?> />
		<input type="hidden" class="shipping-method-rate" name="jigoshop_order[shipping_method_rate][<?php echo $method->getId(); ?>]" value="<?php echo $rate->getId(); ?>" />
		<?php echo $rate->getName(); ?>
	</label>
	<span class="pull-right"><?php echo Product::formatPrice($rate->calculate($cart)); ?></span>
</li>
