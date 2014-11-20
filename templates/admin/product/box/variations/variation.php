<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\Helper\Product;
use Jigoshop\Entity\Product\Attribute;

/**
 * @var $variation \Jigoshop\Entity\Product Product to display.
 * @var $attributes array List of attributes for variation
 */
?>
<li class="list-group-item" data-id="<?php echo $variation->getId(); ?>">
	<h4 class="list-group-item-heading">
		<button type="button" class="remove-variation btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
		<?php foreach($attributes as $attribute): /** @var $attribute Attribute */?>
			<?php Forms::select(array(
				'name' => 'product[variation]['.$variation->getId().'][attribute]['.$attribute->getId().']',
				'classes' => array('variation-attribute'),
				'placeholder' => $attribute->getLabel(),
				'value' => $variation->getAttribute($attribute->getId())->getValue(),
				'options' => Product::getSelectOption($attribute->getOptions(), sprintf(__('Any of %s', 'jigoshop'), $attribute->getLabel())),
				'size' => 12,
			)); ?>
		<?php endforeach; ?>
	</h4>
	<div class="list-group-item-text clearfix">
		<div class="col-md-5"></div>
		<div class="col-md-6"></div>
	</div>
</li>
