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
<div id="product-variations">
<!--	--><?php //foreach($attributes as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Attributes\Attribute */?>
<!--		--><?php //Render::output('admin/product/box/attributes/attribute', array('attribute' => $attribute)); ?>
<!--	--><?php //endforeach; ?>
</div>
<?php do_action('jigoshop\product\tabs\variations'); ?>
