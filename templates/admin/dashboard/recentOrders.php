<?php
use Jigoshop\Entity\Order;
use Jigoshop\Helper;

/**
 * @var $orders array List of orders.
 */
?>
<ul class="recent-orders">
	<?php foreach ($orders as $order): /** @var $order Order */ ?>
		<?php $totalItems = array_reduce($order->getItems(), function($value, $item){
			/** @var $item Order\Item */
			return $value + $item->getQuantity();
		}, 0); ?>
		<li>
			<a href="<?php echo get_edit_post_link($order->getId()); ?>">#<?php echo $order->getNumber(); ?></a>
			<span class="order-customer"><?php echo $order->getCustomer()->getName(); ?></span>
			<?php echo Helper\Order::getStatus($order); ?>
			<span class="order-time"><?php echo get_the_time(_x('M d, Y', 'dashboard', 'jigoshop'), $order->getId()); ?></span>
			<small>
				<?php echo count($order->getItems()); ?> <?php echo _n('Item', 'Items', count($order->getItems()), 'jigoshop'); ?>,
				<span	class="total-quantity"><?php echo __('Total Quantity', 'jigoshop'); ?> <?php echo $totalItems; ?></span>
				<span	class="order-cost"><?php echo Helper\Product::formatPrice($order->getTotal()); ?></span>
			</small>
		</li>
	<?php endforeach; ?>
</ul>
