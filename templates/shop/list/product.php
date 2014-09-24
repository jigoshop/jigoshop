<?php
use Jigoshop\Helper\Product;

/**
 * @var $product \Jigoshop\Entity\Product Product to display.
 */
?>
<li class="product">
	<?php do_action('jigoshop\shop\list\product\before', $product); ?>
	<a href="<?php echo $product->getLink(); ?>">
		<?php do_action('jigoshop\shop\list\product\before_title', $product); ?>
		<?php if (Product::isOnSale($product)): ?>
			<span class="on-sale"><?php _e('Sale!', 'jigoshop'); ?></span>
		<?php endif; ?>
		<?php echo Product::getThumbnail($product, 'shop_small'); ?>
		<strong><?php echo $product->getName(); ?></strong>
		<?php do_action('jigoshop\shop\list\product\after_title', $product); ?>
	</a>
	<span class="price"><?php echo Product::getPrice($product); ?></span>
	<?php do_action('jigoshop\shop\list\product\after', $product); ?>
</li>
