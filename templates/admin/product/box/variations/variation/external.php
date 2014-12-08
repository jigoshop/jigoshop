<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 * @var $parent Product\Variable Parent of current variation..
 * @var $variation Product\Variable\Variation The variation.
 */
?>
<?php
Forms::text(array(
	'name' => 'product[variation]['.$variation->getId().'][product][url]',
	'label' => __('Product URL', 'jigoshop'),
	'classes' => array('product-external', $product instanceof Product\External ? '' : 'not-active'),
	'placeholder' => __('Enter external product URL...', 'jigoshop'),
	'value' => $product instanceof Product\External ? $product->getUrl() : '',
));
?>
