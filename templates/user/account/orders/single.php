<?php
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $customer Customer
 * @var $order \Jigoshop\Entity\Order Order to display.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $myAccountUrl string URL to my account.
 * @var $listUrl string URL to orders list.
 * @var $showWithTax bool Whether to show product price with or without tax.
 * @var $getTaxLabel \Closure Function to retrieve tax label.
 */
?>
<h1><?php printf(__('My account &rang; Orders &rang; %s', 'jigoshop'), $order->getTitle()); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<dl class="dl-horizontal">
	<dt><?php _e('Made on', 'jigoshop'); ?></dt>
	<dd><?php echo $order->getCreatedAt()->format(_x('d.m.Y, H:i', 'account', 'jigoshop')); ?></dd>
	<dt><?php _e('Status', 'jigoshop'); ?></dt>
	<dd><?php echo Status::getName($order->getStatus()); ?></dd>
</dl>
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Billing address', 'jigoshop'); ?></h3>
		</div>
		<div class="panel-body clearfix">
			<?php Render::output('user/account/address', array('address' => $order->getCustomer()->getBillingAddress())); ?>
		</div>
	</div>
</div>
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Shipping address', 'jigoshop'); ?></h3>
		</div>
		<div class="panel-body">
			<?php Render::output('user/account/address', array('address' => $order->getCustomer()->getShippingAddress())); ?>
		</div>
	</div>
</div>
<h3><?php _e('Order details', 'jigoshop'); ?></h3>
<table class="table table-hover">
	<thead>
		<tr>
			<th class="product-thumbnail"></th>
			<th class="product-name"><?php _e('Product Name', 'jigoshop'); ?></th>
			<th class="product-price"><?php _e('Unit Price', 'jigoshop'); ?></th>
			<th class="product-quantity"><?php _e('Quantity', 'jigoshop'); ?></th>
			<th class="product-subtotal"><?php _e('Price', 'jigoshop'); ?></th>
		</tr>
		<?php do_action('jigoshop\checkout\table_head', $order); ?>
	</thead>
	<tbody>
		<?php foreach($order->getItems() as $key => $item): /** @var $item \Jigoshop\Entity\Order\Item */ ?>
			<?php Render::output('shop/checkout/item/'.$item->getType(), array('cart' => $order, 'key' => $key, 'item' => $item, 'showWithTax' => $showWithTax)); ?>
		<?php endforeach; ?>
		<?php do_action('jigoshop\checkout\table_body', $order); ?>
	</tbody>
	<tfoot>
		<tr id="product-subtotal">
			<?php $productSubtotal = $showWithTax ? $order->getProductSubtotal() + $order->getTotalTax() : $order->getProductSubtotal(); ?>
			<th scope="row" colspan="4" class="text-right"><?php _e('Products subtotal', 'jigoshop'); ?></th>
			<td><?php echo Product::formatPrice($productSubtotal); ?></td>
		</tr>
	</tfoot>
</table>
<div id="cart-collaterals">
	<div id="cart-totals" class="panel panel-primary pull-right">
		<div class="panel-heading"><h2 class="panel-title"><?php _e('Order Totals', 'jigoshop'); ?></h2></div>
		<table class="table">
			<tbody>
			<?php if ($order->getShippingMethod()): ?>
				<tr id="shipping-calculator">
					<th scope="row">
						<?php _e('Shipping', 'jigoshop'); ?>
					</th>
					<td>
						<label>
							<?php echo $order->getShippingMethod()->getName(); ?>
						</label>
						<span class="pull-right"><?php echo Product::formatPrice($order->getShippingPrice()); ?></span>
					</td>
				</tr>
			<?php endif; ?>
			<tr id="cart-subtotal">
				<th scope="row"><?php _e('Subtotal', 'jigoshop'); ?></th>
				<td><?php echo Product::formatPrice($order->getSubtotal()); ?></td>
			</tr>
			<?php foreach ($order->getTax() as $taxClass => $tax): ?>
				<?php if ($tax > 0): ?>
					<tr id="tax-<?php echo $taxClass; ?>">
						<th scope="row"><?php echo $getTaxLabel($taxClass); ?></th>
						<td><?php echo Product::formatPrice($tax); ?></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
			<tr id="cart-discount"<?php $order->getDiscount() == 0 and print ' class="not-active"'; ?>>
				<th scope="row"><?php _e('Discount', 'jigoshop'); ?></th>
				<td><?php echo Product::formatPrice($order->getDiscount()); ?></td>
			</tr>
			<tr id="cart-total">
				<th scope="row"><?php _e('Total', 'jigoshop'); ?></th>
				<td><?php echo Product::formatPrice($order->getTotal()); ?></td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
<a href="<?php echo $myAccountUrl; ?>" class="btn btn-default"><?php _e('Go back to My account', 'jigoshop'); ?></a>
<a href="<?php echo $listUrl; ?>" class="btn btn-default"><?php _e('Go back to orders list', 'jigoshop'); ?></a>
<?php if (in_array($order->getStatus(), array(Status::PENDING))): ?>
	<a href="" class="btn btn-success pull-right"><?php _e('Pay', 'jigoshop'); ?></a>
<?php endif; ?>
