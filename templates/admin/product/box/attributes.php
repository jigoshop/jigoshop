<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $product \Jigoshop\Entity\Product The product.
 * @var $availableAttributes array List of available attributes.
 * @var $attributes array List of attributes attached to current product.
 */
?>
<?php Forms::select(array(
	'placeholder' => __('Select attribute...', 'jigoshop'),
	'name' => 'new_attribute',
	'id' => 'new-attribute',
	'options' => $availableAttributes,
	'size' => 9,
	'value' => false,
)); ?>
<button type="button" class="btn btn-default pull-right" id="add-attribute"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
<?php foreach($attributes as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Attributes\Attribute */?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo $attribute->getLabel(); ?></h3>
			<button type="button" class="remove-attribute btn btn-default" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
		</div>
		<div class="panel-body">

		</div>
	</div>
<?php endforeach; ?>
<?php do_action('jigoshop\product\tabs\attributes'); ?>
