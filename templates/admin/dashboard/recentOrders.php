<?php
/**
 * @var $orders array List of orders.
 */
?>
<ul class="recent-orders">
	<?php foreach ($orders as $order): ?>
		<?php /** @var $order \Jigoshop\Entity\Order */ ?>
		<li>
			<span class="order-status <?= sanitize_title($order->getStatus()); ?>"><?= ucwords(\__($order->getStatus(), 'jigoshop')); ?></span> <a
				href="<?= admin_url('post.php?post='.$order->getId()); ?>&action=edit"><?= get_the_time(\__('M d, Y', 'jigoshop'), $order->getId()); ?></a>
			<small><?= count($order->getItems()); ?> <?= _n('Item', 'Items', count($order->getItems()), 'jigoshop'); ?>, <span
					class="total-quantity"><?= \__('Total Quantity', 'jigoshop'); ?> <?= $total_items; ?></span> <span
					class="order-cost"><?= $order->getTotal(); //jigoshop_price($this_order->order_total); ?></span></small>
		</li>
	<?php endforeach; ?>
</ul>