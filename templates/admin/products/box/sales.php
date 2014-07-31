<?php
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Forms;

/**
 * @var $product Product The product.
 */
$enabled = false;
$price = '0';
$from = time();
$to = time();

if($product->isType(Product\Simple::TYPE)){
	/** @var Product\Simple $product */
	$enabled = $product->getSales()->isEnabled();
	$price = $product->getSales()->getPrice();
	$from = $product->getSales()->getFrom()->getTimestamp();
	$to = $product->getSales()->getTo()->getTimestamp();
}
?>
<fieldset>
	<?php
	Forms::checkbox(array(
		'name' => 'product[sales][enabled]',
		'id' => 'sales-enabled',
		'label' => __('Put product on sale?', 'jigoshop'),
		'value' => $enabled,
	));
	?>
</fieldset>
<fieldset class="schedule<?php !$enabled and print ' hide'; ?>">
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
		'label' => __('From', 'jigoshop'),
		'value' => $from,
	));
	Forms::text(array(
		'name' => 'product[sales][to]',
		'label' => __('To', 'jigoshop'),
		'value' => $to,
	));
	?>
</fieldset>
