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
<a href="<?php echo get_pagenum_link(2); ?>">Next</a>
<?php do_action('jigoshop_pagination'); // TODO: Render pagination ?>