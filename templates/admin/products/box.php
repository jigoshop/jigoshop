<?php
/**
 * @var $product \Jigoshop\Entity\Product Product object.
 * @var $types array List of available types.
 * @var $menu array Menu items to display.
 * @var $tabs array List of tabs to display.
 */
?>
<div class="panels">
	<div class="jigoshop_product_data_type">
		<label for="product-type" class="product-type-label"><?= __('Product Type', 'jigoshop'); ?></label>
		<select id="product-type" name="product[type]">
		<?php foreach ($types as $value => $label): ?>
			<option value="<?= $value; ?>" <?php selected($product->getType(), $value); ?>><?= $label; ?></option>
		<?php endforeach; ?>
		</select>
		<div class="clear"></div>
	</div>
	<ul class="jigoshop_product_data tabs">
		<?php foreach ($menu as $id => $label): ?>
		<li class="<?= $id; ?>"><a href="#<?= $id; ?>"><?= $label; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php foreach($tabs as $id => $environment): ?>
	<div id="<?= $id; ?>" class="panel jigoshop_options_panel">
		<?php \Jigoshop\Helper\Render::output('admin/products/box/'.$id, $environment); ?>
	</div>
	<?php endforeach; ?>
</div>
