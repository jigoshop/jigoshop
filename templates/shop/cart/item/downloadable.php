<?php
use Jigoshop\Helper\Order;
use Jigoshop\Helper\Product;

/**
 * @var $cart \Jigoshop\Frontend\Cart Cart object.
 * @var $key string Cart item key.
 * @var $item \Jigoshop\Entity\Order\Item Cart item to display.
 * @var $showWithTax bool Whether to show product price with or without tax.
 */
?>
<?php
$product = $item->getProduct();
$url = apply_filters('jigoshop\cart\product_url', get_permalink($product->getId()), $key);
// TODO: Support for "Prices includes tax"
$price = $showWithTax ? $item->getPrice() + $item->getTotalTax() / $item->getQuantity() : $item->getPrice();
?>
<tr data-id="<?php echo $key; ?>" data-product="<?php echo $product->getId(); ?>">
	<td class="product-remove">
		<a href="<?php echo Order::getRemoveLink($key); ?>" class="remove" title="<?php echo __('Remove', 'jigoshop'); ?>">&times;</a>
	</td>
	<td class="product-thumbnail"><a href="<?php echo $url; ?>"><?php echo Product::getFeaturedImage($product, 'shop_tiny'); ?></a></td>
	<td class="product-name"><a href="<?php echo $url; ?>"><?php echo $product->getName(); ?></a></td>
	<td class="product-price"><?php echo Product::formatPrice($price); ?></td>
	<td class="product-quantity"><input type="number" name="cart[<?php echo $key; ?>]" value="<?php echo $item->getQuantity(); ?>" /></td>
	<td class="product-subtotal"><?php echo Product::formatPrice($item->getQuantity() * $price); ?></td>
</tr>
