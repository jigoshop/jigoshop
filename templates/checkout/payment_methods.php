<div id="payment">

	<ul class="payment_methods methods">
		<?php
		$available_gateways = jigoshop_payment_gateways::get_available_payment_gateways();
		if ($available_gateways) :
			$default_gateway = Jigoshop_Base::get_options()->get_option('jigoshop_default_gateway');
			if (!empty($default_gateway)) {
				if (array_key_exists($default_gateway, $available_gateways)) {
					$temp = $available_gateways[$default_gateway];
					unset($available_gateways[$default_gateway]);
					array_unshift($available_gateways, $temp);
				}
			}
			$gateway_set = false;
			foreach ($available_gateways as $gateway) :
				/** @var jigoshop_payment_gateway $gateway */
				if (jigoshop_checkout::process_gateway($gateway)) :
					if (!$gateway_set) :

						// Chosen Method
						if (sizeof($available_gateways)) :
							if (isset($_POST['payment_method']) && isset($available_gateways[$_POST['payment_method']])) :
								$available_gateways[$_POST['payment_method']]->set_current();
							else :
								$gateway->set_current();
							endif;
						endif;
						$gateway_set = true;

					endif; ?>
					<li>
						<input type="radio" id="payment_method_<?php echo $gateway->id; ?>" class="input-radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php $gateway->chosen and print ' checked="checked"'; ?> />
						<label for="payment_method_<?php echo $gateway->id; ?>"><?php echo $gateway->title; ?> <?php echo apply_filters('gateway_icon', $gateway->icon(), $gateway->id); ?></label>
						<?php if ($gateway->has_fields || $gateway->description) : ?>
							<div class="payment_box payment_method_<?php echo esc_attr($gateway->id); ?>" style="display:none;"><?php $gateway->payment_fields(); ?></div>
						<?php endif; ?>
					</li>
				<?php
				endif;
			endforeach;
		else :
			if (!jigoshop_customer::get_country()) :
				echo '<p>'.__('Please fill in your details above to see available payment methods.', 'jigoshop').'</p>';
			else :
				echo '<p>'.__('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'jigoshop').'</p>';
			endif;
		endif;
		?>
	</ul>
	<div class="form-row">
		<noscript>
			<?php _e('Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'jigoshop'); ?>
			<br /><input type="submit" class="button-alt" name="update_totals" value="<?php _e('Update totals', 'jigoshop'); ?>" />
		</noscript>

		<?php jigoshop::nonce_field('process_checkout') ?>

		<?php do_action('jigoshop_review_order_before_submit'); ?>

		<?php if (jigoshop_get_page_id('terms') > 0) : ?>
			<p class="form-row terms">
				<label for="terms" class="checkbox"><?php _e('I accept the', 'jigoshop'); ?>
					<a href="<?php echo esc_url(get_permalink(jigoshop_get_page_id('terms'))); ?>" target="_blank"><?php _e('terms &amp; conditions', 'jigoshop'); ?></a>
				</label>
				<input type="checkbox" class="input-checkbox" name="terms" id="terms"<?php isset($_POST['terms']) and print ' checked="checked"'; ?> />
			</p>
		<?php endif; ?>

		<?php $order_button_text = apply_filters('jigoshop_order_button_text', __('Place Order', 'jigoshop')); ?>
		<input type="submit" class="button-alt" name="place_order" id="place_order" value="<?php echo esc_attr($order_button_text); ?>" />

		<?php do_action('jigoshop_review_order_after_submit'); ?>
	</div>
</div>