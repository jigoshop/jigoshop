<?php
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $content string Contents of cart page
 * @var $customer \Jigoshop\Entity\Customer The customer.
 */
?>

<h1><?php _e('My account', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<?php echo wpautop(wptexturize($content)); ?>
<div class="col-md-8">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php _e('Billing address', 'jigoshop'); ?></h3>
				<a href="" class="btn btn-primary pull-right"><?php _e('Edit', 'jigoshop'); ?></a>
			</div>
			<div class="panel-body clearfix">
				<?php Render::output('account/address', array('address' => $customer->getBillingAddress())); ?>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php _e('Shipping address', 'jigoshop'); ?></h3>
				<a href="" class="btn btn-primary pull-right"><?php _e('Edit', 'jigoshop'); ?></a>
			</div>
			<div class="panel-body">
				<?php Render::output('account/address', array('address' => $customer->getShippingAddress())); ?>
			</div>
		</div>
</div>
<div class="col-md-4">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Account options', 'jigoshop'); ?></h3>
		</div>
		<ul class="list-group">
			<li class="list-group-item"><a href=""><?php _e('Change password', 'jigoshop'); ?></a></li>
		</ul>
	</div>
</div>
