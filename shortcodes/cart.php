<?php

function get_jigoshop_cart( $atts ) {
	return jigoshop::shortcode_wrapper('jigoshop_cart', $atts);
}

function jigoshop_cart( $atts ) {
	
	$errors = array();
	
	// Process Discount Codes
	if (isset($_POST['apply_coupon']) && $_POST['apply_coupon'] && jigoshop::verify_nonce('cart')) :
	
		$coupon_code = stripslashes(trim($_POST['coupon_code']));
		jigoshop_cart::add_discount($coupon_code);

	// Remove from cart
	elseif ( isset($_GET['remove_item']) && $_GET['remove_item'] > 0  && jigoshop::verify_nonce('cart', '_GET')) :
	
		jigoshop_cart::set_quantity( $_GET['remove_item'], 0 );
		
		jigoshop::add_message( __('Cart updated.', 'jigoshop') );
	
	// Update Cart
	elseif (isset($_POST['update_cart']) && $_POST['update_cart']  && jigoshop::verify_nonce('cart')) :
		
		$cart_totals = $_POST['cart'];
		
		if (sizeof(jigoshop_cart::$cart_contents)>0) : 
			foreach (jigoshop_cart::$cart_contents as $item_id => $values) :
				
				if (isset($cart_totals[$item_id]['qty'])) jigoshop_cart::set_quantity( $item_id, $cart_totals[$item_id]['qty'] );
				
			endforeach;
		endif;
		
		jigoshop::add_message( __('Cart updated.', 'jigoshop') );
	
	// Update Shipping
	elseif (isset($_POST['calc_shipping']) && $_POST['calc_shipping'] && jigoshop::verify_nonce('cart')) :

		unset($_SESSION['_chosen_method_id']);
		$country 	= $_POST['calc_shipping_country'];
		$state 	= $_POST['calc_shipping_state'];
		
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
		</thead>
		<tbody>
			<?php
			if (sizeof(jigoshop_cart::$cart_contents)>0) : 
				foreach (jigoshop_cart::$cart_contents as $item_id => $values) :
					$_product = $values['data'];
					if ($_product->exists() && $values['quantity']>0) :
						echo '
							<tr>
								<td class="product-remove"><a href="'.jigoshop_cart::get_remove_url($item_id).'" class="remove" title="Remove this item">&times;</a></td>
								<td class="product-thumbnail"><a href="'.get_permalink($item_id).'">';
						
						if (has_post_thumbnail($item_id)) echo get_the_post_thumbnail($item_id, 'shop_tiny'); 
						else echo '<img src="'.jigoshop::plugin_url(). '/assets/images/placeholder.png" alt="Placeholder" width="'.jigoshop::get_var('shop_tiny_w').'" height="'.jigoshop::get_var('shop_tiny_h').'" />'; 
							
						echo '	</a></td>
								<td class="product-name"><a href="'.get_permalink($item_id).'">'.$_product->get_title().'</a></td>
								<td class="product-price">'.jigoshop_price($_product->get_price()).'</td>
								<td class="product-quantity"><div class="quantity"><input name="cart['.$item_id.'][qty]" value="'.$values['quantity'].'" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div></td>
								<td class="product-subtotal">'.jigoshop_price($_product->get_price()*$values['quantity']).'</td>
							</tr>';
					endif;
				endforeach; 
			endif;
			
			do_action( 'jigoshop_shop_table_cart' );
			?>
			<tr>
				<td colspan="6" class="actions">
					<div class="coupon">
						<label for="coupon_code"><?php _e('Coupon', 'jigoshop'); ?>:</label> <input name="coupon_code" class="input-text" id="coupon_code" value="" /> <input type="submit" class="button" name="apply_coupon" value="<?php _e('Apply Coupon', 'jigoshop'); ?>" />
					</div>
					<?php jigoshop::nonce_field('cart') ?>
					<input type="submit" class="button" name="update_cart" value="<?php _e('Update Shopping Cart', 'jigoshop'); ?>" /> <a href="<?php echo jigoshop_cart::get_checkout_url(); ?>" class="checkout-button button-alt"><?php _e('Proceed to Checkout &rarr;', 'jigoshop'); ?></a>
				</td>
			</tr>
		</tbody>
	</table>
	</form>
	<div class="cart-collaterals">
		
		<?php do_action('cart-collaterals'); ?>

		<div class="cart_totals">
		<?php
		// Hide totals if customer has set location and there are no methods going there
		$available_methods = jigoshop_shipping::get_available_shipping_methods();
		if ($available_methods || !jigoshop_customer::get_shipping_country() || !jigoshop_shipping::$enabled ) : 
			?>
			<h2><?php _e('Cart Totals', 'jigoshop'); ?></h2>
			<table cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<th><?php _e('Subtotal', 'jigoshop'); ?></th>
						<td><?php echo jigoshop_cart::get_cart_subtotal(); ?></td>
					</tr>
					
					<?php if (jigoshop_cart::get_cart_shipping_total()) : ?><tr>
						<th><?php _e('Shipping', 'jigoshop'); ?> <small><?php echo jigoshop_countries::shipping_to_prefix().' '.jigoshop_countries::$countries[ jigoshop_customer::get_shipping_country() ]; ?></small></th>
						<td><?php echo jigoshop_cart::get_cart_shipping_total(); ?> <small><?php echo jigoshop_cart::get_cart_shipping_title(); ?></small></td>
					</tr><?php endif; ?>
					<?php if (jigoshop_cart::get_cart_tax()) : ?><tr>
						<th><?php _e('Tax', 'jigoshop'); ?> <?php if (jigoshop_customer::is_customer_outside_base()) : ?><small><?php echo sprintf(__('estimated for %s', 'jigoshop'), jigoshop_countries::estimated_for_prefix() . jigoshop_countries::$countries[ jigoshop_countries::get_base_country() ] ); ?></small><?php endif; ?></th>
						<td><?php 
							echo jigoshop_cart::get_cart_tax(); 
						?></td>
					</tr><?php endif; ?>
					
					<?php if (jigoshop_cart::get_total_discount()) : ?><tr class="discount">
						<th><?php _e('Discount', 'jigoshop'); ?></th>
						<td>-<?php echo jigoshop_cart::get_total_discount(); ?></td>
					</tr><?php endif; ?>
					<tr>
						<th><strong><?php _e('Total', 'jigoshop'); ?></strong></th>
						<td><strong><?php echo jigoshop_cart::get_total(); ?></strong></td>
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