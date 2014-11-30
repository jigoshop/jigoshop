<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 */
$stock = $product instanceof Product\Purchasable ? $product->getStock() : new Product\Attributes\StockStatus();
?>
<fieldset>
	<?php
	Forms::checkbox(array(
		'name' => 'product[stock_manage]',
		'id' => 'stock-manage',
		'label' => __('Manage stock?', 'jigoshop'),
		'checked' => $stock->getManage(),
	));
	Forms::select(array(
		'name' => 'product[stock_status]',
		'id' => 'stock-status',
		'label' => __('Status', 'jigoshop'),
		'value' => $stock->getStatus(),
		'options' => array(
			Product\Attributes\StockStatus::IN_STOCK => __('In stock', 'jigoshop'),
			Product\Attributes\StockStatus::OUT_STOCK => __('Out of stock', 'jigoshop'),
		),
		'classes' => array($stock->getManage() ? 'not-active' : ''),
	));
	?>
</fieldset>
<fieldset class="stock-status" style="<?php !$stock->getManage() and print 'display: none;'; ?>">
	<?php
	Forms::text(array(
		'name' => 'product[stock_stock]',
		'label' => __('Items in stock', 'jigoshop'),
		'value' => $stock->getStock(),
	));
	?>
	<?php
	Forms::select(array(
		'name' => 'product[stock_allow_backorders]',
		'label' => __('Allow backorders?', 'jigoshop'),
		'value' => $stock->getAllowBackorders(),
		'options' => array(
			'no' => __('Do not allow', 'jigoshop'),
			'notify' => __('Allow, but notify customer', 'jigoshop'),
			'yes' => __('Allow', 'jigoshop')
		),
	));
	?>
</fieldset>
