<?php
/**
 * @var $available_methods array List of available shipping methods.
 */
?>

<?php $available_methods = jigoshop_shipping::get_available_shipping_methods(); ?>
<tr>
	<td colspan="2"><?php _e('Shipping', 'jigoshop'); ?><br />
		<small><?php echo _x('To: ', 'shipping destination', 'jigoshop').__(jigoshop_customer::get_shipping_country_or_state(), 'jigoshop'); ?></small>
	</td>
	<td>
		<?php	if (count($available_methods) > 0):	?>
		<select name="shipping_method" id="shipping_method">
			<?php foreach ($available_methods as $method): /** @var jigoshop_shipping_method $method */ ?>
				<?php for ($i = 0; $i < $method->get_rates_amount(); $i++):
					$service = $method->get_selected_service($i);
					$price = $method->get_selected_price($i);
					$is_taxed = jigoshop_cart::$shipping_tax_total > 0;
					?>
					<option value="<?php echo esc_attr($method->id.':'.$service.':'.$i); ?>" <?php selected($method->is_rate_selected($i)); ?>>
						<?php echo $service; ?> &ndash; <?php echo $price > 0 ? jigoshop_price($price, array('ex_tax_label' => (int)$is_taxed)) : __('Free', 'jigoshop'); ?>
					</option>
				<?php endfor; ?>
			<?php endforeach; ?>
		</select>
		<?php else: ?>
			<p><?php echo __(jigoshop_shipping::get_shipping_error_message(), 'jigoshop'); ?></p>
		<?php endif; ?>
	</td>
</tr>
