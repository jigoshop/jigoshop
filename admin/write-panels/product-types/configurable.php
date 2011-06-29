<?php

/**
 * Product Options
 * 
 * Add an options panel for this product type
 **/
function configurable_product_type_options() {
	global $post;
	?>
	<div id="configurable_product_options" class="panel">
		
		<p class="description" style="padding:0;"><?php _e('Add pricing/inventory for product variations. All fields are optional; leave blank to use attributes from the main product data. <strong>Note:</strong> Please save your product attributes in the "Product Data" panel first.', 'jigoshop'); ?></p>
		
		<div class="jigoshop_configurations">
			<div class="jigoshop_configuration">
				<p>
					<button type="button" class="remove_config button"><?php _e('Remove', 'jigoshop'); ?></button>
					<strong><?php _e('Configuration:', 'jigoshop'); ?></strong>
					<?php
						if (isset($attributes) && sizeof($attributes)>0) foreach ($attributes as $attribute) :
							$options = explode("\n", $attribute[1]);
							if (sizeof($options)>0) :
								echo '<select name="'.sanitize_title($attribute[0]).'"><option value="">'.$attribute[0].'&hellip;</option><option>'.implode('</option><option>', $options).'</option></select>';
							endif;
						endforeach;
					?>
				</p>
				<table cellpadding="0" cellspacing="0" class="jigoshop_configurable_attributes">
					<tbody>	
						<tr>
							<td><label><?php _e('SKU:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_sku[]" /></td>
							<td><label><?php _e('Weight', 'jigoshop').' ('.get_option('jigoshop_weight_unit').'):'; ?></label><input type="text" size="5" name="configurable_weight[]" /></td>
							<td><label><?php _e('Price:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_price[]" /></td>
							<td><label><?php _e('Sale price:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_saleprice[]" /></td>
							<td><label><?php _e('Stock Qty:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_stock[]" /></td>
						</tr>		
					</tbody>
				</table>
			</div>
		</div>
		
		<button type="button" class="button button-primary add_configuration"><?php _e('Add Configuration', 'jigoshop'); ?></button>
		
		<div class="clear"></div>
	</div>
	<?php
}
add_action('jigoshop_product_type_options_box', 'configurable_product_type_options');

/**
 * Product Type selector
 * 
 * Adds type to the selector on the edit product page
 **/
function configurable_product_type_selector( $product_type ) {
	
	echo '<option value="configurable" '; if ($product_type=='configurable') echo 'selected="selected"'; echo '>'.__('Configurable','jigoshop').'</option>';

}
add_action('product_type_selector', 'configurable_product_type_selector');

/**
 * Product Type JavaScript
 * 
 * Adds JavaScript for the panel
 **/
function configurable_product_write_panel_js( $product_type ) {
	
	?>
	jQuery(function(){
		
		// CONFIGURABLE PRODUCT PANEL
		jQuery('button.add_configuration').live('click', function(){
		
			jQuery('.jigoshop_configurations').append('<div class="jigoshop_configuration">\
				<p>\
					<button type="button" class="remove_config button"><?php _e('Remove', 'jigoshop'); ?></button>\
					<strong><?php _e('Configuration:', 'jigoshop'); ?></strong><?php
						if (isset($attributes) && sizeof($attributes)>0) foreach ($attributes as $attribute) :
							$options = explode("\n", $attribute[1]);
							if (sizeof($options)>0) :
								echo '<select name="'.sanitize_title($attribute[0]).'"><option value="">'.$attribute[0].'&hellip;</option><option>'.implode('</option><option>', $options).'</option></select>\\';
							endif;
						endforeach;
				?></p>\
				<table cellpadding="0" cellspacing="0" class="jigoshop_configurable_attributes">\
					<tbody>	\
						<tr>\
							<td><label><?php _e('SKU:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_sku[]" /></td>\
							<td><label><?php _e('Weight', 'jigoshop').' ('.get_option('jigoshop_weight_unit').'):'; ?></label><input type="text" size="5" name="configurable_weight[]" /></td>\
							<td><label><?php _e('Price:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_price[]" /></td>\
							<td><label><?php _e('Sale price:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_saleprice[]" /></td>\
							<td><label><?php _e('Stock Qty:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_stock[]" /></td>\
						</tr>\
					</tbody>\
				</table>\
			</div>');
			
			return false;
		
		});
		
		jQuery('button.remove_config').live('click', function(){
			jQuery(this).parent().parent().remove();
		});
		
	});
	<?php
	
}
add_action('product_write_panel_js', 'configurable_product_write_panel_js');





/*
// Variations
	$c_attributes = array();
	
	if (isset($_POST['configurable_attribute_names']) && isset($_POST['configurable_attribute_values'])) :
		 $attribute_names = $_POST['configurable_attribute_names'];
		 $attribute_values = $_POST['configurable_attribute_values'];
		 
		  for ($i=0; $i<sizeof($attribute_names); $i++) :
		 	if (!($attribute_names[$i] && $attribute_values[$i])) continue;
		 	$c_attributes[] = array(htmlspecialchars(stripslashes($attribute_names[$i])), htmlspecialchars(stripslashes($attribute_values[$i])));
		 endfor; 
	endif;	
	
		update_post_meta( $post_id, 'configurable_attributes', $c_attributes );
		*/