<?php
/**
 * @var $lowStock array List of products with low stock.
 * @var $outOfStock array List of products out of stock.
 */
?>
<div id="jigoshop_right_now" class="jigoshop_right_now">
	<div class="table table_content">
		<p class="sub"><?php _e('Low Stock', 'jigoshop'); ?></p>
		<?php if(count($lowStock) > 0): ?>
			<ol>
				<?php foreach($lowStock as $item): /** @var $item \Jigoshop\Entity\Product */?>
				<li><a href="<?= get_edit_post_link($item->getId()); ?>"><?= $item->getName(); ?></a></li>
				<?php endforeach; ?>
			</ol>
		<?php else: ?>
			<p><?= __('No products are low in stock.', 'jigoshop'); ?></p>
		<?php endif; ?>
	</div>
	<div class="table table_discussion">
		<p class="sub"><?php _e('Out of Stock/Backorders', 'jigoshop'); ?></p>
		<?php if(count($outOfStock) > 0): ?>
			<ol>
				<?php foreach($outOfStock as $item): /** @var $item \Jigoshop\Entity\Product */?>
					<li><a href="<?= get_edit_post_link($item->getId()); ?>"><?= $item->getName(); ?></a></li>
				<?php endforeach; ?>
			</ol>
		<?php else: ?>
		<p><?= __('No products are out of stock.', 'jigoshop'); ?></p>
		<?php endif; ?>
	</div>
	<br class="clear"/>
</div>