<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\MultipleMethod Method to display.
 * @var $cart \Jigoshop\Frontend\Cart Current cart.
 */
?>
<?php foreach ($method->getRates() as $rate): /** @var $rate \Jigoshop\Shipping\Rate */ ?>
<li class="list-group-item shipping-<?php echo $method->getId(); ?>-<?php echo $rate->getId(); ?>">
	<label>
		<input type="radio" name="jigoshop_order[shipping_method]" value="<?php echo $method->getId(); ?>" <?php echo Forms::checked($cart->hasShippingMethod($method, $rate), true); ?> />
		<input type="hidden" name="jigoshop_order[shipping_method_rate][<?php echo $method->getId(); ?>]" value="<?php echo $rate->getId(); ?>" />
		<?php echo $rate->getName(); ?>
	</label>
	<span class="pull-right"><?php echo Product::formatPrice($rate->calculate($cart)); ?></span>
</li>
<?php endforeach; ?>
