<?php
use Jigoshop\Core\Options;
use Jigoshop\Helper\Product;

/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $cart \Jigoshop\Entity\Cart
 * @var $cart_url string
 * @var $checkout_url string
 */

$view_cart_label = isset($instance['view_cart_button']) ? $instance['view_cart_button'] : __('View Cart', 'jigoshop');
$checkout_label = isset($instance['checkout_button']) ? $instance['checkout_button'] : __('Checkout', 'jigoshop');

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<?php if (!$cart->isEmpty()): ?>
<ul class="cart_list">
	<?php foreach ($cart->getItems() as $item): /** @var $item \Jigoshop\Entity\Order\Item */?>
	<li>
		<a href="<?php echo $item->getProduct()->getLink(); ?>">
			<?php echo Product::getFeaturedImage($item->getProduct(), Options::IMAGE_TINY); ?>
			<span class="js_widget_product_title"><?php echo $item->getProduct()->getName(); ?></span>
		</a>
		<?php
		// Displays variations and cart item meta
		echo jigoshop_cart::get_item_data($value);
		?>
		<span class="js_widget_product_price"><?php echo $item->getQuantity().' &times; '.Product::formatPrice($item->getPrice()); ?></span>
	</li>
	<?php endforeach; ?>
</ul>
<p class="total">
	<strong><?php _e('Subtotal', 'jigoshop'); ?>:</strong>
	<?php echo Product::formatPrice($cart->getTotal()); ?>
	<?php do_action('jigoshop_widget_cart_before_buttons'); ?>
	<p class="buttons">
		<a href="<?php echo $cart_url; ?>" class="btn btn-default"><?php _e($view_cart_label, 'jigoshop'); ?></a>
		<a href="<?php echo $checkout_url; ?>" class="btn btn-primary"><?php _e($checkout_label, 'jigoshop'); ?></a>
	</p>
</p>
<?php else: ?>
	<span class="empty"><?php _e('No products in the cart.', 'jigoshop'); ?></span>
<?php endif; ?>
<?php echo $after_widget;
