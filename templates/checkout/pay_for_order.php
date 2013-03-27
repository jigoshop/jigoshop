<?php
/**
 * Pay for order form template
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
?>

<?php global $order; 
      $jigoshop_options = Jigoshop_Base::get_options();?>
<form id="order_review" method="post">

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
                <?php if (($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax())
                        || ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0)) : ?>
                    <td colspan="2"><?php _e('Retail Price', 'jigoshop'); ?></td>
                <?php else : ?>
                    <td colspan="2"><?php _e('Subtotal', 'jigoshop'); ?></td>
                <?php endif; ?>
                <td><?php echo $order->get_subtotal_to_display(); ?></td>
            </tr>
            <?php
            if ($order->order_shipping > 0) :
                ?><tr>
                    <td colspan="2"><?php _e('Shipping', 'jigoshop'); ?></td>
                    <td><?php echo $order->get_shipping_to_display(); ?></small></td>
                </tr><?php
            endif;
            if ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0) : ?>
                <tr class="discount">
                    <td colspan="2"><?php _e('Discount', 'jigoshop'); ?></td>
                    <td>-<?php echo jigoshop_price($order->order_discount); ?></td>
                </tr>
                <?php 
            endif;
            if (($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax())
              || ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0)) : 
                ?><tr>
                    <td colspan="2"><?php _e('Subtotal', 'jigoshop'); ?></td>
                    <td><?php echo jigoshop_price($order->order_discount_subtotal); ?></td>
                </tr>
                <?php
            endif;
            if ($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes') :
                foreach ($order->get_tax_classes() as $tax_class) :
                    if ($order->show_tax_entry($tax_class)) : ?>
                        <tr>
                            <td colspan="2"><?php echo $order->get_tax_class_for_display($tax_class) . ' (' . (float) $order->get_tax_rate($tax_class) . '%):'; ?></td>
                            <td><?php echo $order->get_tax_amount($tax_class) ?></td>
                        </tr>
                        <?php
                    endif;
                endforeach;
            endif; 
            if ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'no' && $order->order_discount > 0) : ?><tr class="discount">
                    <td colspan="2"><?php _e('Discount', 'jigoshop'); ?></td>
                    <td>-<?php echo jigoshop_price($order->order_discount); ?></td>
                </tr><?php endif; ?>
            <tr>
                <td colspan="2"><strong><?php _e('Grand Total', 'jigoshop'); ?></strong></td>
                <td><strong><?php echo jigoshop_price($order->order_total); ?></strong></td>
            </tr>
        </tfoot>
        <tbody>
            <?php
            if (sizeof($order->items) > 0) :
                foreach ($order->items as $item) :
                    echo '
						<tr>
							<td>' . $item['name'] . '</td>
							<td>' . $item['qty'] . '</td>
							<td>' . jigoshop_price($item['cost']) . '</td>
						</tr>';
                endforeach;
            endif;
            ?>
        </tbody>
    </table>

    <div id="payment">
        <?php if ($order->order_total > 0) : ?>
            <ul class="payment_methods methods">
                <?php
                $available_gateways = jigoshop_payment_gateways::get_available_payment_gateways();
                if ($available_gateways) :
                    // Chosen Method
                    if (sizeof($available_gateways))
                        current($available_gateways)->set_current();
                    foreach ($available_gateways as $gateway) :
                        ?>
                        <li>
                            <input type="radio" id="payment_method_<?php echo $gateway->id; ?>" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php if ($gateway->chosen)
                echo 'checked="checked"'; ?> />
                            <label for="payment_method_<?php echo $gateway->id; ?>"><?php echo $gateway->title; ?> <?php echo $gateway->icon(); ?></label>
                            <?php
                            if ($gateway->has_fields || $gateway->description) :
                                echo '<div class="payment_box payment_method_' . esc_attr( $gateway->id  ) . '" style="display:none;">';
                                $gateway->payment_fields();
                                echo '</div>';
                            endif;
                            ?>
                        </li>
                        <?php
                    endforeach;
                else :

                    echo '<p>' . __('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'jigoshop') . '</p>';

                endif;
                ?>
            </ul>
        <?php endif; ?>

        <div class="form-row">
            <?php jigoshop::nonce_field('pay') ?>
            <input type="submit" class="button-alt" name="pay" id="place_order" value="<?php _e('Pay for order', 'jigoshop'); ?>" />

        </div>

    </div>

</form>