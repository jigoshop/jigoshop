<?php
/**
 * @var $lowStock array List of products with low stock.
 * @var $outOfStock array List of products out of stock.
 * @var $notifyOutOfStock boolean Do the user want to be notified about out of stock products?
 */
?>
<div class="table table_content">
	<p class="sub"><?php _e('Low Stock', 'jigoshop'); ?></p>
	<?php if (count($lowStock) > 0): ?>
		<ol>
			<?php foreach ($lowStock as $item): /** @var $item \Jigoshop\Entity\Product|\Jigoshop\Entity\Product\Purchasable */ ?>
				<li><a href="<?php echo get_edit_post_link($item->getId()); ?>"><?php echo $item->getName(); ?></a> <span><?php printf(__('Stock: %d', 'jigoshop'), $item->getStock()->getStock()); ?></span></li>
			<?php endforeach; ?>
		</ol>
	<?php else: ?>
		<p class="message"><?php echo __('No products are low in stock.', 'jigoshop'); ?></p>
	<?php endif; ?>
</div>
<?php if ($notifyOutOfStock): ?>
	<div class="table table_discussion">
		<p class="sub"><?php _e('Out of Stock/Backorders', 'jigoshop'); ?></p>
		<?php if (count($outOfStock) > 0): ?>
			<ol>
				<?php foreach ($outOfStock as $item): /** @var $item \Jigoshop\Entity\Product */ ?>
					<li><a href="<?php echo get_edit_post_link($item->getId()); ?>"><?php echo $item->getName(); ?></a></li>
				<?php endforeach; ?>
			</ol>
		<?php else: ?>
			<p class="message"><?php echo __('No products are out of stock.', 'jigoshop'); ?></p>
		<?php endif; ?>
	</div>
<?php endif; ?>
