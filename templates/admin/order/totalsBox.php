<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order The order.
 * @var $tax array Tax data for the order.
 * @var $shippingMethods array List of available shipping methods.
 */
?>
<div class="jigoshop jigoshop-totals">
	<div class="form-horizontal">
		<?php //Forms::select(array(
//			'name' => 'order[shipping]',
//			'label' => __('Shipping', 'jigoshop'),
//			'value' => $order->getShippingMethod() ? $order->getShippingMethod()->getId() : false,
//			'options' => $shippingMethods,
//		)); ?>
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
				'classes' => array($option['value'] > 0 ? '' : 'not-active'),
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
