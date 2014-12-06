<?php
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;

/**
 * @var $product Product The product.
 * @var $allowedSubtypes array List of types allowed as variations.
 */
?>
<div class="clearfix">
	<button type="button" class="btn btn-default pull-right" id="add-variation"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
</div>
<ul id="product-variations" class="list-group">
	<?php if ($product instanceof Product\Variable): ?>
		<?php foreach($product->getVariations() as $variation): /** @var $variation \Jigoshop\Entity\Product */?>
			<?php Render::output('admin/product/box/variations/variation', array(
				'variation' => $variation,
				'attributes' => $product->getVariableAttributes(),
				'allowedSubtypes' => $allowedSubtypes,
			)); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>
<!-- TODO: Default selections -->
<noscript>
	<style type="text/css">
		.jigoshop #product-variations .list-group-item-text {
			display: block;
		}
		.jigoshop #product-variations .show-variation {
			display: none;
		}
	</style>
</noscript>
<?php do_action('jigoshop\product\tabs\variations', $product); ?>
