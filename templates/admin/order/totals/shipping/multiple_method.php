<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $method \Jigoshop\Shipping\MultipleMethod Method to display.
 * @var $order \Jigoshop\Entity\Order Order to display.
 */
?>
<?php foreach ($method->getRates() as $rate): /** @var $rate \Jigoshop\Shipping\Rate */ ?>
	<?php \Jigoshop\Helper\Render::output('admin/order/totals/shipping/rate', array('method' => $method, 'rate' => $rate, 'order' => $order)); ?>
<?php endforeach; ?>
