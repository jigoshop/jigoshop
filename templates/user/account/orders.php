<?php
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $customer Customer
 * @var $orders array List of user's orders
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $myAccountUrl string URL to my account.
 */
?>
<h1><?php _e('My account &rang; Orders', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><?php _e('Orders list', 'jigoshop'); ?></h3>
	</div>
	<ul class="list-group">
		<?php foreach ($orders as $order): /** @var $order \Jigoshop\Entity\Order */?>
			<?php $unpaid = in_array($order->getStatus(), array(Status::CREATED, Status::PENDING)); ?>
			<li class="list-group-item clearfix <?php $unpaid and print 'list-group-item-warning'; ?>">
				<h4 class="list-group-item-heading">
					<?php echo $order->getTitle(); ?>
					<?php if ($unpaid): ?>
						<a href="" class="btn btn-success pull-right"><?php _e('Pay', 'jigoshop'); ?></span></a>
					<?php endif; ?>
					<a href="<?php echo Api::getEndpointUrl('orders', $order->getId()); ?>" class="btn btn-primary pull-right"><?php _e('View', 'jigoshop'); ?></span></a>
				</h4>
				<dl class="dl-horizontal list-group-item-text">
					<dt><?php _e('Date', 'jigoshop'); ?></dt>
					<dd><?php echo $order->getCreatedAt()->format(_x('d.m.Y, H:i', 'account', 'jigoshop')); ?></dd>
					<dt><?php _e('Status', 'jigoshop'); ?></dt>
					<dd><?php echo Status::getName($order->getStatus()); ?></dd>
					<dt><?php _e('Total', 'jigoshop'); ?></dt>
					<dd><?php echo Product::formatPrice($order->getTotal()); ?></dd>
				</dl>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<a href="<?php echo $myAccountUrl; ?>" class="btn btn-default"><?php _e('Go back to My account', 'jigoshop'); ?></a>
