<?php
/**
 * @var $variation \Jigoshop\Entity\Product\Variable\Variation
 */
?>
<dl class="dl-horizontal variation-data">
	<?php foreach ($variation->getAttributes() as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Variable\Attribute */?>
		<dt><?php echo $attribute->getAttribute()->getLabel(); ?></dt>
		<dd><?php echo $attribute->printValue($item); ?></dd>
	<?php endforeach; ?>
</dl>
