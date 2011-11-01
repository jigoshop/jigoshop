<?php
/**
 * Cart shortcode
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Checkout
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
function get_jigoshop_cart( $atts ) {
	return jigoshop::shortcode_wrapper('jigoshop_cart', $atts);
}

function jigoshop_cart( $atts ) {

	$errors = array();

	// Process Discount Codes
	if (isset($_POST['apply_coupon']) && $_POST['apply_coupon'] && jigoshop::verify_nonce('cart')) :

		$coupon_code = stripslashes(trim($_POST['coupon_code']));
		jigoshop_cart::add_discount($coupon_code);

	// Update Shipping
	elseif (isset($_POST['calc_shipping']) && $_POST['calc_shipping'] && jigoshop::verify_nonce('cart')) :

		unset($_SESSION['chosen_shipping_method_id']);
		$country 	= $_POST['calc_shipping_country'];
		$state 		= $_POST['calc_shipping_state'];

		$postcode 	= $_POST['calc_shipping_postcode'];

		if ($postcode && !jigoshop_validation::is_postcode( $postcode, $country )) :
			jigoshop::add_error( __('Please enter a valid postcode/ZIP.','jigoshop') );
			$postcode = '';
		elseif ($postcode) :
			$postcode = jigoshop_validation::format_postcode( $postcode, $country );
		endif;

		if ($country) :

			// Update customer location
			jigoshop_customer::set_location( $country, $state, $postcode );
			jigoshop_customer::set_shipping_location( $country, $state, $postcode );

			// Re-calc price
			jigoshop_cart::calculate_totals();

			jigoshop::add_message(  __('Shipping costs updated.', 'jigoshop') );

		else :

			jigoshop_customer::set_shipping_location( '', '', '' );

			jigoshop::add_message(  __('Shipping costs updated.', 'jigoshop') );

		endif;

	endif;

	$result = jigoshop_cart::check_cart_item_stock();
	if (is_wp_error($result)) :
		jigoshop::add_error( $result->get_error_message() );
	endif;

	jigoshop::show_messages();

	if (sizeof(jigoshop_cart::$cart_contents)==0) :
		echo '<p>'.__('Your cart is empty.', 'jigoshop').'</p>';
		echo '<p><a class="button" href="'.get_permalink(get_option('jigoshop_shop_page_id')).'">'.__('&larr; Return To Shop', 'jigoshop').'</a></p>';
		return;
	endif;

	?>
	<form action="<?php echo jigoshop_cart::get_cart_url(); ?>" method="post">
	<table class="shop_table cart" cellspacing="0">
		<thead>
			<tr>
				<th class="product-remove"></th>
				<th class="product-thumbnail"></th>
				<th class="product-name"><span class="nobr"><?php _e('Product Name', 'jigoshop'); ?></span></th>
				<th class="product-price"><span class="nobr"><?php _e('Unit Price', 'jigoshop'); ?></span></th>
				<th class="product-quantity"><?php _e('Quantity', 'jigoshop'); ?></th>
				<th class="product-subtotal"><?php _e('Price', 'jigoshop'); ?></th>
			</tr>
			<?php do_action( 'jigoshop_shop_table_cart_head' ); ?>
		</thead>
		<tbody>
			<?php
			if (sizeof(jigoshop_cart::$cart_contents)>0) :
				foreach (jigoshop_cart::$cart_contents as $cart_item_key => $values) :
					$_product = $values['data'];
					if ($_product->exists() && $values['quantity']>0) :
                        
                        $additional_description = '';
                        if($_product instanceof jigoshop_product_variation && is_array($values['variation'])) {
                            $additional_description = jigoshop_get_formatted_variation( $values['variation'] );
                        }
                        
?>
							<tr>
								<td class="product-remove"><a href="<?php echo jigoshop_cart::get_remove_url($cart_item_key); ?>" class="remove" title="<?php echo __('Remove this item.', 'jigoshop'); ?>">&times;</a></td>
								<td class="product-thumbnail"><a href="<?php echo get_permalink($values['product_id']); ?>">
						<?php
						if ($values['variation_id'] && has_post_thumbnail($values['variation_id'])) {
                            echo get_the_post_thumbnail($values['variation_id'], 'shop_tiny'); 
                        } else if (has_post_thumbnail($values['product_id'])) {
                            echo get_the_post_thumbnail($values['product_id'], 'shop_tiny'); 
                        } else {
                            echo '<img src="'.jigoshop::plugin_url(). '/assets/images/placeholder.png" alt="Placeholder" width="'.jigoshop::get_var('shop_tiny_w').'" height="'.jigoshop::get_var('shop_tiny_h').'" />'; 
                        }
                        ?>
							
                                </a></td>

								<td class="product-name">
									<a href="<?php echo get_permalink($values['product_id']); ?>"><?php echo apply_filters('jigoshop_cart_product_title', $_product->get_title(), $_product); ?></a>
									<?php echo $additional_description; ?>
								</td>
								<td class="product-price"><?php echo jigoshop_price($_product->get_price()); ?></td>
								<td class="product-quantity"><div class="quantity"><input name="cart[<?php echo $cart_item_key?>][qty]" value="<?php echo $values['quantity']; ?>" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div></td>
								<td class="product-subtotal"><?php echo jigoshop_price($_product->get_price()*$values['quantity']); ?></td>
							</tr>
                            <?php
					endif;
				endforeach;
			endif;
			
			do_action( 'jigoshop_shop_table_cart_body' );
			?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6" class="actions">
					<div class="coupon">
						<label for="coupon_code"><?php _e('Coupon', 'jigoshop'); ?>:</label> <input name="coupon_code" class="input-text" id="coupon_code" value="" />
						<input type="submit" class="button" name="apply_coupon" value="<?php _e('Apply Coupon', 'jigoshop'); ?>" />
					</div>
					<?php jigoshop::nonce_field('cart') ?>
					<input type="submit" class="button" name="update_cart" value="<?php _e('Update Shopping Cart', 'jigoshop'); ?>" /> <a href="<?php echo jigoshop_cart::get_checkout_url(); ?>" class="checkout-button button-alt"><?php _e('Proceed to Checkout &rarr;', 'jigoshop'); ?></a>
				</td>
			</tr>
			<?php if ( count( jigoshop_cart::$applied_coupons ) ) : ?>
				<tr>
					<td colspan="6" class="applied-coupons">
						<div>
							<span class="applied-coupons-label"><?php _e('Applied Discount Coupons: ','jigoshop'); ?></span>
							<span class="applied-coupons-values"><?php echo implode( ',', jigoshop_cart::$applied_coupons ); ?></span>
						</div>
					</td>
				</tr>
			<?php 
			endif;
		
			do_action( 'jigoshop_shop_table_cart_foot' ); 
			?>
		</tfoot>
		<?php do_action( 'jigoshop_shop_table_cart' ); ?>
	</table>
	</form>
	<div class="cart-collaterals">

		<?php do_action('cart-collaterals'); ?>

		<div class="cart_totals">
		<?php
		// Hide totals if customer has set location and there are no methods going there
		$available_methods = jigoshop_shipping::get_available_shipping_methods();
		if ($available_methods || !jigoshop_customer::get_shipping_country() || !jigoshop_shipping::is_enabled() ) :
			?>
			<h2><?php _e('Cart Totals', 'jigoshop'); ?></h2>
			<table cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<th class="cart-row-subtotal-title"><?php _e('Subtotal', 'jigoshop'); ?></th>
						<td class="cart-row-subtotal"><?php echo jigoshop_cart::get_cart_subtotal(); ?></td>
					</tr>

					<?php if (jigoshop_cart::get_cart_shipping_total()) : ?><tr>
						<th class="cart-row-shipping-title"><?php _e('Shipping', 'jigoshop'); ?> <small><?php echo jigoshop_countries::shipping_to_prefix().' '.jigoshop_countries::$countries[ jigoshop_customer::get_shipping_country() ]; ?></small></th>
						<td class="cart-row-shipping"><?php echo jigoshop_cart::get_cart_shipping_total(); ?> <small><?php echo jigoshop_cart::get_cart_shipping_title(); ?></small></td>
					</tr><?php endif; ?>
					<?php if (jigoshop_cart::get_cart_tax()) : ?><tr>
						<th class="cart-row-tax-title"><?php _e('Tax', 'jigoshop'); ?> <?php if (jigoshop_customer::is_customer_outside_base()) : ?><small><?php echo sprintf(__('estimated for %s', 'jigoshop'), jigoshop_countries::estimated_for_prefix() . jigoshop_countries::$countries[ jigoshop_countries::get_base_country() ] ); ?></small><?php endif; ?></th>
						<td class="cart-row-tax"><?php
							echo jigoshop_cart::get_cart_tax();
						?></td>
					</tr><?php endif; ?>

					<?php if (jigoshop_cart::get_total_discount()) : ?><tr class="discount">
						<th class="cart-row-discount-title"><?php _e('Discount', 'jigoshop'); ?></th>
						<td class="cart-row-discount">-<?php echo jigoshop_cart::get_total_discount(); ?></td>
					</tr><?php endif; ?>
					<tr>
						<th class="cart-row-total-title"><strong><?php _e('Total', 'jigoshop'); ?></strong></th>
						<td class="cart-row-total"><strong><?php echo jigoshop_cart::get_total(); ?></strong></td>
					</tr>
				</tbody>
			</table>

			<?php
			else :
				echo '<p>'.__('Sorry, it seems that there are no available shipping methods to your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'jigoshop').'</p>';
			endif;
		?>
		</div>

		<?php jigoshop_shipping_calculator(); ?>

	</div>
	<?php
}