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
					/** @var \Jigoshop\Entity\Product $product */
					$product = $item['item'];
					$url = apply_filters('jigoshop\cart\product_url', get_permalink($product->getId()), $key);
					?>
				<tr data-id="<?php echo $key; ?>" data-product="<?php echo $product->getId(); ?>">
					<td class="product-remove">
						<a href="<?php echo esc_url($cart->getRemoveUrl($key)); ?>" class="remove" title="<?php echo __('Remove this item.', 'jigoshop'); ?>">&times;</a>
					</td>
					<td class="product-thumbnail"><a href="<?php echo $url; ?>"><?php echo Product::getFeaturedImage($product, 'shop_tiny'); ?></a></td>
					<td class="product-name"><a href="<?php echo $url; ?>"><?php echo $product->getName(); ?></a></td>
					<td class="product-price"><?php echo Product::formatPrice($item['price'] + $item['tax']); ?></td>
					<td class="product-quantity"><input type="number" name="cart[<?php echo $key; ?>]" value="<?php echo $item['quantity']; ?>" /></td>
					<td class="product-subtotal"><?php echo Product::formatPrice($item['quantity'] * ($item['price'] + $item['tax'])); ?></td>
				</tr>
				<?php endforeach; ?>
				<?php do_action('jigoshop\cart\table_body', $cart); ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="6">
						<noscript>
							<button type="submit" class="btn btn-success pull-right" name="action" value="update-cart"><?php _e('Update Shopping Cart', 'jigoshop'); ?></button>
						</noscript>
					</td>
				</tr>
			</tfoot>
		</table>
		<div id="cart-collaterals">
			<?php do_action('cart-collaterals', $cart); ?>
			<div id="cart-totals" class="panel panel-default pull-right">
				<div class="panel-heading"><h2 class="panel-title"><?php _e('Cart Totals', 'jigoshop'); ?></h2></div>
				<table class="table">
					<tbody>
					<tr>
						<th scope="row"><?php _e('Subtotal', 'jigoshop'); ?></th>
						<td><?php echo Product::formatPrice($cart->getSubtotal()); ?></td>
					</tr>
					<?php foreach ($cart->getTax() as $taxClass => $tax): ?>
						<?php if ($tax == 0) continue; ?>
						<tr>
							<th scope="row"><?php echo $cart->getTaxLabel($taxClass); ?></th>
							<td><?php echo Product::formatPrice($tax); ?></td>
						</tr>
					<?php endforeach; ?>
					<tr id="cart-total">
						<th scope="row"><?php _e('Total', 'jigoshop'); ?></th>
						<td><?php echo Product::formatPrice($cart->getTotal()); ?></td>
					</tr>
					</tbody>
				</table>
			<?php /*
			// Hide totals if customer has set location and there are no methods going there
			$available_methods = jigoshop_shipping::get_available_shipping_methods();

			if ($available_methods || !jigoshop_customer::get_shipping_country() || !jigoshop_shipping::is_enabled()):
				do_action('jigoshop_before_cart_totals');
				?>
				<h2><?php _e('Cart Totals', 'jigoshop'); ?></h2>
				<div class="cart_totals_table">
					<table cellspacing="0" cellpadding="0">
						<tbody>
						<tr>
							<?php $price_label = jigoshop_cart::show_retail_price() ? __('Retail Price', 'jigoshop') : __('Subtotal', 'jigoshop'); ?>
							<th class="cart-row-subtotal-title"><?php echo $price_label; ?></th>
							<td class="cart-row-subtotal"><?php echo jigoshop_cart::get_cart_subtotal(true, false, true); ?></td>
						</tr>
						<?php if (jigoshop_cart::get_cart_shipping_total()): ?>
							<tr>
								<th class="cart-row-shipping-title"><?php _e('Shipping', 'jigoshop'); ?>
									<small><?php echo _x('To: ', 'shipping destination', 'jigoshop').__(jigoshop_customer::get_shipping_country_or_state(), 'jigoshop'); ?></small>
								</th>
								<td class="cart-row-shipping"><?php echo jigoshop_cart::get_cart_shipping_total(true, true); ?>
									<small><?php echo jigoshop_cart::get_cart_shipping_title(); ?></small>
								</td>
							</tr>
						<?php endif; ?>
						<?php if (jigoshop_cart::show_retail_price() && $options->get('jigoshop_prices_include_tax') == 'no'): ?>
							<tr>
								<th class="cart-row-subtotal-title"><?php _e('Subtotal', 'jigoshop'); ?></th>
								<td class="cart-row-subtotal"><?php echo jigoshop_cart::get_cart_subtotal(true, true); ?></td>
							</tr>
						<?php elseif (jigoshop_cart::show_retail_price()): ?>
							<tr>
								<th class="cart-row-subtotal-title"><?php _e('Subtotal', 'jigoshop'); ?></th>
								<?php
								$price = jigoshop_cart::$cart_contents_total_ex_tax + jigoshop_cart::$shipping_total;
								$price = jigoshop_price($price, array('ex_tax_label' => 1));
								?>
								<td class="cart-row-subtotal"><?php echo $price; ?></td>
							</tr>
						<?php endif; ?>
						<?php if (jigoshop_cart::tax_after_coupon()): ?>
							<tr class="discount">
								<th class="cart-row-discount-title"><?php _e('Discount', 'jigoshop'); ?></th>
								<td class="cart-row-discount">-<?php echo jigoshop_cart::get_total_discount(); ?></td>
							</tr>
						<?php endif; ?>
						<?php if ($options->get('jigoshop_calc_taxes') == 'yes'):
							foreach (jigoshop_cart::get_applied_tax_classes() as $tax_class):
								if (jigoshop_cart::get_tax_for_display($tax_class)) : ?>
									<tr data-tax="<?php echo $tax_class; ?>">
										<th class="cart-row-tax-title"><?php echo jigoshop_cart::get_tax_for_display($tax_class) ?></th>
										<td class="cart-row-tax"><?php echo jigoshop_cart::get_tax_amount($tax_class) ?></td>
									</tr>
								<?php
								endif;
							endforeach;
						endif; ?>
						<?php if (!jigoshop_cart::tax_after_coupon() && jigoshop_cart::get_total_discount()): ?>
							<tr class="discount">
								<th class="cart-row-discount-title"><?php _e('Discount', 'jigoshop'); ?></th>
								<td class="cart-row-discount">-<?php echo jigoshop_cart::get_total_discount(); ?></td>
							</tr>
						<?php endif; ?>
						<tr>
							<th class="cart-row-total-title"><strong><?php _e('Total', 'jigoshop'); ?></strong></th>
							<td class="cart-row-total"><strong><?php echo jigoshop_cart::get_total(); ?></strong></td>
						</tr>
						</tbody>
					</table>
				</div>
				<?php
				do_action('jigoshop_after_cart_totals');
			else :
				echo '<p>'.__(jigoshop_shipping::get_shipping_error_message(), 'jigoshop').'</p>';
			endif;
			?>
		</div>
		<?php
		do_action('jigoshop_before_shipping_calculator');
		jigoshop_shipping_calculator();
		do_action('jigoshop_after_shipping_calculator');
		//*/ ?>
			</div>
		</div>
		<?php /* if (false && $options->get('jigoshop_cart_shows_shop_button') == 'yes') : ?>
			<tr>
				<td colspan="6" class="actions">
					<a href="<?php echo esc_url(jigoshop_cart::get_shop_url()); ?>" class="checkout-button button-alt" style="float:left;"><?php _e('&larr; Return to Shop', 'jigoshop'); ?></a>
					<a href="<?php echo esc_url(jigoshop_cart::get_checkout_url()); ?>" class="checkout-button button-alt"><?php _e('Proceed to Checkout &rarr;', 'jigoshop'); ?></a>
				</td>
			</tr>
		<?php endif; //*/ ?>
		<a href="<?php echo $shopUrl; ?>" class="btn btn-default pull-left"><?php _e('&larr; Return to shopping', 'jigoshop'); ?></a>
		<button class="btn btn-primary pull-right" name="action" value="checkout"><?php _e('Proceed to checkout &rarr;', 'jigoshopp'); ?></button>
	</form>
<?php endif; ?>
