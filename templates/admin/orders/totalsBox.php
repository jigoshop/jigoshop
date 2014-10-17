<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order The order.
 * @var $shippingMethods array List of available shipping methods.
 */
?>
<div class="jigoshop">
	<div class="form-horizontal">
		<?php //Forms::select(array(
//			'name' => 'order[shipping]',
//			'label' => __('Shipping', 'jigoshop'),
//			'value' => $order->getShipping() ? $order->getShipping()->getId() : false,
//			'options' => $shippingMethods,
//		)); ?>
		<?php Forms::constant(array(
			'name' => 'order[subtotal]',
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
		<?php foreach($order->getTax() as $class => $value): ?>
			<?php Forms::text(array(
				'name' => 'order[tax]['.$class.']',
				'label' => $class,
				'placeholder' => 0.0,
				'value' => $value,
			)); ?>
		<?php endforeach; ?>
		<?php Forms::constant(array(
			'name' => 'order[total]',
			'label' => __('Total', 'jigoshop'),
			'placeholder' => 0.0,
			'value' => Product::formatPrice($order->getTotal())
		)); ?>
	</div>
</div>
