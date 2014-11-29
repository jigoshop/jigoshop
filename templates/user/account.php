<?php
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $content string Contents of cart page
 * @var $customer \Jigoshop\Entity\Customer The customer.
 * @var $editBillingAddressUrl string URL to billing address edition page.
 * @var $editShippingAddressUrl string URL to shipping address edition page.
 * @var $changePasswordUrl string URL to password changing page.
 * @var $myOrdersUrl string URL to My orders page.
 */
?>

<h1><?php _e('My account', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<?php echo wpautop(wptexturize($content)); ?>
<div class="col-md-8">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php _e('Billing address', 'jigoshop'); ?></h3>
				<a href="<?php echo $editBillingAddressUrl; ?>" class="btn btn-primary pull-right"><?php _e('Edit', 'jigoshop'); ?></a>
			</div>
			<div class="panel-body clearfix">
				<?php Render::output('user/account/address', array('address' => $customer->getBillingAddress())); ?>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php _e('Shipping address', 'jigoshop'); ?></h3>
				<a href="<?php echo $editShippingAddressUrl; ?>" class="btn btn-primary pull-right"><?php _e('Edit', 'jigoshop'); ?></a>
			</div>
			<div class="panel-body">
				<?php Render::output('user/account/address', array('address' => $customer->getShippingAddress())); ?>
			</div>
		</div>
</div>
<div class="col-md-4">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Account options', 'jigoshop'); ?></h3>
		</div>
		<ul class="list-group">
			<li class="list-group-item"><a href="<?php echo $changePasswordUrl; ?>"><?php _e('Change password', 'jigoshop'); ?></a></li>
			<li class="list-group-item"><a href="<?php echo $myOrdersUrl; ?>"><?php _e('My orders', 'jigoshop'); ?></a></li>
		</ul>
	</div>
	<div class="panel panel-warning" id="unpaid-orders">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Unpaid orders', 'jigoshop'); ?></h3>
		</div>
		<ul class="list-group">
			<?php foreach ($unpaidOrders as $order): /** @var $order \Jigoshop\Entity\Order */?>
			<li class="list-group-item clearfix">
				<h4 class="list-group-item-heading"><?php echo $order->getTitle(); ?></h4>
				<dl class="dl-horizontal list-group-item-text">
					<dt><?php _e('Date', 'jigoshop'); ?></dt>
					<dd><?php echo $order->getCreatedAt()->format(_x('d.m.Y, H:i', 'account', 'jigoshop')); ?></dd>
					<dt><?php _e('Status', 'jigoshop'); ?></dt>
					<dd><?php echo Status::getName($order->getStatus()); ?></dd>
					<dt><?php _e('Total', 'jigoshop'); ?></dt>
					<dd><?php echo Product::formatPrice($order->getTotal()); ?></dd>
				</dl>
				<a href="" class="btn btn-success pull-right"><?php _e('Pay', 'jigoshop'); ?></span></a>
			</li>
			<?php endforeach; ?>
			<li class="list-group-item">
				<a href="<?php echo $myOrdersUrl; ?>" class="btn btn-default"><?php _e('See more...', 'jigoshop'); ?></span></a>
			</li>
		</ul>
	</div>
</div>
