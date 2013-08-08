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
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
function get_jigoshop_cart($atts) {
    return jigoshop_shortcode_wrapper('jigoshop_cart', $atts);
}

function jigoshop_cart($atts) {

    $errors = array();
    unset(jigoshop_session::instance()->selected_rate_id);

    // Process Discount Codes
    if (isset($_POST['apply_coupon']) && $_POST['apply_coupon'] && jigoshop::verify_nonce('cart')) :

        $coupon_code = sanitize_title( $_POST['coupon_code'] );
        jigoshop_cart::add_discount($coupon_code);

    // Update Shipping
    elseif (isset($_POST['calc_shipping']) && $_POST['calc_shipping'] && jigoshop::verify_nonce('cart')) :

        unset( jigoshop_session::instance()->chosen_shipping_method_id );
        $country = $_POST['calc_shipping_country'];
        $state = $_POST['calc_shipping_state'];

        $postcode = $_POST['calc_shipping_postcode'];

        if ($postcode && !jigoshop_validation::is_postcode($postcode, $country)) :
            jigoshop::add_error(__('Please enter a valid postcode/ZIP.', 'jigoshop'));
            $postcode = '';
        elseif ($postcode) :
            $postcode = jigoshop_validation::format_postcode($postcode, $country);
        endif;

        if ($country) :

            // Update customer location
            jigoshop_customer::set_location($country, $state, $postcode);
            jigoshop_customer::set_shipping_location($country, $state, $postcode);

            jigoshop::add_message(__('Shipping costs updated.', 'jigoshop'));

        else :

            jigoshop_customer::set_shipping_location('', '', '');

            jigoshop::add_message(__('Shipping costs updated.', 'jigoshop'));

        endif;

    elseif (isset($_POST['shipping_rates'])) :

        $rates_params = explode(":", $_POST['shipping_rates']);
        $available_methods = jigoshop_shipping::get_available_shipping_methods();
        $shipping_method = $available_methods[$rates_params[0]];

        if ($rates_params[1] != NULL) :
            jigoshop_session::instance()->selected_rate_id = $rates_params[1];
        endif;

        $shipping_method->choose(); // choses the method selected by user.

    endif;

    // Re-Calc prices. This needs to happen every time the cart page is loaded and after checking post results. It will happen twice for coupon.
    jigoshop_cart::calculate_totals();

    $result = jigoshop_cart::check_cart_item_stock();
    if (is_wp_error($result)) :
        jigoshop::add_error($result->get_error_message());
    endif;

    jigoshop::show_messages();

    if (sizeof(jigoshop_cart::$cart_contents) == 0) :
        echo '<p>' . __('Your cart is empty.', 'jigoshop') . '</p>';
		?><p><a href="<?php echo esc_url( jigoshop_cart::get_shop_url() ); ?>" class="button"><?php _e('&larr; Return to Shop', 'jigoshop'); ?></a></p><?php
        return;
    endif;
    ?>
    <form action="<?php echo esc_url( jigoshop_cart::get_cart_url() ); ?>" method="post">
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
                <?php do_action('jigoshop_shop_table_cart_head'); ?>
            </thead>
            <tbody>
                <?php
                if (sizeof(jigoshop_cart::$cart_contents) > 0) :
                    foreach (jigoshop_cart::$cart_contents as $cart_item_key => $values) :
                        $_product = $values['data'];
                        if ($_product->exists() && $values['quantity'] > 0) :

                            $additional_description = jigoshop_cart::get_item_data($values);
                            ?>
                            <tr>
                                <td class="product-remove"><a href="<?php echo esc_url( jigoshop_cart::get_remove_url($cart_item_key) ); ?>" class="remove" title="<?php echo esc_attr( __('Remove this item.', 'jigoshop') ); ?>">&times;</a></td>
                                <td class="product-thumbnail"><a href="<?php echo esc_url( apply_filters('jigoshop_product_url_display_in_cart', get_permalink($values['product_id']), $cart_item_key) ); ?>">
                                    <?php
                                        if ($values['variation_id'] && has_post_thumbnail($values['variation_id'])) {
                                            echo get_the_post_thumbnail($values['variation_id'], 'shop_tiny');
                                        } else if (has_post_thumbnail($values['product_id'])) {
                                            echo get_the_post_thumbnail($values['product_id'], 'shop_tiny');
                                        } else {
                                            echo '<img src="' . jigoshop::assets_url() . '/assets/images/placeholder.png" alt="Placeholder" width="' . jigoshop::get_var('shop_tiny_w') . '" height="' . jigoshop::get_var('shop_tiny_h') . '" />';
                                        }
                                    ?></a>
                                </td>

                                <td class="product-name">
                                    <a href="<?php echo esc_url( apply_filters('jigoshop_product_url_display_in_cart', get_permalink($values['product_id']), $cart_item_key) ); ?>"><?php echo apply_filters('jigoshop_cart_product_title', $_product->get_title(), $_product); ?></a>
                                    <?php echo $additional_description; ?>
                                    <?php
										if ( !empty( $values['variation_id'] )) {
											$product_id = $values['variation_id'];
										} else {
											$product_id = $values['product_id'];
										}
										$custom_products = (array) jigoshop_session::instance()->customized_products;
										$custom = isset( $custom_products[$product_id] ) ? $custom_products[$product_id] : '';
										if ( ! empty( $custom_products[$product_id] ) ) :
											?>
											<dl class="customization">
												<dt class="customized_product_label"><?php echo apply_filters('jigoshop_customized_product_label', __('Personal: ','jigoshop') ); ?></dt>
												<dd class="customized_product"><?php echo esc_textarea( $custom ); ?></dd>
											</dl>
											<?php
										endif;
                                    ?>
                                </td>
                                <td class="product-price"><?php echo apply_filters( 'jigoshop_product_price_display_in_cart', jigoshop_price($_product->get_price_excluding_tax()), $values['product_id'], $values ); ?></td>
                                <td class="product-quantity">
                                	<?php ob_start(); ?>
                                     <div class="quantity"><input name="cart[<?php echo $cart_item_key ?>][qty]" value="<?php echo esc_attr( $values['quantity'] ); ?>" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div>
                                    <?php
                                    	$quantity_display = ob_get_contents();
                                    	ob_end_clean();
                                    	echo apply_filters( 'jigoshop_product_quantity_display_in_cart', $quantity_display, $values['product_id'], $values );
                                    	
                                    ?>
                                </td>
                                <td class="product-subtotal"><?php echo apply_filters( 'jigoshop_product_subtotal_display_in_cart', jigoshop_price($_product->get_price_excluding_tax() * $values['quantity']), $values['product_id'], $values ); ?></td>
                            </tr>
                            <?php
                        endif;
                    endforeach;
                endif;

                do_action('jigoshop_shop_table_cart_body');
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="actions">

                        <?php $coupons = JS_Coupons::get_coupons(); if(!empty($coupons)): ?>
                            <div class="coupon">
                                <label for="coupon_code"><?php _e('Coupon', 'jigoshop'); ?>:</label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" />
                                <input type="submit" class="button" name="apply_coupon" value="<?php _e('Apply Coupon', 'jigoshop'); ?>" />
                            </div>
                        <?php endif; ?>

                        <?php jigoshop::nonce_field('cart') ?>
                        
                        <?php if ( Jigoshop_Base::get_options()->get_option( 'jigoshop_cart_shows_shop_button' ) == 'no' ) : ?>
                        	<input type="submit" class="button" name="update_cart" value="<?php _e('Update Shopping Cart', 'jigoshop'); ?>" /> <a href="<?php echo esc_url( jigoshop_cart::get_checkout_url() ); ?>" class="checkout-button button-alt"><?php _e('Proceed to Checkout &rarr;', 'jigoshop'); ?></a>
                        <?php else : ?>
							<input type="submit" class="button" name="update_cart" value="<?php _e('Update Shopping Cart', 'jigoshop'); ?>" />
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (count(jigoshop_cart::$applied_coupons)) : ?>
                    <tr>
                        <td colspan="6" class="applied-coupons">
                            <div>
                                <span class="applied-coupons-label"><?php _e('Applied Coupons: ', 'jigoshop'); ?></span>
								<?php foreach ( jigoshop_cart::$applied_coupons as $code ) : ?>
                                <a href="?unset_coupon=<?php echo $code; ?>" id="<?php echo $code; ?>" class="applied-coupons-values"><?php echo $code; ?>
									<span class="close">&times;</span>
								</a>
								<?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php if ( Jigoshop_Base::get_options()->get_option( 'jigoshop_cart_shows_shop_button' ) == 'yes' ) : ?>
					<tr>
						<td colspan="6" class="actions">
							<a href="<?php echo esc_url( jigoshop_cart::get_shop_url() ); ?>" class="checkout-button button-alt" style="float:left;"><?php _e('&larr; Return to Shop', 'jigoshop'); ?></a>
							<a href="<?php echo esc_url( jigoshop_cart::get_checkout_url() ); ?>" class="checkout-button button-alt"><?php _e('Proceed to Checkout &rarr;', 'jigoshop'); ?></a>
						</td>
					</tr>
                <?php endif;
                do_action('jigoshop_shop_table_cart_foot');
                ?>
            </tfoot>
            <?php do_action('jigoshop_shop_table_cart'); ?>
        </table>
    </form>
    <div class="cart-collaterals">

        <?php do_action('cart-collaterals'); ?>

        <div class="cart_totals">
            <?php
            // Hide totals if customer has set location and there are no methods going there
            $available_methods = jigoshop_shipping::get_available_shipping_methods();
            $jigoshop_options = Jigoshop_Base::get_options();
            
            if ($available_methods || !jigoshop_customer::get_shipping_country() || !jigoshop_shipping::is_enabled()) :
            	do_action( 'jigoshop_before_cart_totals' );
                ?>
                <h2><?php _e('Cart Totals', 'jigoshop'); ?></h2>
				
                <div class="cart_totals_table">
                    <table cellspacing="0" cellpadding="0">
                        <tbody>

                            <tr>
							   <?php $price_label = jigoshop_cart::show_retail_price() ? __('Retail Price', 'jigoshop') : __('Subtotal', 'jigoshop'); ?>

								<th class="cart-row-subtotal-title"><?php echo $price_label; ?></th>
                                <td class="cart-row-subtotal"><?php echo jigoshop_cart::get_cart_subtotal(true,false,true); ?></td>
                            </tr>

                            <?php if (jigoshop_cart::get_cart_shipping_total()) : ?>
							<tr>
                                <th class="cart-row-shipping-title"><?php _e('Shipping', 'jigoshop'); ?> <small><?php echo _x('To: ','shipping destination','jigoshop') . __(jigoshop_customer::get_shipping_country_or_state(), 'jigoshop'); ?></small></th>
                                <td class="cart-row-shipping"><?php echo jigoshop_cart::get_cart_shipping_total(true,true); ?> <small><?php echo jigoshop_cart::get_cart_shipping_title(); ?></small></td>
                            </tr>
                            <?php endif; ?>

                            <?php if ( jigoshop_cart::show_retail_price() && Jigoshop_Base::get_options()->get_option( 'jigoshop_prices_include_tax' ) == 'no') : ?>
                                <tr>
                                    <th class="cart-row-subtotal-title"><?php _e('Subtotal', 'jigoshop'); ?></th>
                                    <td class="cart-row-subtotal"><?php echo jigoshop_cart::get_cart_subtotal(true, true); ?></td>
                                </tr>
                            <?php elseif ( jigoshop_cart::show_retail_price() ) : ?>
                                <tr>
                                    <th class="cart-row-subtotal-title"><?php _e('Subtotal', 'jigoshop'); ?></th>
                                    <?php
                                    $price = jigoshop_cart::$cart_contents_total_ex_tax + jigoshop_cart::$shipping_total;
									$price = jigoshop_price($price, array('ex_tax_label' => 1));
                                    ?>
                                    <td class="cart-row-subtotal"><?php echo $price; ?></td>
                                </tr>
                            <?php endif; ?>

                            <?php if ( jigoshop_cart::tax_after_coupon() ) : ?>
                                <tr class="discount">
                                    <th class="cart-row-discount-title"><?php _e('Discount', 'jigoshop'); ?></th>
									<td class="cart-row-discount">-<?php echo jigoshop_cart::get_total_discount(); ?></td>
                                </tr>
                            <?php endif; ?>

                            <?php if (Jigoshop_Base::get_options()->get_option('jigoshop_calc_taxes') == 'yes') :
                                foreach (jigoshop_cart::get_applied_tax_classes() as $tax_class) :
                                    if (jigoshop_cart::get_tax_for_display($tax_class)) : ?>
                                        <tr>
                                            <th class="cart-row-tax-title"><?php echo jigoshop_cart::get_tax_for_display($tax_class) ?></th>
                                            <td class="cart-row-tax"><?php echo jigoshop_cart::get_tax_amount($tax_class) ?></td>
                                        </tr>
                                    <?php
                                    endif;
                                endforeach;
                            endif; ?>

							<?php if ( !jigoshop_cart::tax_after_coupon() && jigoshop_cart::get_total_discount() ) : ?>
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
			do_action( 'jigoshop_after_cart_totals' );
			else :
                echo '<p>' . __(jigoshop_shipping::get_shipping_error_message(), 'jigoshop') . '</p>';
			endif;
		?>
		</div>

		<?php
			do_action( 'jigoshop_before_shipping_calculator' );
		?>
		<?php jigoshop_shipping_calculator(); ?>
		<?php
			do_action( 'jigoshop_after_shipping_calculator' );
		?>
	</div>
	<?php
}