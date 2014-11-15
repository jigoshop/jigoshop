<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 */
$enabled = false;
$price = '';
$from = time();
$to = time();

if($product->isType(Product\Simple::TYPE)){
	/** @var Product\Simple $product */
	$enabled = $product->getSales()->isEnabled();
	$price = $product->getSales()->getPrice();
	$from = $product->getSales()->getFrom()->format('m/d/Y');
	$to = $product->getSales()->getTo()->format('m/d/Y');
}
?>
<fieldset>
	<?php
	Forms::checkbox(array(
		'name' => 'product[sales][enabled]',
		'id' => 'sales-enabled',
		'label' => __('Put product on sale?', 'jigoshop'),
		'checked' => $enabled,
	));
	?>
</fieldset>
<fieldset class="schedule" style="<?php !$enabled and print 'display: none;'; ?>">
	<h3><?php _e('Schedule', 'jigoshop'); ?></h3>
	<?php
	Forms::text(array(
		'name' => 'product[sales][price]',
		'label' => __('Sale price', 'jigoshop'),
		'value' => $price,
		'placeholder' => __('15% or 19.99', 'jigoshop'),
	));
	Forms::text(array(
		'name' => 'product[sales][from]',
		'id' => 'sales-from',
		'label' => __('From', 'jigoshop'),
		'value' => $from,
	));
	Forms::text(array(
		'name' => 'product[sales][to]',
		'id' => 'sales-to',
		'label' => __('To', 'jigoshop'),
		'value' => $to,
	));
	?>
</fieldset>
