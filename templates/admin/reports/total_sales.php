<?php
use Jigoshop\Helper\Product;

/**
 * @var $orders array List of currently processed orders.
 * @var $total_sales float Total value of sales.
 */
?>
<div class="span3 thumbnail">
	<h1><?php echo Product::formatPrice($total_sales); ?></h1>
	<h3><?php _e('Total Sales','jigoshop'); ?></h3>
</div>
