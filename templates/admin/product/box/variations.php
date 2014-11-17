<?php
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;

/**
 * @var $product Product The product.
 */
?>
<div class="clearfix">
	<button type="button" class="btn btn-default pull-right" id="add-variation"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
</div>
<ul id="product-variations" class="list-group">
	<?php if ($product instanceof Product\Variable): ?>
		<?php foreach($product->getVariations() as $variation): /** @var $variation \Jigoshop\Entity\Product */?>
			<?php Render::output('admin/product/box/variations/variation', array('variation' => $variation, 'attributes' => $product->getVariableAttributes())); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>
<?php do_action('jigoshop\product\tabs\variations'); ?>
