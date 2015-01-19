<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Product;

/**
 * @var $variation \Jigoshop\Entity\Product\Variable\Variation Variation to display.
 * @var $attributes array List of attributes for variation.
 * @var $allowedSubtypes array List of types allowed as variations.
 */
$product = $variation->getProduct();
?>
<li class="list-group-item variation" data-id="<?php echo $variation->getId(); ?>">
	<h4 class="list-group-item-heading">
		<button type="button" class="remove-variation btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
		<button type="button" class="show-variation btn btn-default pull-right" title="<?php _e('Show', 'jigoshop'); ?>"><span class="glyphicon glyphicon-collapse-down"></span></button>
		<?php foreach($attributes as $attribute): /** @var $attribute Attribute */ $value = $variation->getAttribute($attribute->getId());?>
			<?php Forms::select(array(
				'name' => 'product[variation]['.$variation->getId().'][attribute]['.$attribute->getId().']',
				'classes' => array('variation-attribute'),
				'placeholder' => $attribute->getLabel(),
				'value' => $value !== null ? $value->getValue() : '',
				'options' => ProductHelper::getSelectOption($attribute->getOptions(), sprintf(__('Any of %s', 'jigoshop'), $attribute->getLabel())),
				'size' => 12,
			)); ?>
		<?php endforeach; ?>
	</h4>
	<div class="list-group-item-text row clearfix">
		<div class="col-md-2">
			<?php echo Product::getFeaturedImage($product, \Jigoshop\Core\Options::IMAGE_SMALL); ?>
			<button class="btn btn-default set_variation_image"><?php _e('Set image', 'jigoshop'); ?></button>
			<button class="btn btn-danger remove_variation_image<?php !Product::hasFeaturedImage($product) and print ' not-active'; ?>"><?php _e('Remove image', 'jigoshop'); ?></button>
		</div>
		<div class="col-md-10">
			<fieldset>
			<?php
			Forms::select(array(
				'name' => 'product[variation]['.$variation->getId().'][product][type]',
				'classes' => array('variation-type'),
				'label' => __('Type', 'jigoshop'),
				'value' => $product->getType(),
				'options' => $allowedSubtypes,
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][regular_price]',
				'label' => __('Price', 'jigoshop'),
				'placeholder' => __('Price not announced', 'jigoshop'),
				'value' => $product->getPrice(),
				'size' => 11,
			));
			?>
			</fieldset>
			<fieldset>
			<?php
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][sku]',
				'label' => __('SKU', 'jigoshop'),
				'value' => $product->getSku(),
				'placeholder' => $variation->getParent()->getId().' - '.$variation->getId(),
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][brand]',
				'label' => __('Brand', 'jigoshop'),
				'value' => $product->getBrand(),
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][gtin]',
				'label' => __('GTIN', 'jigoshop'),
				'tip' => 'Global Trade Item Number',
				'value' => $product->getGtin(),
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][mpn]',
				'label' => __('MPN', 'jigoshop'),
				'tip' => 'Manufacturer Part Number',
				'value' => $product->getMpn(),
				'size' => 11,
			));
			?>
			</fieldset>
			<fieldset>
			<?php
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][stock_stock]',
				'label' => __('Stock', 'jigoshop'),
				'value' => $product->getStock()->getStock(),
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][sales_price]',
				'label' => __('Sale price', 'jigoshop'),
				'value' => $product->getSales()->getPrice(),
				'placeholder' => ProductHelper::formatNumericPrice(0),
				'size' => 11,
			));
			?>
			</fieldset>
			<?php do_action('jigoshop\admin\variation', $variation, $product); ?>
		</div>
	</div>
</li>
