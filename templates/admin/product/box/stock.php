<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 */
?>
<fieldset>
	<?php
	Forms::checkbox(array(
		'name' => 'product[stock][manage]',
		'id' => 'stock-manage',
		'label' => __('Manage stock?', 'jigoshop'),
		'checked' => $product->getStock()->getManage(),
	));
	Forms::select(array(
		'name' => 'product[stock][status]',
		'id' => 'stock-status',
		'label' => __('Status', 'jigoshop'),
		'value' => $product->getStock()->getStatus(),
		'options' => array(
			Product\Attributes\StockStatus::IN_STOCK => __('In stock', 'jigoshop'),
			Product\Attributes\StockStatus::OUT_STOCK => __('Out of stock', 'jigoshop'),
		),
		'classes' => array($product->getStock()->getManage() ? 'not-active' : ''),
	));
	?>
</fieldset>
<fieldset class="stock-status" style="<?php !$product->getStock()->getManage() and print 'display: none;'; ?>">
	<?php
	Forms::text(array(
		'name' => 'product[stock][stock]',
		'label' => __('Items in stock', 'jigoshop'),
		'value' => $product->getStock()->getStock(),
	));
	?>
	<?php
	Forms::select(array(
		'name' => 'product[stock][allow_backorders]',
		'label' => __('Allow backorders?', 'jigoshop'),
		'value' => $product->getStock()->getAllowBackorders(),
		'options' => array(
			'no' => __('Do not allow', 'jigoshop'),
			'notify' => __('Allow, but notify customer', 'jigoshop'),
			'yes' => __('Allow', 'jigoshop')
		),
	));
	?>
</fieldset>
