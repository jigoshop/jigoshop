<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\MultipleMethod Method to display.
 * @var $cart \Jigoshop\Frontend\Cart Current cart.
 */
?>
<?php foreach ($method->getRates() as $rate): /** @var $rate \Jigoshop\Shipping\Rate */ ?>
	<?php \Jigoshop\Helper\Render::output('shop/checkout/shipping/rate', array('method' => $method, 'rate' => $rate, 'cart' => $cart)); ?>
<?php endforeach; ?>
