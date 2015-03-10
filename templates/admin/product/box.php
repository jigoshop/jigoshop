<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Render;

/**
 * @var $product \Jigoshop\Entity\Product Product object.
 * @var $types array List of available types.
 * @var $menu array Menu items to display.
 * @var $tabs array List of tabs to display.
 */

/**
 * Checks if selected tab is to be hidden or not.
 *
 * @param $options mixed List of options.
 * @return bool
 */
$isHidden = function($options) use ($product) {
	return $options['visible'] !== true && !in_array($product->getType(), $options['visible']);
};
?>
<div class="jigoshop" data-id="<?php echo $product->getId(); ?>">
	<div id="messages"></div>
	<div class="form-horizontal">
		<?php Forms::select(array(
			'id' => 'product-type',
			'name' => 'product[type]',
			'label' => __('Product type', 'jigoshop'),
			'options' => $types,
			'value' => $product->getType(),
			'size' => 10,
		)); ?>
		<ul class="jigoshop_product_data nav nav-tabs" role="tablist">
			<?php foreach ($menu as $id => $options): ?>
			<li class="<?php echo $id; ?><?php $id == $current_tab and print ' active'; ?><?php $isHidden($options) and print ' not-active' ?>">
				<a href="#<?php echo $id; ?>" data-toggle="tab"><?php echo $options['label']; ?></a>
			</li>
			<?php endforeach; ?>
		</ul>
		<div class="tab-content">
			<?php foreach($tabs as $id => $environment): ?>
			<div class="tab-pane<?php $id == $current_tab and print ' active'; ?>" id="<?php echo $id; ?>">
				<?php if (is_array($environment)): ?>
					<?php Render::output('admin/product/box/'.$id, $environment); ?>
				<?php else: ?>
					<?php echo $environment; ?>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
