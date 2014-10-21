<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order The order.
 * @var $shippingMethods array List of available shipping methods.
 */
?>
<div class="jigoshop jigoshop-order">
	<div class="form-horizontal">
		<table class="table table-striped">
			<thead>
			<tr>
				<th scope="col"><?php _e('ID', 'jigoshop'); ?></th>
				<th scope="col"><?php _e('SKU', 'jigoshop'); ?></th>
				<th scope="col"><?php _e('Name', 'jigoshop'); ?></th>
				<th scope="col"><?php printf(__('Unit price (%s)', 'jigoshop'), Currency::symbol()); ?></th>
				<th scope="col"><?php _e('Quantity', 'jigoshop'); ?></th>
				<th scope="col"><?php _e('Price', 'jigoshop'); ?></th>
				<th scope="col"></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($order->getItems() as $item): ?>
			<tr>
				<td class="id"><?php Forms::constant(array('name' => 'order[items]['.$item['id'].'][id]', 'value' => $item['id'])); ?></td>
				<td class="sku"><?php Forms::constant(array('name' => 'order[items]['.$item['id'].'][sku]', 'value' => $item['sku'])); ?></td>
				<td class="name"><?php Forms::constant(array('name' => 'order[items]['.$item['id'].'][name]', 'value' => $item['name'])); ?></td>
				<td class="price"><?php Forms::text(array('name' => 'order[items]['.$item['id'].'][price]', 'value' => Product::formatNumericPrice($item['price']))); ?></td>
				<td class="quantity"><?php Forms::text(array('name' => 'quantity['.$item['id'].']', 'value' => $item['quantity'])); ?></td>
				<td class="total"><?php Forms::constant(array('name' => 'order[items]['.$item['id'].'][total]', 'value' => Product::formatPrice($item['price'] * $item['quantity']))); ?></td>
				<td class="actions">
					<a href="" class="close"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Remove', 'jigoshop'); ?></span></a>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="3"><?php Forms::text(array('name' => 'new_item', 'id' => 'new-item', 'placeholder' => __('Search for products...', 'jigoshop'))); ?></td>
				<td><button class="btn btn-primary" id="add-item"><?php _e('Add item', 'jigoshop'); ?></button></td>
				<td class="text-right"><strong><?php _e('Product subtotal:', 'jigoshop'); ?></strong></td>
				<td id="product-subtotal"><?php echo Product::formatPrice($order->getSubtotal()); ?></td>
				<td></td>
			</tr>
			</tfoot>
		</table>
	</div>
</div>
