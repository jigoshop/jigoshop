<?php
/**
 * Order tracking shortcode
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Customer
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

function get_jigoshop_order_tracking ($atts) {
	return jigoshop_shortcode_wrapper('jigoshop_order_tracking', $atts);
}

function jigoshop_order_tracking( $atts ) {

	extract(shortcode_atts(array(
	), $atts));

	global $post;
    $jigoshop_options = Jigoshop_Base::get_options();

	if ($_POST) :

		$order = new jigoshop_order();

		$order->id = !empty( $_POST['orderid'] ) ? $_POST['orderid'] : 0;
		if (isset($_POST['order_email']) && $_POST['order_email']) $order_email = trim($_POST['order_email']); else $order_email = '';

		if ( !jigoshop::verify_nonce('order_tracking') ):

			echo '<p>'.__('You have taken too long. Please refresh the page and retry.', 'jigoshop').'</p>';

		elseif ($order->id && $order_email && $order->get_order( apply_filters( 'jigoshop_shortcode_order_tracking_order_id', $order->id ) )) :

			if ($order->billing_email == $order_email) :
				echo '<p>'.sprintf( __('Order %s which was made %s ago and has the status "%s"', 'jigoshop'), $order->get_order_number(), human_time_diff( strtotime( $order->order_date ), current_time( 'timestamp' )), __( $order->status, 'jigoshop' ) );

				if ( $order->status == 'completed' ) {
					$completed = (array)get_post_meta( $order->id, '_js_completed_date', true );
					if ( ! empty( $completed )) $completed = $completed[0];
					else $completed = '';    // shouldn't happen, reset to be sure
					echo sprintf( __( ' was completed %s ago', 'jigoshop' ), human_time_diff( strtotime( $completed ), current_time( 'timestamp' )) );
				}
				echo '.</p>';

				do_action( 'jigoshop_tracking_details_info', $order );

				?>
				<?php do_action('jigoshop_before_track_order_details', $order->id);?>
				<h2><?php _e('Order Details', 'jigoshop'); ?></h2>
				<table class="shop_table">
					<thead>
						<tr>
							<th><?php _e('ID/SKU', 'jigoshop'); ?></th>
							<th><?php _e('Title', 'jigoshop'); ?></th>
							<th><?php _e('Price', 'jigoshop'); ?></th>
							<th><?php _e('Quantity', 'jigoshop'); ?></th>
						</tr>
					</thead>
					<tfoot>
                        <tr>
                            <?php if (($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax())
                                || ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0)) : ?>
                                <td colspan="3"><?php _e('Retail Price', 'jigoshop'); ?></td>
                            <?php else : ?>
                                <td colspan="3"><?php _e('Subtotal', 'jigoshop'); ?></td>
                            <?php endif; ?>
                                <td><?php echo $order->get_subtotal_to_display(); ?></td>
                        </tr>
                        <?php
                        if ($order->order_shipping>0) : ?>
                            <tr>
                                <td colspan="3"><?php _e('Shipping', 'jigoshop'); ?></td>
                                <td><?php echo $order->get_shipping_to_display(); ?></td>
                            </tr>
                            <?php
                        endif;

			            do_action('jigoshop_processing_fee_after_shipping');

                        if ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0) : ?>
                            <tr class="discount">
                                <td colspan="3"><?php _e('Discount', 'jigoshop'); ?></td>
                                <td>-<?php echo jigoshop_price($order->order_discount); ?></td>
                            </tr>
                            <?php
                        endif;
                        if (($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax())
                         || ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0)) :  ?>
                            <tr>
                                <td colspan="3"><?php _e('Subtotal', 'jigoshop'); ?></td>
                                <td><?php echo jigoshop_price($order->order_discount_subtotal); ?></td>
                            </tr>
                            <?php
                        endif;
                        if ($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes') :
                            foreach ( $order->get_tax_classes() as $tax_class ) :
                                if ($order->show_tax_entry($tax_class)) : ?>
                                    <tr>
                                        <td colspan="3"><?php echo $order->get_tax_class_for_display($tax_class) . ' (' . (float) $order->get_tax_rate($tax_class) . '%):'; ?></td>
                                        <td><?php echo $order->get_tax_amount($tax_class) ?></td>
                                    </tr>
                                    <?php
                                endif;
                            endforeach;
                        endif; ?>
						<?php if ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'no' && $order->order_discount>0) : ?><tr class="discount">
							<td colspan="3"><?php _e('Discount', 'jigoshop'); ?></td>
							<td>-<?php echo jigoshop_price($order->order_discount); ?></td>
						</tr><?php endif; ?>
						<tr>
							<td colspan="3"><strong><?php _e('Grand Total', 'jigoshop'); ?></strong></td>
							<td><strong><?php echo jigoshop_price($order->order_total); ?></strong></td>
						</tr>
					</tfoot>
					<tbody>
						<?php
						foreach($order->items as $order_item) :

							if (isset($order_item['variation_id']) && $order_item['variation_id'] > 0) :
								$_product = new jigoshop_product_variation( $order_item['variation_id'] );
							else :
								$_product = new jigoshop_product( $order_item['id'] );
							endif;

							echo '<tr>';

							echo '<td>'.$_product->sku.'</td>';
							echo '<td class="product-name">'.$_product->get_title();

							if (isset($_product->variation_data)) :
								echo jigoshop_get_formatted_variation( $_product->variation_data );
							endif;

							do_action( 'jigoshop_display_item_meta_data', $order_item );

							echo '</td>';
							echo '<td>'.jigoshop_price($order_item['cost']).'</td>';
							echo '<td>'.$order_item['qty'].'</td>';

							echo '</tr>';

						endforeach;
						?>
					</tbody>
				</table>
				<?php do_action('jigoshop_after_track_order_details', $order->id);?>

				<div style="width: 49%; float:left;">
					<h2><?php _e('Billing Address', 'jigoshop'); ?></h2>
					<p><?php
					$address = $order->billing_first_name.' '.$order->billing_last_name.'<br/>';
					if ($order->billing_company) $address .= $order->billing_company.'<br/>';
					$address .= $order->formatted_billing_address;
					echo $address;
					?></p>
				</div>
				<div style="width: 49%; float:right;">
					<h2><?php _e('Shipping Address', 'jigoshop'); ?></h2>
					<p><?php
					$address = $order->shipping_first_name.' '.$order->shipping_last_name.'<br/>';
					if ($order->shipping_company) $address .= $order->shipping_company.'<br/>';
					$address .= $order->formatted_shipping_address;
					echo $address;
					?></p>
				</div>
				<div class="clear"></div>
				<?php

			else :
				echo '<p>'.__('Sorry, we could not find that order id in our database. <a href="'.get_permalink($post->ID).'">Want to retry?</a>', 'jigoshop').'</p>';
			endif;
		else :
			echo '<p>'.sprintf(__('Sorry, we could not find that order id in our database. <a href="%s">Want to retry?</a></p>', 'jigoshop'), get_permalink($post->ID));
		endif;

	else :

		?>
		<form action="<?php echo esc_url( get_permalink($post->ID) ); ?>" method="post" class="track_order">

			<p><?php _e('To track your order please enter your Order ID and email address in the boxes below and press return. This was given to you on your receipt and in the confirmation email you should have received.', 'jigoshop'); ?></p>

			<p class="form-row form-row-first"><label for="orderid"><?php _e('Order ID', 'jigoshop'); ?></label> <input class="input-text" type="text" name="orderid" id="orderid" placeholder="<?php _e('Found in your order confirmation email.', 'jigoshop'); ?>" /></p>
			<p class="form-row form-row-last"><label for="order_email"><?php _e('Billing Email', 'jigoshop'); ?></label> <input class="input-text" type="text" name="order_email" id="order_email" placeholder="<?php _e('Email you used during checkout.', 'jigoshop'); ?>" /></p>
			<div class="clear"></div>
			<p class="form-row"><input type="submit" class="button" name="track" value="<?php _e('Track"', 'jigoshop'); ?>" /></p>
			<?php jigoshop::nonce_field('order_tracking') ?>
		</form>
		<?php

	endif;

}