<?php if (jigoshop_shipping::$enabled && get_option('jigoshop_enable_shipping_calc')=='yes' && jigoshop_cart::needs_shipping()) : 
	?>
	<form class="shipping_calculator" action="<?php echo jigoshop_cart::get_cart_url(); ?>" method="post">
		<h2><a href="#" class="shipping-calculator-button"><?php _e('Calculate Shipping', 'jigoshop'); ?> <span>&darr;</span></a></h2>
		<section class="shipping-calculator-form">
		<p class="form-row">
			<select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state" rel="calc_shipping_state">
				<option value=""><?php _e('Select a country&hellip;', 'jigoshop'); ?></option>
				<?php				
					foreach(jigoshop_countries::get_allowed_countries() as $key=>$value) :
						echo '<option value="'.$key.'"';
						if (jigoshop_customer::get_country()==$key) echo 'selected="selected"';
						echo '>'.$value.'</option>';
					endforeach;
				?>
			</select>
		</p>
		<div class="col2-set">
			<p class="form-row col-1">
				<?php 
					$current_cc = jigoshop_customer::get_country();
					$current_r = jigoshop_customer::get_state();
					$states = jigoshop_countries::$states;
					
					if (isset( $states[$current_cc][$current_r] )) :
						// Dropdown
						?>
						<span>
							<select name="calc_shipping_state" id="calc_shipping_state"><option value=""><?php _e('Select a state&hellip;', 'jigoshop'); ?></option><?php
								foreach($states[$current_cc] as $key=>$value) :
									echo '<option value="'.$key.'"';
									if ($current_r==$key) echo 'selected="selected"';
									echo '>'.$value.'</option>';
								endforeach;
							?></select>
						</span>
						<?php
					else :
						// Input
						?>
						<span class="input-text">
							<input type="text" value="<?php echo $current_r; ?>" placeholder="<?php _e('state', 'jigoshop'); ?>" name="calc_shipping_state" id="calc_shipping_state" />
						</span>
						<?php
					endif;
				?>
			</p>
			<p class="form-row col-2">
				<span class="input-text"><input type="text" value="<?php echo jigoshop_customer::get_postcode(); ?>" placeholder="<?php _e('Postcode/Zip', 'jigoshop'); ?>" title="<?php _e('Postcode', 'jigoshop'); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" /></span>
			</p>
		</div>
		<p><button type="submit" name="calc_shipping" value="1" class="button"><?php _e('Update Totals', 'jigoshop'); ?></button></p>
		<?php jigoshop::nonce_field('cart', 'cart') ?>
		</section>
	</form>
	<?php
endif;