<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 * @var $taxClasses array Available tax classes.
 */
?>
<fieldset>
	<?php
	Forms::checkbox(array(
		'name' => 'product[is_taxable]',
		'id' => 'is_taxable',
		'label' => __('Is taxable?', 'jigoshop'),
		'checked' => $product->isTaxable(),
	));
	Forms::select(array(
		'name' => 'product[tax_classes]',
		'id' => 'tax_classes',
		'label' => __('Tax classes', 'jigoshop'),
		'multiple' => true,
		'value' => $product->getTaxClasses(),
		'options' => $taxClasses,
		'classes' => array($product->isTaxable() ? '' : 'not-active'),
	));
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\advanced', $product); ?>
