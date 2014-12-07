<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order
 * @var $item \Jigoshop\Entity\Order\Item
 */

$id = $item->getKey();
?>
<tr data-id="<?php echo $id; ?>" data-product="<?php echo $item->getProduct()->getId(); ?>">
	<td class="id"><?php Forms::constant(array('name' => 'order[items]['.$id.'][id]', 'value' => $item->getProduct()->getId())); ?></td>
	<td class="sku"><?php Forms::constant(array('name' => 'order[items]['.$id.'][sku]', 'value' => $item->getProduct()->getSku())); ?></td>
	<td class="name"><?php Forms::constant(array('name' => 'order[items]['.$id.'][name]', 'value' => $item->getName())); ?></td>
	<td class="price"><?php Forms::text(array('name' => 'order[items]['.$id.'][price]', 'value' => Product::formatNumericPrice($item->getPrice()))); ?></td>
	<td class="quantity"><?php Forms::text(array('name' => 'quantity['.$id.']', 'value' => $item->getQuantity())); ?></td>
	<td class="total"><?php Forms::constant(array('name' => 'order[items]['.$id.'][total]', 'value' => Product::formatPrice($item->getCost()))); ?></td>
	<td class="actions">
		<a href="" class="close remove"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Remove', 'jigoshop'); ?></span></a>
	</td>
</tr>
