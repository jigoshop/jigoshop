<?php
/**
 * @var $productCount int Number of products.
 * @var $categoryCount int Number of product categories.
 * @var $tagCount int Number of tags for products.
 * @var $attributesCount int Number of product attributes.
 * @var $pendingCount int Number of pending orders.
 * @var $onHoldCount int Number of orders on hold.
 * @var $processingCount int Number of processing orders.
 * @var $completedCount int Number of completed orders.
 */
?>
<div id="jigoshop_right_now" class="jigoshop_right_now">
	<div class="table table_content">
		<p class="sub"><?= __('<span>Shop</span> Content', 'jigoshop'); ?></p>
		<table>
			<tbody>
			<tr class="first">
				<td class="first b"><a href="edit.php?post_type=product"><?= $productCount; ?></a></td>
				<td class="t"><a href="edit.php?post_type=product"><?php _e('Products', 'jigoshop'); ?></a></td>
			</tr>
			<tr>
				<td class="first b"><a href="edit-tags.php?taxonomy=product_cat&post_type=product"><?= $categoryCount; ?></a></td>
				<td class="t"><a href="edit-tags.php?taxonomy=product_cat&post_type=product"><?php _e('Product Categories', 'jigoshop'); ?></a></td>
			</tr>
			<tr>
				<td class="first b"><a href="edit-tags.php?taxonomy=product_tag&post_type=product"><?= $tagCount; ?></a></td>
				<td class="t"><a href="edit-tags.php?taxonomy=product_tag&post_type=product"><?php _e('Product Tag', 'jigoshop'); ?></a></td>
			</tr>
			<tr>
				<td class="first b"><a href="admin.php?page=jigoshop_attributes"><?= $attributesCount; ?></a></td>
				<td class="t"><a href="admin.php?page=jigoshop_attributes"><?php _e('Attribute taxonomies', 'jigoshop'); ?></a></td>
			</tr>
			</tbody>
		</table>
	</div>
	<div class="table table_discussion">
		<p class="sub"></p>
		<table>
			<tbody>
			<tr class="first pending-orders">
				<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=pending"><span class="total-count"><?= $pendingCount; ?></span></a></td>
				<td class="last t"><a class="pending" href="edit.php?post_type=shop_order&shop_order_status=pending"><?php _e('Pending', 'jigoshop'); ?></a></td>
			</tr>
			<tr class="on-hold-orders">
				<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=on-hold"><span class="total-count"><?= $onHoldCount; ?></span></a></td>
				<td class="last t"><a class="onhold" href="edit.php?post_type=shop_order&shop_order_status=on-hold"><?php _e('On-Hold', 'jigoshop'); ?></a></td>
			</tr>
			<tr class="processing-orders">
				<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=processing"><span class="total-count"><?= $processingCount; ?></span></a></td>
				<td class="last t"><a class="processing" href="edit.php?post_type=shop_order&shop_order_status=processing"><?php _e('Processing', 'jigoshop'); ?></a></td>
			</tr>
			<tr class="completed-orders">
				<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=completed"><span class="total-count"><?= $completedCount; ?></span></a></td>
				<td class="last t"><a class="complete" href="edit.php?post_type=shop_order&shop_order_status=completed"><?php _e('Completed', 'jigoshop'); ?></a></td>
			</tr>
			</tbody>
		</table>
	</div>
	<br class="clear" />
</div>