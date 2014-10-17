<?php

/**
 * @var $order \Jigoshop\Entity\Order The order.
 * @var $shippingMethods array List of available shipping methods.
 */
?>
<div class="jigoshop">
	<div class="form-horizontal">
		<table class="table table-striped">
			<thead>
			<tr>
				<th scope="col"><?php _e('ID', 'jigoshop'); ?></th>
				<th scope="col"><?php _e('Name', 'jigoshop'); ?></th>
				<th scope="col"><?php _e('Price', 'jigoshop'); ?></th>
				<th scope="col"><?php _e('Quantity', 'jigoshop'); ?></th>
				<th scope="col"><?php _e('Total', 'jigoshop'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($order->getItems() as $item): ?>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
