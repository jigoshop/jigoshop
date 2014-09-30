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
		<?php echo Product::getFeaturedImage($product, 'shop_small'); ?>
		<strong><?php echo $product->getName(); ?></strong>
		<?php do_action('jigoshop\shop\list\product\after_title', $product); ?>
	</a>
	<span class="price"><?php echo Product::getPrice($product); ?></span>
	<form action="" method="post" class="form-inline" role="form">
		<!-- TODO: Render proper form based on product type -->
		<input type="hidden" name="action" value="add-to-cart" />
		<input type="hidden" name="item" value="<?php echo $product->getId(); ?>" />
		<button class="btn btn-primary" type="submit"><?php _e('Add to cart', 'jigoshop'); ?></button>
	</form>
	<?php do_action('jigoshop\shop\list\product\after', $product); ?>
</li>
