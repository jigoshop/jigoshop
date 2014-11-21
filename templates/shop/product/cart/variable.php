<?php
use Jigoshop\Helper\Product;

/**
 * @var $product \Jigoshop\Entity\Product\Variable Product to add.
 */

?>
<form action="" method="post" class="form" role="form">
	<input type="hidden" name="action" value="add-to-cart" />
	<?php foreach ($product->getVariableAttributes() as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Attribute */ ?>
		<?php \Jigoshop\Helper\Forms::select(array(
			'name' => 'attributes['.$attribute->getId().']',
			'classes' => array('product-attribute'),
			'label' => $attribute->getLabel(),
			'options' => Product::getSelectOption($attribute->getOptions(), ''),
			// TODO: Default selections
			'placeholder' => __('Please select…', 'jigoshop'),
		)); ?>
	<?php endforeach; ?>
	<div id="buttons">
		<p class="price"><?php _e('Current price:', 'jigoshop'); ?> <span></span></p>
		<?php \Jigoshop\Helper\Forms::text(array(
			'id' => 'product-quantity',
			'name' => 'quantity',
			'type' => 'number',
			'label' => __('Quantity', 'jigoshop'),
			'value' => 1,
		)); ?>
		<button class="btn btn-primary" type="submit"><?php _e('Add to cart', 'jigoshop'); ?></button>
	</div>
</form>
