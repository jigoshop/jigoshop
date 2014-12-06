<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attributes\StockStatus;

/**
 * @var $product Product The product.
 */
$stock = $product instanceof Product\Purchasable ? $product->getStock() : new StockStatus();
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
			StockStatus::IN_STOCK => __('In stock', 'jigoshop'),
			StockStatus::OUT_STOCK => __('Out of stock', 'jigoshop'),
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
			StockStatus::BACKORDERS_FORBID => __('Do not allow', 'jigoshop'),
			StockStatus::BACKORDERS_NOTIFY => __('Allow, but notify customer', 'jigoshop'),
			StockStatus::BACKORDERS_ALLOW => __('Allow', 'jigoshop')
		),
	));
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\stock', $product); ?>
