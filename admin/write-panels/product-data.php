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
 * Change label for insert buttons
 */
add_filter( 'gettext', 'jigoshop_change_insert_into_post', null, 2 );

function jigoshop_change_insert_into_post( $translation, $original ) {

	// Check if the translation is correct
    if( ! isset( $_REQUEST['from'] ) || $original != 'Insert into Post' ) 
    	return $translation;

    // Modify text based on context
    switch ($_REQUEST['from']) {
    	case 'jigoshop_variation':
    		return __('Attach to Variation', 'jigoshop' );
    	break;
    	case 'jigoshop_product':
    		return __('Attach to Product', 'jigoshop' );
    	break;
    	default:
    		return $translation;
    }
}
 
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

	<div class="panels">
		<ul class="product_data_tabs tabs" style="display:none;">
			<li class="active">
				<a href="#general"><?php _e('General', 'jigoshop'); ?></a>
			</li>

			<li>
				<a href="#tax"><?php _e('Display & Tax', 'jigoshop') ?></a>
			</li>

			<?php if (get_option('jigoshop_manage_stock')) : ?>
			<li>
				<a href="#inventory"><?php _e('Inventory', 'jigoshop'); ?></a>
			</li>
			<?php endif; ?>

			<li>
				<a href="#attributes"><?php _e('Attributes', 'jigoshop'); ?></a>
			</li>

			<li>	
				<a href="#grouped"><?php _e('Grouped', 'jigoshop') ?></a>
			</li>

			<li>	
				<a href="#files"><?php _e('File', 'jigoshop') ?></a>
			</li>
			
			<?php do_action('jigoshop_product_write_panel_tabs'); ?>
			<?php do_action('product_write_panel_tabs'); // Legacy ?>
		</ul>
		
		<div id="general" class="panel jigoshop_options_panel">
			<fieldset>
			<?php	
				// Product Type
				$terms = wp_get_object_terms( $thepostid, 'product_type' );
				$product_type = ($terms) ? current($terms)->slug : 'simple';

				echo jigoshop_form::select(
					'product-type', 
					__('Product Type', 'jigoshop'),
					apply_filters('jigoshop_product_type_selector', array(
						'simple'			=> __('Simple', 'jigoshop'),
						'downloadable'	=> __('Downloadable', 'jigoshop'),
						'grouped'		=> __('Grouped', 'jigoshop'),
						'virtual'		=> __('Virtual', 'jigoshop'),
						'variable'		=> __('Variable', 'jigoshop'),
					)),
					$product_type
				);

				// SKU
				if ( get_option('jigoshop_enable_sku') !== 'no' ) {
					echo jigoshop_form::input( 'sku', 'SKU', 'Leave blank to use product ID' );
				}
			?>
			</fieldset>

			<fieldset>
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
			?>
			</fieldset>

			<fieldset>
			<?php
				// Weight
				// TODO: Do we need this check? -Rob
				if( get_option('jigoshop_enable_weight') !== 'no' ) {
					echo jigoshop_form::input( 'weight', 'Weight' ); // Missing placeholder attribute 0.00
				}

				// Dimensions
				if( get_option('jigoshop_enable_dimensions', true) !== 'no' ) {
					echo jigoshop_form::input( 'length', 'Dimensions' ); // Missing Unit // get_option('jigoshop_dimension_unit')
					//echo jigoshop_form::input( 'width', 'Width' ); // Missing Unit // get_option('jigoshop_dimension_unit')
					//echo jigoshop_form::input( 'height', 'Height' ); // Missing Unit // get_option('jigoshop_dimension_unit')
				}
			?>
			</fieldset>
		</div>
		<div id="tax" class="panel jigoshop_options_panel">
			<fieldset>
			<?php
				// Featured
				echo jigoshop_form::checkbox( 'featured', 'Featured?');

				// Visibility
				echo jigoshop_form::select( 'visibility', 'Visibility',
					array(
						'visible'	=> 'Catalog & Search',
						'catalog'	=> 'Catalog Only',
						'search'	=> 'Search Only',
						'Hidden'	=> 'Hidden'
					) );
			?>
			</fieldset>
			<fieldset>
			<?php

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
			</fieldset>
		</div>
		<?php if (get_option('jigoshop_manage_stock')=='yes') : ?>
		<div id="inventory" class="panel jigoshop_options_panel">
			<fieldset>
			<?php
			// manage stock
			echo jigoshop_form::checkbox( 'manage_stock', 'Manage Stock?' );

			?>
			</fieldset>
			<fieldset>
			<?php
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
			</fieldset>
		</div>
		<?php endif; 

		// Attributs begin here
		// TODO: Much love needs to be applied here

		?>
		<div id="attributes" class="panel">
			<div class="toolbar">
				<button type="button" class="button button-primary add_attribute"><?php _e('Add Attribute', 'jigoshop'); ?></button>
				<select name="attribute_taxonomy" class="attribute_taxonomy">
					<option value="" data-type="custom"><?php _e('Custom product attribute', 'jigoshop'); ?></option>
					<?php
						$attribute_taxonomies = jigoshop_product::getAttributeTaxonomies();
						if ( $attribute_taxonomies ) :
					    	foreach ($attribute_taxonomies as $tax) :
					    		echo '<option value="'.sanitize_title($tax->attribute_name).'" data-type="'.$tax->attribute_type.'">'.$tax->attribute_name.'</option>';
					    	endforeach;
					    endif;
					?>
				</select>
			</div>
			<div class="jigoshop_attributes_wrapper">
				<?php /**<table cellpadding="0" cellspacing="0" class="jigoshop_attributes" style="display: none;">
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
						    			
										<td class="handle center">
											<a href="#">X</a>
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
										<?php /*
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
											</div> ?>
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
				</table> **/ ?>
				<?php
					// TODO: This needs refactoring

					// This is getting all the taxonomies
					$attribute_taxonomies = jigoshop_product::getAttributeTaxonomies();

					// This is whats applied to the product
					$attributes = get_post_meta($post->ID, 'product_attributes', true);
					
					$i = -1;

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
				?>
				<div class="postbox attribute closed <?php echo $attribute_taxonomy_name; ?>" rel="<?php echo $position; ?>"  <?php if ( !$has_terms ) echo 'style="display:none"'; ?>>
					<button type="button" class="hide_row button">Remove</button>
					<h3 class="handle"><?php echo $tax->attribute_name; ?></h3>

					<input type="hidden" name="attribute_names[<?php echo $i; ?>]" value="<?php echo $tax->attribute_name; ?>" />
					<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="1" />
					<input type="hidden" name="attribute_enabled[<?php echo $i; ?>]" value="1" />
					<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo $position; ?>" />

					<div class="inside">
						<table>
							<tr>
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
								<td>
									<label>Visible?
										<input type="checkbox" <?php checked(boolval( isset($attribute) ? $attribute['visible'] : 0 ), true); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" />
									</label>

									<?php if ($tax->attribute_type!="select") : // always disable variation for select elements ?>
									<label>Variation
										<input type="checkbox" <?php checked(boolval( isset($attribute) ? $attribute['variation'] : 0 ), true); ?> name="attribute_variation[<?php echo $i; ?>]" value="1" />
									</label>
									<?php endif; ?>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<?php endforeach; ?>

			</div>
			<div class="clear"></div>
		</div>

		<div id="grouped" class="panel jigoshop_options_panel">
			<?php
			// Grouped Products
			// TODO: Needs refactoring & a bit of love
			$posts_in = (array) get_objects_in_term( get_term_by( 'slug', 'grouped', 'product_type' )->term_id, 'product_type' );
			$posts_in = array_unique($posts_in);

			if( ! (bool) $posts_in ) {

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
				echo jigoshop_form::select( 'parent_id', 'Product Group', $options, $post->post_parent );
			}
			
			// Ordering
			echo jigoshop_form::input( 'menu_order', 'Sort Order', false, $post->menu_order );
			?>
		</div>

		<div id="files" class="panel jigoshop_options_panel">
			<fieldset>
			<?php 
				// DOWNLOADABLE OPTIONS
				// File URL
				// TODO: Refactor this into a helper
				echo jigoshop_form::input( 'file_path', __('File Path', 'jigoshop') );

				// $file_path = get_post_meta($post->ID, 'file_path', true);
				// $field = array( 'id' => 'file_path', 'label' => __('Internal Path', 'jigoshop') );
				// echo '<p class="form-field">
				// 	<label for="'.$field['id'].'">'.$field['label'].':</label>
				// 	<span style="float:left">'.ABSPATH.'</span><input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('path to file on your server', 'jigoshop').'" /></p>';

				// File URL (External URL)
				// $file_url = get_post_meta($post->ID, 'file_url', true);
				// $field = array( 'id' => 'file_url', 'label' => __('External URL', 'jigoshop') );
				// echo '<p class="form-field">
				// 	<label for="'.$field['id'].'">'.$field['label'].':</label>
				// 	<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_url.'" placeholder="'.__('An external URL to the file', 'jigoshop').'" /><span class="description">' . __('Note: This URL will be visible to the customer.', 'jigoshop') . '</span></p>';

				// Download Limit
				echo jigoshop_form::input( 'download_limit', 'Download Limit', 'Leave blank for unlimited re-downloads' );
				do_action( 'additional_downloadable_product_type_options' )
			?>
			</fieldset>
		</div>
		
		<?php do_action('jigoshop_product_write_panels'); ?>
		<?php do_action('product_write_panels'); ?>
	</div>
	<?php
}