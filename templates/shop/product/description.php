<?php
/**
 * @var $currentTab string Currently selected tab.
 * @var $product \Jigoshop\Entity\Product Currently displayed product.
 */
?>
<div id="#tab-description" class="tab-pane<?php $currentTab == 'description' and print ' active'; ?>">
	<?php echo $product->getDescription(); ?>
</div>
