<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order The order.
 * @var $tax array Tax data for the order.
 * @var $shippingMethods array List of available shipping methods.
 */
$orderTax = $order->getTax();
?>
<div class="jigoshop jigoshop-totals">
	<div class="form-horizontal">
		<div class="form-group">
			<label for="order_shipping" class="col-sm-2 control-label">
				<?php echo __('Shipping', 'jigoshop'); ?>
			</label>
			<div class="col-sm-9">
				<ul class="list-group">
					<?php foreach($shippingMethods as $method): /** @var $method \Jigoshop\Shipping\Method */ ?>
						<li class="list-group-item" id="shipping-<?php echo $method->getId(); ?>">
							<label>
								<input type="radio" name="order[shipping]" value="<?php echo $method->getId(); ?>" <?php echo Forms::checked($order->hasShippingMethod($method), true); ?> />
								<?php echo $method->getName(); ?>
							</label>
							<span class="pull-right"><?php echo Product::formatPrice($method->calculate($order)); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php Forms::constant(array(
			'name' => 'order[subtotal]',
			'id' => 'subtotal',
			'label' => __('Subtotal', 'jigoshop'),
			'placeholder' => 0.0,
			'value' => Product::formatPrice($order->getSubtotal()),
		)); ?>
		<?php Forms::text(array(
			'name' => 'order[discount]',
			'label' => sprintf(__('Discount (%s)', 'jigoshop'), Currency::symbol()),
			'placeholder' => 0.0,
			'value' => $order->getDiscount()
		)); ?>
		<?php foreach($tax as $class => $option): ?>
			<?php Forms::constant(array(
				'name' => 'order[tax]['.$class.']',
				'label' => $option['label'],
				'placeholder' => 0.0,
				'value' => $option['value'],
				'classes' => array($orderTax[$class] > 0 ? '' : 'not-active'),
			)); ?>
		<?php endforeach; ?>
		<?php Forms::constant(array(
			'name' => 'order[total]',
			'id' => 'total',
			'label' => __('Total', 'jigoshop'),
			'placeholder' => 0.0,
			'value' => Product::formatPrice($order->getTotal())
		)); ?>
	</div>
</div>
