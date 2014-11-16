<?php
/**
 * @var $currentTab string Currently selected tab.
 * @var $product \Jigoshop\Entity\Product Currently displayed product.
 */
?>
<div role="tabpanel" id="tab-attributes" class="tab-pane<?php $currentTab == 'attributes' and print ' active'; ?>">
	<dl class="dl-horizontal">
		<?php foreach($product->getVisibleAttributes() as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Attribute */?>
			<dt><?php echo $attribute->getLabel(); ?></dt>
			<dd><?php echo $attribute->printValue(); ?></dd>
		<?php endforeach; ?>
	</dl>
</div>
