<?php
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $content string Contents of cart page
 * @var $order \Jigoshop\Entity\Order The order.
 * @var $shopUrl string URL to product list page.
 * @var $showWithTax bool Whether to show product price with or without tax.
 * @var $getTaxLabel \Closure Function to retrieve tax label.
 */
?>

<h1><?php _e('Thank you for your order', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<?php echo wpautop(wptexturize($content)); ?>
<dl class="dl-horizontal">
	<dt><?php _e('Order number', 'jigoshop'); ?></dt>
	<dd><?php echo $order->getNumber(); ?></dd>
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
		<th scope="row" colspan="4" class="text-right"><?php _e('Products subtotal', 'jigoshop'); ?></th>
		<td><?php echo Product::formatPrice($order->getProductSubtotal()); ?></td>
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
			<?php foreach ($order->getTax() as $taxClass => $tax): //TODO: Fix showing tax after registering ?>
				<?php if ($tax > 0): ?>
					<tr id="tax-<?php echo $taxClass; ?>">
						<th scope="row"><?php echo $getTaxLabel($taxClass); ?></th>
						<td><?php echo Product::formatPrice($tax); ?></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
			<tr id="cart-total">
				<th scope="row"><?php _e('Total', 'jigoshop'); ?></th>
				<td><?php echo Product::formatPrice($order->getTotal()); ?></td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
<a href="<?php echo $shopUrl; ?>" class="btn btn-primary pull-right"><?php _e('Continue shopping', 'jigoshop'); ?></a>
