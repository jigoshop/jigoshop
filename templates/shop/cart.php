<?php
use Jigoshop\Core\Pages;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $productService \Jigoshop\Service\ProductServiceInterface Product service.
 * @var $content string Contents of cart page
 * @var $cart \Jigoshop\Frontend\Cart Cart object.
 * @var $shopUrl string Url to sh
 */
?>
<h1><?php _e('Cart', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<?php echo wpautop(wptexturize($content)); ?>
<?php if ($cart->isEmpty()): ?>
	<div class="alert alert-info text-center" id="cart">
		<p><?php _e('Your cart is empty.', 'jigoshop'); ?></p>
		<a href="<?php echo $shopUrl; ?>" class="btn btn-primary"><?php _e('Return to shop', 'jigoshop'); ?></a>
	</div>
<?php else: ?>
	<form id="cart" role="form" action="" method="post">
		<table class="table table-hover">
			<thead>
				<tr>
					<th class="product-remove"></th>
					<th class="product-thumbnail"></th>
					<th class="product-name"><?php _e('Product Name', 'jigoshop'); ?></th>
					<th class="product-price"><?php _e('Unit Price', 'jigoshop'); ?></th>
					<th class="product-quantity"><?php _e('Quantity', 'jigoshop'); ?></th>
					<th class="product-subtotal"><?php _e('Price', 'jigoshop'); ?></th>
				</tr>
				<?php do_action('jigoshop\cart\table_head', $cart); ?>
			</thead>
			<tbody>
				<?php foreach($cart->getItems() as $key => $item): ?>
					<?php
					$product = $productService->findForState($item['item']);
					$url = apply_filters('jigoshop\cart\product_url', get_permalink($product->getId()), $key);
					?>
				<tr data-id="<?php echo $key; ?>" data-product="<?php echo $product->getId(); ?>">
					<td class="product-remove">
						<a href="<?php echo esc_url($cart->getRemoveUrl($key)); ?>" class="remove" title="<?php echo __('Remove this item.', 'jigoshop'); ?>">&times;</a>
					</td>
					<td class="product-thumbnail"><a href="<?php echo $url; ?>"><?php echo Product::getFeaturedImage($product, 'shop_tiny'); ?></a></td>
					<td class="product-name"><a href="<?php echo $url; ?>"><?php echo $product->getName(); ?></a></td>
					<td class="product-price"><?php echo Product::formatPrice($product->getPrice()); ?></td>
					<td class="product-quantity"><input type="number" name="cart[<?php echo $key; ?>]" value="<?php echo $item['quantity']; ?>" /></td>
					<td class="product-subtotal"><?php echo Product::formatPrice($item['quantity'] * $product->getPrice()); ?></td>
				</tr>
				<?php endforeach; ?>
				<?php do_action('jigoshop\cart\table_body', $cart); ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="6">
						<noscript>
							<button type="submit" class="btn btn-success pull-right"><?php _e('Update Shopping Cart', 'jigoshop'); ?></button>
						</noscript>
					</td>
				</tr>
				<?php if (false && $options->get('jigoshop_cart_shows_shop_button') == 'yes') : ?>
					<tr>
						<td colspan="6" class="actions">
							<a href="<?php echo esc_url(jigoshop_cart::get_shop_url()); ?>" class="checkout-button button-alt" style="float:left;"><?php _e('&larr; Return to Shop', 'jigoshop'); ?></a>
							<a href="<?php echo esc_url(jigoshop_cart::get_checkout_url()); ?>" class="checkout-button button-alt"><?php _e('Proceed to Checkout &rarr;', 'jigoshop'); ?></a>
						</td>
					</tr>
				<?php endif; ?>
			</tfoot>
		</table>
	</form>
<?php endif; ?>
