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
	<div class="alert alert-info text-center">
		<p><?php _e('Your cart is empty.', 'jigoshop'); ?></p>
		<a href="<?php echo $shopUrl; ?>" class="alert-link"><?php _e('Return to shop', 'jigoshop'); ?></a>
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
				<?php foreach($cart->getItems() as $id => $item): ?>
					<?php
					$product = $productService->findForState($item['item']);
					$url = apply_filters('jigoshop\cart\product_url', get_permalink($product->getId()), $id);
					?>
				<tr data-id="<?php echo $id; ?>" data-product="<?php echo $product->getId(); ?>">
					<td>
						<a href="<?php echo esc_url($cart->getRemoveUrl($id)); ?>" class="remove" title="<?php echo __('Remove this item.', 'jigoshop'); ?>">&times;</a>
					</td>
					<td><a href="<?php echo $url; ?>"><?php echo Product::getFeaturedImage($product, 'shop_tiny'); ?></a></td>
					<td><a href="<?php echo $url; ?>"><?php echo $product->getName(); ?></a></td>
					<td><?php echo Product::formatPrice($product->getPrice()); ?></td>
					<td><?php echo $item['quantity']; ?></td>
					<td><?php echo Product::formatPrice($item['quantity'] * $product->getPrice()); ?></td>
				</tr>
				<?php endforeach; ?>
				<?php do_action('jigoshop\cart\table_body', $cart); ?>
			</tbody>
		</table>
	</form>
<?php endif; ?>
