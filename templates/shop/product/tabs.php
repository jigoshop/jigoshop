<?php
/**
 * @var $currentTab string Currently selected tab.
 * @var $product \Jigoshop\Entity\Product Currently displayed product.
 */
?>
<?php if (!empty($tabs)): ?>
<div id="tabs">
	<ul class="nav nav-tabs tabs" role="tablist">
		<?php foreach($tabs as $tab => $label): ?>
			<li<?php $tab == $currentTab and print ' class="active"'; ?>><a href="#tab-<?php echo $tab; ?>" role="tab" data-toggle="tab"><?php echo $label; ?></a></li>
		<?php endforeach; ?>
		<?php do_action('jigoshop\template\product\tabs', $currentTab, $product); ?>
	</ul>
	<div class="tab-content">
		<?php do_action('jigoshop\template\product\tab_panels', $currentTab, $product); ?>
	</div>
</div>
<?php endif; ?>
