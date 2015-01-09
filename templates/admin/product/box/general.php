<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product as ProductHelper;

/**
 * @var $product Product The product.
 */
?>
<fieldset>
	<?php
	Forms::text(array(
		'name' => 'product[regular_price]',
		'label' => __('Price', 'jigoshop').' ('.Currency::symbol().')',
		'placeholder' => __('Price not announced', 'jigoshop'),
		'classes' => array('product-simple', $product instanceof Product\Simple ? '' : 'not-active'),
		'value' => $product instanceof Product\Simple ? $product->getRegularPrice() : 0,
	));
	Forms::text(array(
		'name' => 'product[sku]',
		'label' => __('SKU', 'jigoshop'),
		'value' => $product->getSku(),
		'placeholder' => $product->getId(),
	));
	Forms::text(array(
		'name' => 'product[brand]',
		'label' => __('Brand', 'jigoshop'),
		'value' => $product->getBrand(),
	));
	Forms::text(array(
		'name' => 'product[gtin]',
		'label' => __('GTIN', 'jigoshop'),
		'tip' => 'Global Trade Item Number',
		'value' => $product->getGtin(),
	));
	Forms::text(array(
		'name' => 'product[mpn]',
		'label' => __('MPN', 'jigoshop'),
		'tip' => 'Manufacturer Part Number',
		'value' => $product->getMpn(),
	));
	?>
</fieldset>
<fieldset>
	<?php
	Forms::text(array(
		'name' => 'product[size_weight]',
		'label' => __('Weight', 'jigoshop').' ('.ProductHelper::weightUnit().')',
		'value' => $product->getSize()->getWeight(),
	));
	Forms::text(array(
		'name' => 'product[size_length]',
		'label' => __('Length', 'jigoshop').' ('.ProductHelper::dimensionsUnit().')',
		'value' => $product->getSize()->getLength(),
	));
	Forms::text(array(
		'name' => 'product[size_width]',
		'label' => __('Width', 'jigoshop').' ('.ProductHelper::dimensionsUnit().')',
		'value' => $product->getSize()->getWidth(),
	));
	Forms::text(array(
		'name' => 'product[size_height]',
		'label' => __('Height', 'jigoshop').' ('.ProductHelper::dimensionsUnit().')',
		'value' => $product->getSize()->getHeight(),
	));
	?>
</fieldset>
<fieldset>
	<?php
	Forms::select(array(
		'name' => 'product[visibility]',
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
		'name' => 'product[featured]',
		'label' => __('Featured?', 'jigoshop'),
		'checked' => $product->isFeatured(),
		'description' => __('Enable this option to feature this product', 'jigoshop'),
	));
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\general', $product); ?>
