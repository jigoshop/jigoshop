<?php
/**
 * @var $orientation string CSS class for orientation of items in the list.
 * @var $products array List of products to display.
 * @var $has_thumbnails boolean Whether we want to display thumbnails or not.
 */
?>
<ul class="products <?php echo $orientation; ?> <?php echo $has_thumbnails ? 'thumbnails' : ''; ?>">
	<?php foreach($products as $post): ?>
	<li>
		<?php $product = new jigoshop_product($post->ID); ?>
		<div class="before-product">
			<?php do_action('jigoshop_before_shop_loop_item', $post, $product); ?>
		</div>
		<a class="product" href="<?php echo $product->get_link(); ?>">
			<?php do_action('jigoshop_before_shop_loop_item_title', $post, $product); ?>
			<strong><?php echo $product->get_title(); ?></strong>
			<?php do_action('jigoshop_after_shop_loop_item_title', $post, $product); ?>
		</a>
		<div class="after-product">
			<?php do_action('jigoshop_after_shop_loop_item', $post, $product); ?>
		</div>
	</li>
	<?php endforeach; ?>
</ul>