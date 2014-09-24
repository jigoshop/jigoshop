<?php
use Jigoshop\Helper\Render;

/**
 * @var $products array List of products to display
 * @var $product_count int Number of all available products.
 */
have_posts();
?>
<ul id="products">
	<?php foreach($products as $product): ?>
		<?php Render::output('shop/list/product', array(
			'product' => $product,
		)); ?>
	<?php endforeach; ?>
</ul>
<?php
next_posts_link(__('Next &raquo;', 'jigoshop'), $product_count);
previous_posts_link(__('&laquo; Previous', 'jigoshop'));
?>
<?php //do_action('jigoshop\list\pagination', $product_count, $products); // TODO: Render pagination ?>
