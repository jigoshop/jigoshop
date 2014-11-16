<?php

/**
 * @var $product \Jigoshop\Entity\Product The product.
 */
?>
<button type="button" class="btn btn-default pull-right" id="add-variation"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
<ul id="product-variations" class="list-group clearfix">
	<?php if ($product instanceof \Jigoshop\Entity\Product\Variable): ?>
		<?php foreach($product->getVariations() as $variation): /** @var $variation \Jigoshop\Entity\Product */?>
			<?php Render::output('admin/product/box/variations/variation', array('variation' => $variation)); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>
<?php do_action('jigoshop\product\tabs\variations'); ?>
