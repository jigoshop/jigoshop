<?php
/**
 * Product Data
 * 
 * Function for displaying the product data meta boxes
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Admin
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
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

	$thepostid = $post->ID;	
	?>
	<div class="panel-wrap product_data">

		<ul class="product_data_tabs tabs" style="display:none;">
			<li class="active">
				<a href="#general_product_data"><?php _e('General', 'jigoshop'); ?></a>
			</li>

			<li class="pricing_tab">
				<a href="#pricing_product_data"><?php _e('Pricing', 'jigoshop'); ?></a>
			</li>

			<?php if (get_option('jigoshop_manage_stock')) : ?>
			<li class="inventory_tab">
				<a href="#inventory_product_data"><?php _e('Inventory', 'jigoshop'); ?></a>
			</li>
			<?php endif; ?>

			<li>
				<a href="#jigoshop_attributes"><?php _e('Attributes', 'jigoshop'); ?></a>
			</li>
			
			<?php do_action('product_write_panel_tabs'); ?>
		</ul>
		
		<div id="general_product_data" class="panel jigoshop_options_panel"><?php
			
			// Product Type
			$terms = wp_get_object_terms( $thepostid, 'product_type' );
			$product_type = ($terms) ? current($terms)->slug : 'simple';

			$field = array( 
				'id' 	=> 'product-type',
				'label' => __('Product Type', 'jigoshop')
			);

			echo '<p class="form-field">
				<label for="'.$field['id'].'">'.$field['label'].' <em class="req" title="'.__('Required', 'jigoshop') . '">*</em></label>
				<select id="'.$field['id'].'" name="'.$field['id'].'" class="select short">';

			echo '<option value="simple" '; if ($product_type=='simple') echo 'selected="selected"'; echo '>'.__('Simple','jigoshop').'</option>';
			
			do_action('product_type_selector', $product_type);

			echo '</select></p>';

			// Grouped Products
			// TODO: Needs refactoring & a bit of love
			$posts_in = (array) get_objects_in_term( get_term_by( 'slug', 'grouped', 'product_type' )->term_id, 'product_type' );
			$posts_in = array_unique($posts_in);

			if( (bool) $posts_in ) {

				$args = array(
					'post_type'	=> 'product',
					'post_status' => 'publish',
					'numberposts' => -1,
					'orderby' => 'title',
					'order' => 'asc',
					'post_parent' => 0,
					'include' => $posts_in,
				);

				$grouped_products = get_posts($args);

				$options = array( null => 'Pick a Product Group &hellip;' );

				if( $grouped_products ) foreach( $grouped_products as $product ) {
					if ($product->ID==$post->ID) continue;

					$options[$product->ID] = $product->post_title;
				}
				// Only echo the form if we have grouped products
				echo jigoshop_form::select( 'parent_id', 'Product Group', $options );
			}
			
			// Ordering
			echo jigoshop_form::input( 'menu_order', 'Sort Order', false, $post->menu_order );

			// SKU
			// TODO: Do we need this check? Why are we disabling the SKU? -Rob
			if ( get_option('jigoshop_enable_sku') !== 'no' ) {
				echo jigoshop_form::input( 'sku', 'SKU', 'Leave blank to use product ID' );
			}

			// Weight
			// TODO: Do we need this check? -Rob
			if( get_option('jigoshop_enable_weight') !== 'no' ) {
				echo jigoshop_form::input( 'weight', 'Weight' ); // Missing placeholder attribute 0.00
			}

			// Featured
			echo jigoshop_form::select( 'featured', 'Featured?', 
				array(
					false	=> 'No',
					true	=> 'Yes'
				) );

			// Visibility
			echo jigoshop_form::select( 'visibility', 'Visibility',
				array(
					'visible'	=> 'Catalog & Search',
					'catalog'	=> 'Catalog Only',
					'search'	=> 'Search Only',
					'Hidden'	=> 'Hidden'
				) );
			?>
		</div>
		<div id="pricing_product_data" class="panel jigoshop_options_panel">
			
			<?php
			// Regular Price
			echo jigoshop_form::input( 'regular_price', 'Regular Price' );

			// Sale Price
			echo jigoshop_form::input( 'sale_price', 'Sale Price' );

			// Sale Price date range
			// TODO: Convert this to a helper somehow?
			$field = array( 'id' => 'sale_price_dates', 'label' => __('Sale Price Dates', 'jigoshop') );
			
			$sale_price_dates_from = get_post_meta($thepostid, 'sale_price_dates_from', true);
			$sale_price_dates_to = get_post_meta($thepostid, 'sale_price_dates_to', true);
			
			echo '	<p class="form-field">
						<label for="'.$field['id'].'_from">'.$field['label'].':</label>
						<input type="text" class="short date-pick" name="'.$field['id'].'_from" id="'.$field['id'].'_from" value="';
			if ($sale_price_dates_from) echo date('Y-m-d', $sale_price_dates_from);
			echo '" placeholder="' . __('From&hellip;', 'jigoshop') . '" maxlength="10" />
						<input type="text" class="short date-pick" name="'.$field['id'].'_to" id="'.$field['id'].'_to" value="';
			if ($sale_price_dates_to) echo date('Y-m-d', $sale_price_dates_to);
			echo '" placeholder="' . __('To&hellip;', 'jigoshop') . '" maxlength="10" />
						<span class="description">' . __('Date format', 'jigoshop') . ': <code>YYYY-MM-DD</code></span>
					</p>';

			// Tax Status
			echo jigoshop_form::select( 'tax_status', 'Tax Status',
				array(
					'taxable'	=> 'Taxable',
					'shipping'	=> 'Shipping',
					'none'		=> 'None'
				) );

			// Tax Classes
			$options = array( null => 'Standard' );

			// Get all tax classes
			$_tax = new jigoshop_tax();
			$tax_classes = $_tax->get_tax_classes();
			if( $tax_classes) foreach( $tax_classes as $class ) {
				$options[sanitize_title($class)] = $class;
			}

			echo jigoshop_form::select( 'tax_class', 'Tax Class', $options );
			?>

		</div>
		<?php if (get_option('jigoshop_manage_stock')=='yes') : ?>
		<div id="inventory_product_data" class="panel jigoshop_options_panel">

			<?php
			// manage stock
			echo jigoshop_form::checkbox( 'manage_stock', 'Manage Stock?' );

			// Stock Status
			// TODO: These values should be true/false
			echo jigoshop_form::select( 'stock_status', 'Stock Status', 
				array(
					'instock'		=> 'In Stock',
					'outofstock'	=> 'Out of Stock'
				) );

			echo '<div class="stock_fields">';

			// Stock
			// TODO: Missing default value of 0
			echo jigoshop_form::input( 'stock', 'Stock Quantity' );

			// Backorders
			echo jigoshop_form::select( 'backorders', 'Allow Backorders?',
				array(
					'no'		=> 'Do not allow',
					'notify'	=> 'Allow, but notify customer',
					'yes'		=> 'Allow'
				) );

			echo '</div>';
			?>

		</div>
		<?php endif; 

		// Attributs begin here
		// TODO: Much love needs to be applied here

		?>
		<div id="jigoshop_attributes" class="panel">
		
			<div class="jigoshop_attributes_wrapper">
				<table cellpadding="0" cellspacing="0" class="jigoshop_attributes">
					<thead>
						<tr>
							<th class="center" width="60"><?php _e('Order', 'jigoshop'); ?></th>
							<th width="180"><?php _e('Name', 'jigoshop'); ?></th>
							<th><?php _e('Value', 'jigoshop'); ?></th>
							<th class="center" width="1%"><?php _e('Visible?', 'jigoshop'); ?></th>
							<th class="center" width="1%"><?php _e('Variation?', 'jigoshop'); ?></th>
							<th class="center" width="1%"><?php _e('Remove', 'jigoshop'); ?></th>
						</tr>
					</thead>
					
					<tbody id="attributes_list">	
						<?php
							$attribute_taxonomies = jigoshop_product::getAttributeTaxonomies();
							$attributes = get_post_meta($post->ID, 'product_attributes', true);
							
							$i = -1;
							
							// Taxonomies
							if ( $attribute_taxonomies ) :
						    	foreach ($attribute_taxonomies as $tax) : $i++;
									
						    		$attribute_taxonomy_name = sanitize_title($tax->attribute_name);
						    		if (isset($attributes[$attribute_taxonomy_name])) $attribute = $attributes[$attribute_taxonomy_name];
									$position = (isset($attribute['position'])) ? $attribute['position'] : 0;
									
						    		$allterms = wp_get_post_terms( $thepostid, 'pa_'.$attribute_taxonomy_name );
						    		$has_terms = ( is_wp_error( $allterms ) || !$allterms || sizeof( $allterms ) == 0 ) ? 0 : 1;
						    		$term_slugs = array();
						    		if ( !is_wp_error( $allterms ) && $allterms ) :
						    			foreach ($allterms as $term) :
						    				$term_slugs[] = $term->slug;
						    			endforeach;
						    		endif;
						    		
						    		?><tr class="taxonomy <?php echo $attribute_taxonomy_name; ?>" rel="<?php echo $position; ?>" <?php if ( !$has_terms ) echo 'style="display:none"'; ?>>
						    			
										<td class="center">
											<button type="button" class="move_up button">&uarr;</button><button type="button" class="move_down button">&darr;</button>
											<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo $position; ?>" />
										</td>
										
										<td class="name">
											<?php echo $tax->attribute_name; ?> 
											<input type="hidden" name="attribute_names[<?php echo $i; ?>]" value="<?php echo $tax->attribute_name; ?>" />
											<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="1" />
											<input type="hidden" name="attribute_enabled[<?php echo $i; ?>]" value="1" />
										</td>
										
										<td class="control">
										<?php if ($tax->attribute_type=="select") : ?>
											<select name="attribute_values[<?php echo $i ?>]">
												<option value=""><?php _e('Choose an option&hellip;', 'jigoshop'); ?></option>
												<?php
												if (taxonomy_exists('pa_'.$attribute_taxonomy_name)) :
					        						$terms = get_terms( 'pa_'.$attribute_taxonomy_name, array( 'orderby' => 'slug', 'hide_empty' => '0' ) );
					        						if ($terms) :
														foreach ($terms as $term) :
															printf('<option value="%s" %s>%s</option>'
																, $term->name
																, selected(in_array($term->slug, $term_slugs), true, false)
																, $term->name);
														endforeach;
													endif;
												endif;
												?>			
											</select>
										<?php elseif ($tax->attribute_type=="multiselect") : ?>
											<div class="multiselect">
												<?php
												if (taxonomy_exists('pa_'.$attribute_taxonomy_name)) :
					        						$terms = get_terms( 'pa_'.$attribute_taxonomy_name, array( 'orderby' => 'slug', 'hide_empty' => '0' ) );
					        						if ($terms) :
						        						foreach ($terms as $term) :
															$checked = checked(in_array($term->slug, $term_slugs), true, false);
															printf('<label %s><input type="checkbox" name="attribute_values[%d][]" value="%s" %s/> %s</label>'
																, !empty($checked) ? 'class="selected"' : ''
																, $i
																, $term->slug
																, $checked
																, $term->name);
														endforeach;
													endif;
												endif;
												?>
											</div>
											<div class="multiselect-controls">
												<a class="check-all" href="#"><?php _e('Check All'); ?></a>&nbsp;|
												<a class="uncheck-all" href="#"><?php _e('Uncheck All');?></a>&nbsp;|
												<a class="toggle" href="#"><?php _e('Toggle');?></a>
											</div>
										<?php elseif ($tax->attribute_type=="text") : ?>
											<input type="text" name="attribute_values[<?php echo $i; ?>]" value="<?php 												
												if ($allterms) :
													$prettynames = array();
													foreach ($allterms as $term) :
														$prettynames[] = $term->name;
													endforeach;
													echo implode(',', $prettynames);
												endif;
											?>" placeholder="<?php _e('Comma separate terms', 'jigoshop'); ?>" />										
										<?php endif; ?>
										</td>
										
										<td class="center visibility"><input type="checkbox" <?php checked(boolval( isset($attribute) ? $attribute['visible'] : 0 ), true); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /></td>

										<?php if ($tax->attribute_type=="select") : // always disable variation for select elements ?>
											<td class="center variation"><input type="checkbox" disabled="disabled" /></td>
										<?php else: ?>
											<td class="center variation"><input type="checkbox" <?php checked(boolval( isset($attribute) ? $attribute['variation'] : 0 ), true); ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /></td>
										<?php endif; ?>

										<td class="center hiderow"><button type="button" class="hide_row button">&times;</button></td>
									</tr><?php
						    	endforeach;
						    endif;
							
							// Attributes
							if ($attributes && sizeof($attributes)>0) foreach ($attributes as $attribute) : 
								if (boolval($attribute['is_taxonomy'])) continue;
								
								$i++; 
								$position = (isset($attribute['position'])) ? $attribute['position'] : 0;

								?><tr rel="<?php echo $position; ?>">
									<td class="center">
										<button type="button" class="move_up button">&uarr;</button><button type="button" class="move_down button">&darr;</button>
										<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo $position; ?>" />
									</td>
									<td>
										<input type="text" name="attribute_names[<?php echo $i; ?>]" value="<?php echo $attribute['name']; ?>" />
										<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="0" />
									</td>
									<td><input type="text" name="attribute_values[<?php echo $i; ?>]" value="<?php echo $attribute['value']; ?>" /></td>
									<td class="center"><input type="checkbox" <?php checked(boolval($attribute['visible']), true); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /></td>
									<td class="center"><input type="checkbox" <?php checked(boolval($attribute['variation']), true); ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /></td>
									<td class="center"><button type="button" class="remove_row button">&times;</button></td>
								</tr><?php
							endforeach;
						?>			
					</tbody>
				</table>
			</div>
			<button type="button" class="button button-primary add_attribute"><?php _e('Add', 'jigoshop'); ?></button>
			<select name="attribute_taxonomy" class="attribute_taxonomy">
				<option value="" data-type="custom"><?php _e('Custom product attribute', 'jigoshop'); ?></option>
				<?php
					if ( $attribute_taxonomies ) :
				    	foreach ($attribute_taxonomies as $tax) :
				    		echo '<option value="'.sanitize_title($tax->attribute_name).'" data-type="'.$tax->attribute_type.'">'.$tax->attribute_name.'</option>';
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