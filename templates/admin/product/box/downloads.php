<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 */
?>
<fieldset>
	<?php
	Forms::text(array(
		'name' => 'product[url]',
		'label' => __('File path', 'jigoshop'),
		'classes' => array('product-downloadable'),
		'placeholder' => __('Enter file URL...', 'jigoshop'),
		'value' => $product instanceof Product\Downloadable ? $product->getUrl() : '',
	));
	?>
	<?php
	Forms::text(array(
		'name' => 'product[limit]',
		'type' => 'number',
		'label' => __('Downloads limit', 'jigoshop'),
		'description' => __('Leave empty for unlimited downloads.', 'jigoshop'),
		'classes' => array('product-downloadable'),
		'placeholder' => 0,
		'value' => $product instanceof Product\Downloadable ? $product->getLimit() : '',
	));
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\downloads', $product); ?>
