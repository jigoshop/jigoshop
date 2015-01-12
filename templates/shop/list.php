<?php
use Jigoshop\Helper\Render;

/**
 * @var $products array List of products to display
 * @var $product_count int Number of all available products.
 */
do_action('jigoshop\template\shop\list\before');
?>
<ul id="products" class="list-inline">
	<?php foreach($products as $product): ?>
		<?php Render::output('shop/list/product', array(
			'product' => $product,
		)); ?>
	<?php endforeach; ?>
</ul>
<?php
do_action('jigoshop\template\shop\list\after');
Render::output('shop/pagination', array(
	'product_count' => $product_count,
));
?>
