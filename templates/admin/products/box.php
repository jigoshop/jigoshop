<?php
/**
 * @var $product \Jigoshop\Entity\Product Product object.
 * @var $types array List of available types.
 * @var $menu array Menu items to display.
 * @var $tabs array List of tabs to display.
 */
?>
<div class="jigoshop">
	<div class="form-horizontal">
		<?php \Jigoshop\Helper\Forms::select(array(
			'id' => 'product-type',
			'name' => 'product[type]',
			'label' => __('Product type', 'jigoshop'),
			'options' => $types,
			'value' => $product->getType(),
		)); ?>
		<ul class="jigoshop_product_data nav nav-tabs" role="tablist">
			<?php foreach ($menu as $id => $label): ?>
			<li class="<?= $id; ?><?php $id == $current_tab and print ' active'; ?>">
				<a href="#<?= $id; ?>" data-toggle="tab"><?= $label; ?></a>
			</li>
			<?php endforeach; ?>
		</ul>
		<div class="tab-content">
			<?php foreach($tabs as $id => $environment): ?>
			<div class="tab-pane<?php $id == $current_tab and print ' active'; ?>" id="<?= $id; ?>">
				<?php \Jigoshop\Helper\Render::output('admin/products/box/'.$id, $environment); ?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
