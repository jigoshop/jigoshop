<?php
/**
 * Review order form template
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

$options = Jigoshop_Base::get_options(); ?>
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
			<?php $price_label = jigoshop_cart::show_retail_price() ? __('Retail Price', 'jigoshop') : __('Subtotal', 'jigoshop'); ?>
			<td colspan="2"><?php echo $price_label; ?></td>
			<td class="cart-row-subtotal"><?php echo jigoshop_cart::get_cart_subtotal(true, false, true); ?></td>
		</tr>

		<?php jigoshop_checkout::render_shipping_dropdown(); ?>

		<?php if (jigoshop_cart::show_retail_price() && Jigoshop_Base::get_options()->get('jigoshop_prices_include_tax') == 'no') : ?>
			<tr>
				<td colspan="2"><?php _e('Subtotal', 'jigoshop'); ?></td>
				<td><?php echo jigoshop_cart::get_cart_subtotal(true, true); ?></td>
			</tr>
		<?php elseif (jigoshop_cart::show_retail_price()): ?>
			<tr>
				<td colspan="2"><?php _e('Subtotal', 'jigoshop'); ?></td>
				<?php
				$price = jigoshop_cart::$cart_contents_total_ex_tax + jigoshop_cart::$shipping_total;
				$price = jigoshop_price($price, array('ex_tax_label' => 1));
				?>
				<td><?php echo $price; ?></td>
			</tr>
		<?php endif; ?>

		<?php if (jigoshop_cart::tax_after_coupon()): ?>
			<tr class="discount">
				<td colspan="2"><?php _e('Discount', 'jigoshop'); ?></td>
				<td>-<?php echo jigoshop_cart::get_total_discount(); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($options->get('jigoshop_calc_taxes') == 'yes'):
			foreach (jigoshop_cart::get_applied_tax_classes() as $tax_class):
				if (jigoshop_cart::get_tax_for_display($tax_class)) : ?>
					<tr>
						<td colspan="2"><?php echo jigoshop_cart::get_tax_for_display($tax_class); ?></td>
						<td><?php echo jigoshop_cart::get_tax_amount($tax_class) ?></td>
					</tr>
				<?php endif;
			endforeach;
		endif; ?>

		<?php do_action('jigoshop_after_review_order_items'); ?>

		<?php if (!jigoshop_cart::tax_after_coupon() && jigoshop_cart::get_total_discount()) : ?>
			<tr class="discount">
				<td colspan="2"><?php _e('Discount', 'jigoshop'); ?></td>
				<td>-<?php echo jigoshop_cart::get_total_discount(); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td colspan="2"><strong><?php _e('Grand Total', 'jigoshop'); ?></strong></td>
			<td><strong><?php echo jigoshop_cart::get_total(); ?></strong></td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		foreach (jigoshop_cart::$cart_contents as $item_id => $values) :
			/** @var jigoshop_product $product */
			$product = $values['data'];
			if ($product->exists() && $values['quantity'] > 0) :
				$variation = jigoshop_cart::get_item_data($values);
				$customization = '';
				$custom_products = (array)jigoshop_session::instance()->customized_products;
				$product_id = !empty($values['variation_id']) ? $values['variation_id'] : $values['product_id'];
				$custom = isset($custom_products[$product_id]) ? $custom_products[$product_id] : ''; ?>
				<tr>
					<td class="product-name">
						<?php echo $product->get_title().$variation;
						if (!empty($custom)) :
							$label = apply_filters('jigoshop_customized_product_label', __(' Personal: ', 'jigoshop')); ?>
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
						echo jigoshop_price($product->get_defined_price() * $values['quantity'], array('ex_tax_label' => Jigoshop_Base::get_options()->get('jigoshop_show_prices_with_tax') == 'yes' ?  2 : 1));
						?>
					</td>
				</tr>
			<?php endif;
		endforeach;
		?>
		</tbody>
	</table>

	<?php $coupons = jigoshop_cart::get_coupons();?>
	<table>
		<tr>
			<td colspan="6" class="actions">
				<?php if (JS_Coupons::has_coupons()): ?>
					<div class="coupon">
						<label for="coupon_code"><?php _e('Coupon', 'jigoshop'); ?>:</label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" />
						<input type="submit" class="button" name="apply_coupon" value="<?php _e('Apply Coupon', 'jigoshop'); ?>" />
					</div>
				<?php endif; ?>
			</td>
			<?php if (count($coupons)): ?>
				<td class="applied-coupons">
					<div>
						<span class="applied-coupons-label"><?php _e('Applied Coupons: ', 'jigoshop'); ?></span>
						<?php foreach ($coupons as $code): ?>
							<a href="?unset_coupon=<?php echo $code; ?>" id="<?php echo $code; ?>" class="applied-coupons-values"><?php echo $code; ?>
								<span class="close">&times;</span>
							</a>
						<?php endforeach; ?>
					</div>
				</td>
			<?php endif; ?>
		</tr>
	</table>
</div>
