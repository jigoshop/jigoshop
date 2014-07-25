<?php
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Forms;

/**
 * @var $product Product The product.
 */
?>
<fieldset>
	<?php
	Forms::select(array(
		'name' => 'visibility',
		'label' => __('Visibility', 'jigoshop'),
		'options' => array(
			Product::VISIBILITY_PUBLIC => __('Catalog & Search', 'jigoshop'),
			Product::VISIBILITY_CATALOG => __('Catalog Only', 'jigoshop'),
			Product::VISIBILITY_SEARCH => __('Search Only', 'jigoshop'),
			Product::VISIBILITY_NONE => __('Hidden', 'jigoshop')
		),
		'value' => $product->getVisibility(),
	));
	Forms::checkbox(array(
		'name' => 'featured',
		'label' => __('Featured?', 'jigoshop'),
		'value' => $product->isFeatured(),
		'description' => __('Enable this option to feature this product', 'jigoshop'),
	));
	?>
</fieldset>
<fieldset>
	<?php
	Forms::text(array(
		'name' => 'sku',
		'label' => __('SKU', 'jigoshop'),
		'value' => $product->getSku(),
		'placeholder' => $product->getId(),
	));
	do_action('jigoshop\product\tabs\general\main');
	?>
</fieldset>
<fieldset id="price_fieldset">
	<?php
	Forms::text(array(
		'name' => 'regular_price',
		'label' => __('Price', 'jigoshop'),
		'value' => $product->getRegularPrice(),
	));
	Forms::text(array(
		'name' => 'sale_price',
		'label' => __('Sale price', 'jigoshop'),
		'value' => $product->getSales()->getPrice(),
		'description' => '<a href="#" class="schedule">'.__('Schedule', 'jigoshop').'</a>',
		'placeholder' => __('15% or 19.99', 'jigoshop'),
	));
	// TODO: Add hidden fields with sales schedule
	do_action('jigoshop\product\tabs\general\pricing');
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\general\additional'); ?>
