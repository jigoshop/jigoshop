<?php

/**
 * @var $product \Jigoshop\Entity\Product The product.
 */
?>
<?php //Forms::select(array(
//	'placeholder' => __('Select attribute...', 'jigoshop'),
//	'name' => 'new_attribute',
//	'id' => 'new-attribute',
//	'options' => $availableAttributes,
//	'size' => 9,
//	'value' => false,
//)); ?>
<button type="button" class="btn btn-default pull-right" id="add-variation"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
<ul id="product-variations" class="list-group">
	<?php foreach($variations as $variation): /** @var $variation \Jigoshop\Entity\Product */?>
		<?php Render::output('admin/product/box/variations/variation', array('variation' => $variation)); ?>
	<?php endforeach; ?>
</ul>
<?php do_action('jigoshop\product\tabs\variations'); ?>
