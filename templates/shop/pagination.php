<?php
/**
 * @var $product_count int Number of all available products
 */
?>
<?php if ($product_count > 1): ?>
	<div class="navigation">
		<?php if (function_exists('wp_pagenavi')) : ?>
			<?php wp_pagenavi(); ?>
		<?php else: ?>
			<div class="nav-next"><?php next_posts_link(__('Next <span class="meta-nav">&rarr;</span>', 'jigoshop'), $product_count); ?></div>
			<div class="nav-previous"><?php previous_posts_link(__('<span class="meta-nav">&larr;</span> Previous', 'jigoshop')); ?></div>
		<?php endif; ?>
	</div>
<?php endif; ?>
