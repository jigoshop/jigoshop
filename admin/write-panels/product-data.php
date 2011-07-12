<?php
/**
 * Product Data
 * 
 * Function for displaying the product data meta boxes
 *
 * @author 		Jigowatt
 * @category 	Admin Write Panels
 * @package 	JigoShop
 */
 
/**
 * Product data box
 * 
 * Displays the product data box, tabbed, with several panels covering price, stock etc
 *
 * @since 		1.0
 */
function jigoshop_product_data_box() {
	global $post, $wpdb, $thepostid;
	add_action('admin_footer', 'jigoshop_meta_scripts');
	
	wp_nonce_field( 'jigoshop_save_data', 'jigoshop_meta_nonce' );
	
	$data = (array) maybe_unserialize( get_post_meta($post->ID, 'product_data', true) );
	$featured = (string) get_post_meta( $post->ID, 'featured', true );
	$visibility = (string) get_post_meta( $post->ID, 'visibility', true);
	
	if (!isset($data['weight'])) $data['weight'] = '';
	if (!isset($data['regular_price'])) $data['regular_price'] = '';
	if (!isset($data['sale_price'])) $data['sale_price'] = '';
	
	$thepostid = $post->ID;
	
	?>
	<div class="panel-wrap product_data">
	
		<ul class="product_data_tabs tabs" style="display:none;">
			<li class="active"><a href="#general_product_data"><?php _e('General', 'jigoshop'); ?></a></li>
			<li class="pricing_tab"><a href="#pricing_product_data"><?php _e('Pricing', 'jigoshop'); ?></a></li>
			<?php if (get_option('jigoshop_manage_stock')=='yes') : ?><li class="inventory_tab"><a href="#inventory_product_data"><?php _e('Inventory', 'jigoshop'); ?></a></li><?php endif; ?>
			<li><a href="#jigoshop_attributes"><?php _e('Attributes', 'jigoshop'); ?></a></li>
			
			<?php do_action('product_write_panel_tabs'); ?>

		</ul>
		<div id="general_product_data" class="panel jigoshop_options_panel"><?php
			
			// Product Type
			if ($terms = wp_get_object_terms( $thepostid, 'product_type' )) $product_type = current($terms)->slug; else $product_type = 'simple';
			$field = array( 'id' => 'product-type', 'label' => 'Product Type' );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].' <em class="req" title="Required">*</em></label><select id="'.$field['id'].'" name="'.$field['id'].'">';

			echo '<option value="simple" '; if ($product_type=='simple') echo 'selected="selected"'; echo '>'.__('Simple','jigoshop').'</option>';
			
			do_action('product_type_selector', $product_type);

			echo '</select></p>';
			
			// Summary
			echo '<p class="form-field"><label for="excerpt">Summary:</label>
			<textarea name="excerpt" id="excerpt" placeholder="Add a summary for your product &ndash; this is a quick description to encourage users to view the product.">'.esc_html( $post->post_excerpt ).'</textarea></p>';
			
			// SKU
			$field = array( 'id' => 'sku', 'label' => 'SKU:' );
			$SKU = get_post_meta($thepostid, 'SKU', true);
			echo '<p class="form-field">
				<label for="'.$field['id'].'">'.$field['label'].'</label>
				<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$SKU.'" /> <span class="description">Leave blank to use product ID</span></p>';
				 
			// Weight
			$field = array( 'id' => 'weight', 'label' => 'Weight ('.get_option('jigoshop_weight_unit').'):' );
			echo '<p class="form-field">
				<label for="'.$field['id'].'">'.$field['label'].'</label>
				<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$data[$field['id']].'" placeholder="0.00" /></p>';
			
			// Featured
			$field = array( 'id' => 'featured', 'label' => 'Featured?' );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].'</label><select name="'.$field['id'].'">';
			echo '<option value="no" '; if (isset($featured) && $featured=='no') echo 'selected="selected"'; echo '>No</option>';
			echo '<option value="yes" '; if (isset($featured) && $featured=='yes') echo 'selected="selected"'; echo '>Yes</option>';
			echo '</select></p>';
			
			// Visibility
			$field = array( 'id' => 'visibility', 'label' => 'Visibility:' );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].'</label><select name="'.$field['id'].'">';
			echo '<option value="visible" '; if (isset($visibility) && $visibility=='visible') echo 'selected="selected"'; echo '>Catalog &amp; Search</option>';
			echo '<option value="catalog" '; if (isset($visibility) && $visibility=='catalog') echo 'selected="selected"'; echo '>Catalog</option>';
			echo '<option value="search" '; if (isset($visibility) && $visibility=='search') echo 'selected="selected"'; echo '>Search</option>';
			echo '<option value="hidden" '; if (isset($visibility) && $visibility=='hidden') echo 'selected="selected"'; echo '>Hidden</option>';
			echo '</select></p>';
			?>
		</div>
		<div id="pricing_product_data" class="panel jigoshop_options_panel">
			
			<?php 
			// Price
			$field = array( 'id' => 'regular_price', 'label' => 'Regular Price ('.get_jigoshop_currency_symbol().'):' );
			echo '	<p class="form-field">
						<label for="'.$field['id'].'">'.$field['label'].'</label>
						<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$data[$field['id']].'" placeholder="0.00" /></p>';
			
			// Special Price
			$field = array( 'id' => 'sale_price', 'label' => 'Sale Price ('.get_jigoshop_currency_symbol().'):' );
			echo '	<p class="form-field">
						<label for="'.$field['id'].'">'.$field['label'].'</label>
						<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$data[$field['id']].'" placeholder="0.00" /></p>';
					
			// Special Price date range
			$field = array( 'id' => 'sale_price_dates', 'label' => 'Sale Price Dates:' );
			
			$sale_price_dates_from = get_post_meta($thepostid, 'sale_price_dates_from', true);
			$sale_price_dates_to = get_post_meta($thepostid, 'sale_price_dates_to', true);
			
			echo '	<p class="form-field">
						<label for="'.$field['id'].'_from">'.$field['label'].'</label>
						<input type="text" class="short date-pick" name="'.$field['id'].'_from" id="'.$field['id'].'_from" value="';
			if ($sale_price_dates_from) echo date('Y-m-d', $sale_price_dates_from);
			echo '" placeholder="From&hellip;" maxlength="10" />
						<input type="text" class="short date-pick" name="'.$field['id'].'_to" id="'.$field['id'].'_to" value="';
			if ($sale_price_dates_to) echo date('Y-m-d', $sale_price_dates_to);
			echo '" placeholder="To&hellip;" maxlength="10" />
						<span class="description">Date format: <code>YYYY-MM-DD</code></span>
					</p>';
					
			// Tax
			$_tax = new jigoshop_tax();
			
			$field = array( 'id' => 'tax_status', 'label' => 'Tax Status:' );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].'</label><select name="'.$field['id'].'">';
			echo '<option value="taxable" '; if (isset($data[$field['id']]) && $data[$field['id']]=='taxable') echo 'selected="selected"'; echo '>Taxable</option>';
			echo '<option value="shipping" '; if (isset($data[$field['id']]) && $data[$field['id']]=='shipping') echo 'selected="selected"'; echo '>Shipping only</option>';
			echo '<option value="none" '; if (isset($data[$field['id']]) && $data[$field['id']]=='none') echo 'selected="selected"'; echo '>None</option>';
			echo '</select></p>';
			
			$field = array( 'id' => 'tax_class', 'label' => 'Tax Class:' );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].'</label><select name="'.$field['id'].'">';
			echo '<option value="" '; if (isset($data[$field['id']]) && $data[$field['id']]=='') echo 'selected="selected"'; echo '>'.__('Standard', 'jigoshop').'</option>';
			$tax_classes = $_tax->get_tax_classes();
    		if ($tax_classes) foreach ($tax_classes as $class) :
    			echo '<option value="'.sanitize_title($class).'" '; if (isset($data[$field['id']]) && $data[$field['id']]==sanitize_title($class)) echo 'selected="selected"'; echo '>'.$class.'</option>';
    		endforeach;
			echo '</select></p>';
			?>
			
		</div>
		<?php if (get_option('jigoshop_manage_stock')=='yes') : ?>
		<div id="inventory_product_data" class="panel jigoshop_options_panel">
			
			<?php
			// manage stock
			$field = array( 'id' => 'manage_stock', 'label' => 'Manage stock?' );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].' <em class="req" title="Required">*</em></label><input type="checkbox" class="checkbox" name="'.$field['id'].'" id="'.$field['id'].'"';
			if (isset($data[$field['id']]) && $data[$field['id']]=='yes') echo 'checked="checked"';
			echo ' /></p>';
			
			// Stock status
			$field = array( 'id' => 'stock_status', 'label' => 'Stock status:' );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].' <em class="req" title="Required">*</em></label><select name="'.$field['id'].'">';
			echo '<option value="instock" '; if (isset($data[$field['id']]) && $data[$field['id']]=='instock') echo 'selected="selected"'; echo '>In stock</option>';
			echo '<option value="outofstock" '; if (isset($data[$field['id']]) && $data[$field['id']]=='outofstock') echo 'selected="selected"'; echo '>Out of stock</option>';
			echo '</select></p>';
			
			echo '<div class="stock_fields">';
			
			// Stock
			$field = array( 'id' => 'stock', 'label' => 'Stock Qty:' );
			echo '	<p class="form-field">
						<label for="'.$field['id'].'">'.$field['label'].' <em class="req" title="Required">*</em></label>
						<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="';
			
			$stock = get_post_meta($post->ID, 'stock', true);
    		if (!$stock) $stock = 0;
    		echo $stock;
						
			echo '" />
					</p>';

			// Backorders?
			$field = array( 'id' => 'backorders', 'label' => 'Allow Backorders?' );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].' <em class="req" title="Required">*</em></label><select name="'.$field['id'].'">';
			echo '<option value="no" '; if (isset($data[$field['id']]) && $data[$field['id']]=='no') echo 'selected="selected"'; echo '>Do not allow</option>';
			echo '<option value="notify" '; if (isset($data[$field['id']]) && $data[$field['id']]=='notify') echo 'selected="selected"'; echo '>Allow, but notify customer</option>';
			echo '<option value="yes" '; if (isset($data[$field['id']]) && $data[$field['id']]=='yes') echo 'selected="selected"'; echo '>Allow</option>';
			echo '</select></p>';
			
			echo '</div>';
			?>			
			
		</div>
		<?php endif; ?>
		<div id="jigoshop_attributes" class="panel">
		
			<div class="jigoshop_attributes_wrapper">
				<table cellpadding="0" cellspacing="0" class="jigoshop_attributes">
					<thead>
						<tr>
							<th class="center" width="60"><?php _e('Order', 'jigoshop'); ?></th>
							<th width="180"><?php _e('Name', 'jigoshop'); ?></th>
							<th><?php _e('Value', 'jigoshop'); ?></th>
							<th class="center" width="1%"><?php _e('Visible?', 'jigoshop'); ?></th>
							<th class="center" width="1%"><?php _e('Remove', 'jigoshop'); ?></th>
						</tr>
					</thead>
					<tbody id="attributes_list">	
						<?php
							$attribute_taxonomies = jigoshop::$attribute_taxonomies;
							$attributes = maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) );

							$i = -1;
							
							// Taxonomies
							if ( $attribute_taxonomies ) :
						    	foreach ($attribute_taxonomies as $tax) : $i++;
						    							    	
						    		$attribute_nicename = strtolower(sanitize_title($tax->attribute_name));
						    		if (isset($attributes[$attribute_nicename])) $attribute = $attributes[$attribute_nicename];
						    		if (isset($attribute['visible']) && $attribute['visible']=='yes') $checked = 'checked="checked"'; else $checked = '';
						    		
						    		$values = wp_get_post_terms( $thepostid, 'product_attribute_'.strtolower(sanitize_title($tax->attribute_name)) );
						    		$value = array();
						    		if (!is_wp_error($values) && $values) :
						    			foreach ($values as $v) :
						    				$value[] = $v->slug;
						    			endforeach;
						    		endif;
						    		
						    		?><tr class="taxonomy <?php echo strtolower(sanitize_title($tax->attribute_name)); ?>" rel="<?php if (isset($attribute['position'])) echo $attribute['position']; else echo '0'; ?>" <?php if (!$value || sizeof($value)==0) echo 'style="display:none"'; ?>>
										<td class="center">
											<button type="button" class="move_up button">&uarr;</button><button type="button" class="move_down button">&darr;</button>
											<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php if (isset($attribute['position'])) echo $attribute['position']; else echo '0'; ?>" />
										</td>
										<td class="name">
											<?php echo $tax->attribute_name; ?> 
											<input type="hidden" name="attribute_names[<?php echo $i; ?>]" value="<?php echo $tax->attribute_name; ?>" />
											<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="1" />
										</td>
										<td>
										<?php if ($tax->attribute_type=="select" || $tax->attribute_type=="multiselect") : ?>
											<select <?php if ($tax->attribute_type=="multiselect") echo 'multiple="multiple" class="multiselect" name="attribute_values['.$i.'][]"'; else echo 'name="attribute_values['.$i.']"'; ?>>
												<?php if ($tax->attribute_type=="select") : ?><option value=""><?php _e('Choose an option&hellip;', 'jigoshop'); ?></option><?php endif; ?>
												<?php
												if (taxonomy_exists('product_attribute_'.strtolower(sanitize_title($tax->attribute_name)))) :
					        						$terms = get_terms( 'product_attribute_'.strtolower(sanitize_title($tax->attribute_name)), 'orderby=name&hide_empty=0' );
					        						if ($terms) :
						        						foreach ($terms as $term) :
						        							echo '<option value="'.$term->slug.'" ';
						        							if (in_array($term->slug, $value)) echo 'selected="selected"';
						        							echo '>'.$term->name.'</option>';
														endforeach;
													endif;
												endif;
												?>			
											</select>
										<?php elseif ($tax->attribute_type=="text") : ?>
											<input type="text" name="attribute_values[<?php echo $i; ?>]" value="<?php if (isset($attribute['value'])) echo $attribute['value']; ?>" placeholder="<?php _e('Comma separate terms', 'jigoshop'); ?>" />
										<?php endif; ?>
										</td>
										<td class="center"><input type="checkbox" <?php echo $checked; ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /></td>
										<td class="center"><button type="button" class="hide_row button">&times;</button></td>
									</tr><?php
						    	endforeach;
						    endif;
							
							// Attributes
							if ($attributes && sizeof($attributes)>0) foreach ($attributes as $attribute) : 
								if (isset($attribute['is_taxonomy']) && $attribute['is_taxonomy']=='yes') continue;
								
								$i++; 
								
								if ($attribute['visible']=='yes') $checked = 'checked="checked"'; else $checked = '';
								?><tr rel="<?php if (isset($attribute['position'])) echo $attribute['position']; else echo '0'; ?>">
									<td class="center">
										<button type="button" class="move_up button">&uarr;</button><button type="button" class="move_down button">&darr;</button>
										<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php if (isset($attribute['position'])) echo $attribute['position']; else echo '0'; ?>" />
									</td>
									<td>
										<input type="text" name="attribute_names[<?php echo $i; ?>]" value="<?php echo $attribute['name']; ?>" />
										<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="0" />
									</td>
									<td><input type="text" name="attribute_values[<?php echo $i; ?>]" value="<?php echo $attribute['value']; ?>" /></td>
									<td class="center"><input type="checkbox" <?php echo $checked; ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /></td>
									<td class="center"><button type="button" class="remove_row button">&times;</button></td>
								</tr><?php
							endforeach;
						?>			
					</tbody>
				</table>
			</div>
			<button type="button" class="button button-primary add_attribute"><?php _e('Add', 'jigoshop'); ?></button>
			<select name="attribute_taxonomy" class="attribute_taxonomy">
				<option value=""><?php _e('Custom product attribute', 'jigoshop'); ?></option>
				<?php
					if ( $attribute_taxonomies ) :
				    	foreach ($attribute_taxonomies as $tax) :
				    		echo '<option value="'.strtolower(sanitize_title($tax->attribute_name)).'">'.$tax->attribute_name.'</option>';
				    	endforeach;
				    endif;
				?>
			</select>
			<div class="clear"></div>
		</div>	
		
		<?php do_action('product_write_panels'); ?>
		
	</div>
	<?php
}