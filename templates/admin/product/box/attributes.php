<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Render;

/**
 * @var $product \Jigoshop\Entity\Product The product.
 * @var $availableAttributes array List of available attributes.
 * @var $attributes array List of attributes attached to current product.
 */
?>
<div class="form-inline">
	<?php Forms::select(array(
		'placeholder' => __('Select attribute...', 'jigoshop'),
		'name' => 'new_attribute',
		'id' => 'new-attribute',
		'options' => $availableAttributes,
		'size' => 13,
		'value' => false,
	)); ?>
	<?php Forms::text(array(
		'placeholder' => __('Enter attribute name...', 'jigoshop'),
		'name' => 'new_attribute_label',
		'id' => 'new-attribute-label',
		'size' => 13,
		'value' => '',
		'classes' => array('not-active'),
	)); ?>
	<button type="button" class="btn btn-default pull-right" id="add-attribute"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
</div>
<ul id="product-attributes" class="list-group clearfix">
	<?php foreach($attributes as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Attribute */?>
		<?php Render::output('admin/product/box/attributes/attribute', array('attribute' => $attribute)); ?>
	<?php endforeach; ?>
</ul>
<?php do_action('jigoshop\product\tabs\attributes', $product); ?>
