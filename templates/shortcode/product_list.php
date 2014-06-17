<?php
/**
 * @var $orientation string CSS class for orientation of items in the list.
 * @var $products array List of products to display.
 */
?>
<ul class="products <?= $orientation; ?>">
	<?php foreach($products as $post): ?>
	<li>
		<?php $product = new jigoshop_product($post->ID); ?>
		<?php do_action('jigoshop_before_shop_loop_item'); ?>
		<a href="<?= $product->get_link(); ?>">
			<?php do_action('jigoshop_before_shop_loop_item_title', $post, $product); ?>
			<strong><?= $product->get_title(); ?></strong>
			<?php do_action('jigoshop_after_shop_loop_item_title', $post, $product); ?>
		</a>
		<?php do_action('jigoshop_after_shop_loop_item', $post, $product); ?>
	</li>
	<?php endforeach; ?>
</ul>