<?php
/**
 * Review order form template
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigowatt
 * @copyright           Copyright � 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */
?>
<div id="order_review">

    <table class="shop_table">
        <thead>
            <tr>
                <th><?php _e('Product', 'jigoshop'); ?></th>
                <th><?php _e('Qty', 'jigoshop'); ?></th>
                <th><?php _e('Totals', 'jigoshop'); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <?php if ((get_option('jigoshop_calc_taxes') == 'yes' && jigoshop_cart::has_compound_tax()) 
                       || (get_option('jigoshop_tax_after_coupon') == 'yes' && jigoshop_cart::get_total_discount())) : ?>
                    <td colspan="2"><?php _e('Retail Price', 'jigoshop'); ?></td>
                <?php else : ?>
                    <td colspan="2"><?php _e('Subtotal', 'jigoshop'); ?></td>
                <?php endif; ?>
                <td><?php echo jigoshop_cart::get_cart_subtotal(); ?></td>
            </tr>
            <?php
            jigoshop_checkout::get_shipping_dropdown();
            if (get_option('jigoshop_tax_after_coupon') == 'yes' && jigoshop_cart::get_total_discount()) : ?>
                <tr class="discount">
                    <td colspan="2"><?php _e('Discount', 'jigoshop'); ?></td>
                    <td>-<?php echo jigoshop_cart::get_total_discount(); ?></td>
                </tr>
                <?php 
            endif;
            if ((get_option('jigoshop_calc_taxes') == 'yes' && jigoshop_cart::has_compound_tax()) 
                       || (get_option('jigoshop_tax_after_coupon') == 'yes' && jigoshop_cart::get_total_discount())) : ?>
                <tr>
                    <td colspan="2"><?php _e('Subtotal', 'jigoshop'); ?></td>
                    <td><?php echo jigoshop_cart::get_cart_subtotal(true, true); ?></td>
                </tr>
                <?php
            endif;
            if (get_option('jigoshop_calc_taxes') == 'yes') :
                foreach (jigoshop_cart::get_applied_tax_classes() as $tax_class) : 
                    if (jigoshop_cart::get_tax_for_display($tax_class)) : ?>                    
                        <tr>
                            <td colspan="2"><?php echo jigoshop_cart::get_tax_for_display($tax_class); ?></th>
                            <td><?php echo jigoshop_cart::get_tax_amount($tax_class) ?></td>
                        </tr>
                        <?php
                    endif;
                endforeach;
            endif;
            ?>
            <?php do_action('jigoshop_after_review_order_items'); ?>
            <?php if (get_option('jigoshop_tax_after_coupon') == 'no' && jigoshop_cart::get_total_discount()) : ?><tr class="discount">
                    <td colspan="2"><?php _e('Discount', 'jigoshop'); ?></td>
                    <td>-<?php echo jigoshop_cart::get_total_discount(); ?></td>
                </tr><?php endif; ?>
            <tr>
                <td colspan="2"><strong><?php _e('Grand Total', 'jigoshop'); ?></strong></td>
                <td><strong><?php echo jigoshop_cart::get_total(); ?></strong></td>
            </tr>
        </tfoot>
        <tbody>
            <?php
            if (sizeof(jigoshop_cart::$cart_contents) > 0) :
                foreach (jigoshop_cart::$cart_contents as $item_id => $values) :
                    $_product = $values['data'];
                    if ($_product->exists() && $values['quantity'] > 0) :
                    
                        $variation = jigoshop_cart::get_item_data($values);
						
						$customization = '';
						if ( !empty( $values['variation_id'] )) {
							$product_id = $values['variation_id'];
						} else {
							$product_id = $values['product_id'];
						}
						$custom_products = (array) jigoshop_session::instance()->customized_products;
						$custom = isset( $custom_products[$product_id] ) ? $custom_products[$product_id] : '';
						
						if ( ! empty( $custom_products[$product_id] ) ) :
						
							$custom = $custom_products[$product_id];
							$label = apply_filters( 'jigoshop_customized_product_label', __(' Personal: ','jigoshop') );
							$customization = '<dl class="customization">';
							$customization = '<dt class="customized_product_label">';
							$customization .= $label . '</dt>';
							$customization .= '<dd class="customized_product">';
							$customization .= $custom . '</dd></dl>';
						endif;

                        echo '
                            <tr>
                                <td class="product-name">' . $_product->get_title() . $variation . $customization . '</td>
								<td>' . $values['quantity'] . '</td>
								<td>' . jigoshop_price($_product->get_price_excluding_tax($values['quantity']), array('ex_tax_label' => 1)) . '</td>
							</tr>';
					endif;
				endforeach;
			endif;
			?>
		</tbody>
	</table>

	<div id="payment">
		
		<ul class="payment_methods methods">
			<?php
				$available_gateways = jigoshop_payment_gateways::get_available_payment_gateways();
				if ($available_gateways) :
                    
                    $gateway_set = false;
					foreach ($available_gateways as $gateway ) :
                        if (jigoshop_checkout::process_gateway($gateway)) :
                            if (!$gateway_set) :
                                
                                // Chosen Method
                                if (sizeof($available_gateways)) :
                                    if( isset( $_POST[ 'payment_method' ] ) && isset( $available_gateways[ $_POST['payment_method'] ] ) ) :
                                        $available_gateways[ $_POST[ 'payment_method' ] ]->set_current();
                                    else :
                                        $gateway->set_current();
                                    endif;
                                endif;
                                $gateway_set = true;
                                    
                            endif;
    						?>
                            <li>
                            <input type="radio" id="payment_method_<?php echo $gateway->id; ?>" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php if ($gateway->chosen) echo 'checked="checked"'; ?> />
                            <label for="payment_method_<?php echo $gateway->id; ?>"><?php echo $gateway->title; ?> <?php echo apply_filters('gateway_icon', $gateway->icon(), $gateway->id); ?></label>
                                <?php
                                    if ($gateway->has_fields || $gateway->description) :
                                        echo '<div class="payment_box payment_method_' . esc_attr( $gateway->id ) . '" style="display:none;">';
                                        $gateway->payment_fields();
                                        echo '</div>';
                                    endif;
                                ?>
                            </li>
                            <?php
                        endif;
					endforeach;
				else :

					if ( !jigoshop_customer::get_country() ) :
						echo '<p>'.__('Please fill in your details above to see available payment methods.', 'jigoshop').'</p>';
					else :
						echo '<p>'.__('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'jigoshop').'</p>';
					endif;

				endif;
			?>
		</ul>
		

		<div class="form-row">

			<noscript><?php _e('Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'jigoshop'); ?><br/><input type="submit" class="button-alt" name="update_totals" value="<?php _e('Update totals', 'jigoshop'); ?>" /></noscript>

			<?php jigoshop::nonce_field('process_checkout')?>

			<?php do_action( 'jigoshop_review_order_before_submit' ); ?>

			<?php if (jigoshop_get_page_id('terms')>0) : ?>
			<p class="form-row terms">
				<label for="terms" class="checkbox"><?php _e('I accept the', 'jigoshop'); ?> <a href="<?php echo esc_url( get_permalink(jigoshop_get_page_id('terms')) ); ?>" target="_blank"><?php _e('terms &amp; conditions', 'jigoshop'); ?></a></label>
				<input type="checkbox" class="input-checkbox" name="terms" <?php if (isset($_POST['terms'])) echo 'checked="checked"'; ?> id="terms" />
			</p>
			<?php endif; ?>

            <a href="<?php echo home_url(); ?>" class="button cancel"><?php echo apply_filters( 'jigoshop_order_cancel_button_text', __( 'Cancel', 'jigoshop') ) ?></a>

			<?php $order_button_text = apply_filters( 'jigoshop_order_button_text', __( 'Place order', 'jigoshop') ); ?>
			<input type="submit" class="button-alt" name="place_order" id="place_order" value="<?php echo esc_attr( $order_button_text ); ?>" />

			<?php do_action( 'jigoshop_review_order_after_submit' ); ?>

		</div>

	</div>

</div>