<?php
/**
 * @var $products array List of products to display
 */
?>
<ul id="products">
	<?php foreach($products as $product): ?>
		<?php \Jigoshop\Helper\Render::output('shop/list/product', array(
			'product' => $product,
		)); ?>
	<?php endforeach; ?>
</ul>