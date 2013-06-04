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
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
 
$jigoshop_options = Jigoshop_Base::get_options(); ?>
<div id="order_review">

    <table class="shop_table">
        <thead>
            <tr>
				<th><?php _e('Product', 'jigoshop'); ?></th>
				<th><?php _e('Qty'    , 'jigoshop'); ?></th>
				<th><?php _e('Totals' , 'jigoshop'); ?></th>
            </tr>
        </thead>
        <tfoot>
			<tr>
			   <?php $price_label = jigoshop_cart::show_retail_price() ? __('Retail Price', 'jigoshop') : __('Subtotal', 'jigoshop'); ?>

				<td colspan="2"><?php echo $price_label; ?></td>
				<td class="cart-row-subtotal"><?php echo jigoshop_cart::get_cart_subtotal(true,false,true); ?></td>
			</tr>

            <?php jigoshop_checkout::get_shipping_dropdown(); ?>

            <?php if ( jigoshop_cart::show_retail_price() && Jigoshop_Base::get_options()->get_option( 'jigoshop_prices_include_tax' ) == 'no' ) : ?>
                <tr>
                    <td colspan="2"><?php _e('Subtotal', 'jigoshop'); ?></td>
                    <td><?php echo jigoshop_cart::get_cart_subtotal(true, true); ?></td>
                </tr>
			<?php elseif ( jigoshop_cart::show_retail_price() ) : ?>
				<tr>
					<td colspan="2"><?php _e('Subtotal', 'jigoshop'); ?></th>
					<?php
					$price = jigoshop_cart::$cart_contents_total_ex_tax + jigoshop_cart::$shipping_total;
					$price = jigoshop_price($price, array('ex_tax_label' => 1));
					?>
					<td><?php echo $price; ?></td>
				</tr>
            <?php endif; ?>

            <?php if ( jigoshop_cart::tax_after_coupon() ) : ?>
                <tr class="discount">
                    <td colspan="2"><?php _e('Discount', 'jigoshop'); ?></td>
                    <td>-<?php echo jigoshop_cart::get_total_discount(); ?></td>
                </tr>
			<?php endif; ?>

            <?php if ($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes') :
                foreach (jigoshop_cart::get_applied_tax_classes() as $tax_class) :
                      if (jigoshop_cart::get_tax_for_display($tax_class)) : ?>
                        <tr>
                            <td colspan="2"><?php echo jigoshop_cart::get_tax_for_display($tax_class); ?></td>
                            <td><?php echo jigoshop_cart::get_tax_amount($tax_class) ?></td>
                        </tr>
				<?php endif;
				endforeach;
			endif; ?>

            <?php do_action('jigoshop_after_review_order_items'); ?>

				<?php if ( !jigoshop_cart::tax_after_coupon() && jigoshop_cart::get_total_discount() ) : ?>
				<tr class="discount">
					<td colspan="2"><?php _e('Discount', 'jigoshop'); ?></td>
					<td>-<?php echo jigoshop_cart::get_total_discount(); ?></td>
				</tr>
				<?php endif; ?>

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

						$customization   = '';
						$custom_products = (array) jigoshop_session::instance()->customized_products;
						$product_id      = !empty( $values['variation_id'] )      ? $values['variation_id']       : $values['product_id'];
						$custom          = isset( $custom_products[$product_id] ) ? $custom_products[$product_id] : ''; ?>

                            <tr>
                                <td class="product-name">
									<?php echo $_product->get_title() . $variation;

									if ( ! empty( $custom ) ) :
										$label  = apply_filters( 'jigoshop_customized_product_label', __(' Personal: ','jigoshop') ); ?>

										<dl class="customization">
											<dt class="customized_product_label">
												<?php echo $label; ?>
											</dt>
											<dd class="customized_product">
												<?php echo $custom; ?>
											</dd>
										</dl>

									<?php endif; ?>
								</td>
								<td><?php echo $values['quantity']; ?></td>
								<td>
                                <?php 
                                    echo jigoshop_price($_product->get_price_excluding_tax()*$values['quantity'], array('ex_tax_label' => 1)); 
                                ?></td>   
							</tr>

					<?php endif;
				endforeach;
			endif;
			?>
		</tbody>
	</table>

	<?php $coupons = JS_Coupons::get_coupons(); if(!empty($coupons)): ?>
		<div class="coupon">
			<label for="coupon_code"><?php _e('Coupon', 'jigoshop'); ?>:</label>
				<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" />
		</div><br/>
	<?php endif; ?>

</div>