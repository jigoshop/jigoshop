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

			<li class="tax_form_tab">
				<a href="#tax"><?php _e('Tax & Form', 'jigoshop') ?></a>
			</li>

			<?php if (get_option('jigoshop_manage_stock')) : ?>
			<li class="inventory_tab">
				<a href="#inventory"><?php _e('Inventory', 'jigoshop'); ?></a>
			</li>
			<?php endif; ?>

			<li class="attributes_tab">
				<a href="#attributes"><?php _e('Attributes', 'jigoshop'); ?></a>
			</li>

			<li class="grouped_tab">	
				<a href="#grouped"><?php _e('Grouping', 'jigoshop') ?></a>
			</li>

			<li class="file_tab">	
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
					echo jigoshop_form::input( 'sku', 'SKU', null, null, 'short', $post->ID );
				}
			?>
			</fieldset>

			<fieldset id="price_fieldset">
			<?php
				// Regular Price
				echo jigoshop_form::input( 'regular_price', 'Regular Price', null, null, 'short', null, array('after_label' => ' ('.get_jigoshop_currency_symbol().')') );

				// Sale Price
				echo jigoshop_form::input( 'sale_price', 'Sale Price', '<a href="#" class="sale_schedule">Schedule</a>', null, 'short', null, array('after_label' => ' ('.get_jigoshop_currency_symbol().')' ));

				// Sale Price date range
				// TODO: Convert this to a helper somehow?
				$field = array( 'id' => 'sale_price_dates', 'label' => __('Sale Price Dates', 'jigoshop') );
				
				$sale_price_dates_from = get_post_meta($thepostid, 'sale_price_dates_from', true);
				$sale_price_dates_to = get_post_meta($thepostid, 'sale_price_dates_to', true);
				
				echo '	<p class="form-field sale_price_dates_fields">
							<label for="'.$field['id'].'_from">'.$field['label'].':</label>
							<input type="text" class="short date-pick" name="'.$field['id'].'_from" id="'.$field['id'].'_from" value="';
				if ($sale_price_dates_from) echo date('Y-m-d', $sale_price_dates_from);
				echo '" placeholder="' . __('From', 'jigoshop') . ' (' . date('Y-m-d'). ')" maxlength="10" />
							<input type="text" class="short date-pick" name="'.$field['id'].'_to" id="'.$field['id'].'_to" value="';
				if ($sale_price_dates_to) echo date('Y-m-d', $sale_price_dates_to);
				echo '" placeholder="' . __('To', 'jigoshop') . ' (' . date('Y-m-d'). ')" maxlength="10" />
							<a href="#" class="cancel_sale_schedule">Cancel</a>
						</p>';
			?>
			</fieldset>

			<fieldset>
			<?php
				// Featured
				echo jigoshop_form::checkbox( 'featured', 'Featured?', false, __('Enable this option to feature this product', 'jigoshop') );

				// Visibility
				/*echo jigoshop_form::select( 'visibility', 'Visibility',
					array(
						'visible'	=> 'Catalog & Search',
						'catalog'	=> 'Catalog Only',
						'search'	=> 'Search Only',
						'Hidden'	=> 'Hidden'
					) );*/
			?>
			</fieldset>
		</div>
		<div id="tax" class="panel jigoshop_options_panel">
			<fieldset>
			<?php

				// Tax Status
				echo jigoshop_form::select( 'tax_status', 'Tax Status',
					array(
						'taxable'	=> 'Taxable',
						'shipping'	=> 'Shipping',
						'none'		=> 'None'
					), $data[$field['id']] );

				//TODO: need to think how to populate this new array with the old one. Used to be id=>'tax_class'
				$field = array( 'id' => 'tax_classes', 'label' => __('Tax Classes', 'jigoshop') );
	                        
	            $_tax = new jigoshop_tax();
	            $tax_classes = $_tax->get_tax_classes();
	            $selections = $data[$field['id']];
	            echo '<p><label for="'.$field['id'].'">'.$field['label'].':</label>';

				echo '<input type="checkbox" class="checkbox" name="'.$field['id'].'[]" value="" '; if ($selections && in_array('', $selections)) echo 'checked="checked"'; echo '/><span class="checkbox-label">'.__('Standard', 'jigoshop').'</span></p>';
	                        
	            if ($tax_classes) :
	                
	                foreach ($tax_classes as $tax_class) :
	                    echo '<p><label for="'.$field['id'].'">&nbsp;</label><input type="checkbox" class="checkbox" name="'.$field['id'].'[]" value="'. sanitize_title($tax_class) .'" ';
	                    if ($selections && in_array(sanitize_title($tax_class), $selections)) :
	                        echo 'checked="checked"';
	                    endif;
	                    echo ' /><span class="checkbox-label">'. __($tax_class, 'jigoshop') .'</span></p>';
	                endforeach;
	                
	            endif;
	            echo '</p>';

			?>
			</fieldset>

			<fieldset id="form_fieldset">
			<?php
				// Weight
				if( get_option('jigoshop_enable_weight') !== 'no' ) {
					echo jigoshop_form::input( 'weight', 'Weight', null, null, 'short', '0.00', array('after_label' => ' ('.get_option('jigoshop_weight_unit').')') ); // Missing placeholder attribute 0.00
				}

				// Dimensions
				if( get_option('jigoshop_enable_dimensions', true) !== 'no' ) {
					echo '
					<p class="form-field dimensions_field">
						<label for"product_length">'. __('Dimensions', 'jigoshop') . ' ('.get_option('jigoshop_dimension_unit').')' . '</label>
						<input type="text" name="length" class="short" value="' . get_post_meta( $thepostid, 'length', true ) . '" placeholder="'. __('Length', 'jigoshop') . '" />
						<input type="text" name="width" class="short" value="' . get_post_meta( $thepostid, 'width', true ) . '" placeholder="'. __('Width', 'jigoshop') . '" />
						<input type="text" name="height" class="short" value="' . get_post_meta( $thepostid, 'height', true ) . '" placeholder="'. __('Height', 'jigoshop') . '" />
					</p>
					';
				}
			?>
			</fieldset>
		</div>
		<?php if (get_option('jigoshop_manage_stock')=='yes') : ?>
		<div id="inventory" class="panel jigoshop_options_panel">
			<fieldset>
			<?php
			// manage stock
			echo jigoshop_form::checkbox( 'manage_stock', 'Manage Stock?', false, __('Handle stock for me', 'jigoshop') );

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
				), false, false, 'select' );

			echo '</div>';
			?>
			</fieldset>
		</div>
		<?php endif; ?>

		<div id="attributes" class="panel">
			<?php do_action('jigoshop_attributes_display'); ?>
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

				$options = array( null => '&ndash; Pick a Product Group &ndash;' );

				if( $grouped_products ) foreach( $grouped_products as $product ) {
					if ($product->ID==$post->ID) continue;

					$options[$product->ID] = $product->post_title;
				}
				// Only echo the form if we have grouped products
				echo jigoshop_form::select( 'parent_id', 'Product Group', $options, $post->post_parent, false, 'select' );
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
			$file_path = get_post_meta($post->ID, 'file_path', true);
			$field = array( 'id' => 'file_path', 'label' => __('File Path', 'jigoshop') );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
				<input type="text" class="file_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.site_url().'" />
				<input type="button"  class="upload_file_button button" data-postid="'.$post->ID.'" value="'.__('Upload a file', 'jigoshop').'" />
			</p>';

			// Download Limit
			echo jigoshop_form::input( 'download_limit', 'Download Limit', 'Leave blank for unlimited re-downloads' );
			do_action( 'additional_downloadable_product_type_options' );
			?>
			</fieldset>
		</div>
		
		<?php do_action('jigoshop_product_write_panels'); ?>
		<?php do_action('product_write_panels'); ?>
	</div>
	<?php
}

add_action('jigoshop_attributes_display', 'attributes_display');
function attributes_display() { ?>
	
	<div class="toolbar">
		
		<button type="button" class="button button-secondary add_attribute"><?php _e('Add Attribute', 'jigoshop'); ?></button>
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
		
		<?php do_action('jigoshop_display_attribute'); ?>

	</div>
	<div class="clear"></div>
<?php 
}

add_action('jigoshop_display_attribute', 'display_attribute');
function display_attribute() { ?>
	<?php
		global $post;
		// TODO: This needs refactoring

		// This is getting all the taxonomies
		$attribute_taxonomies = jigoshop_product::getAttributeTaxonomies();

		// Sneaky way of doing sort by desc
		$attribute_taxonomies = array_reverse($attribute_taxonomies);

		// This is whats applied to the product
		$attributes = get_post_meta($post->ID, 'product_attributes', true);


	?>
	<?php if( ! $attributes ): ?>
		<div class="demo attribute">
			<a href="http://forum.jigoshop.com/kb" target="_blank" class="overlay"><span>Click me to learn more about Attributes</span></a>
			<div class="inside">
				<div class="postbox attribute">
					<button type="button" class="hide_row button">Remove</button>
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="handle">Size</h3>

					<div class="inside">
						<table>
							<tr>
								<td class="options">
									<input type="text" class="attribute-name" value="Size" disabled="disabled" />

									<div>
										<label>
											<input type="checkbox" value="1" checked="checked" />
											Display on product page
										</label>

									</div>
								</td>
								<td class="value">
									<select>
										<option>Choose an option…</option>			
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="postbox attribute">
					<button type="button" class="hide_row button">Remove</button>
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="handle">Colour</h3>
				</div>

				<div class="postbox attribute">
					<button type="button" class="hide_row button">Remove</button>
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="handle">Material</h3>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php
		$i = -1;
		foreach ($attribute_taxonomies as $tax) : $i++;

		$attribute_taxonomy_name = sanitize_title($tax->attribute_name);
		if (isset($attributes[$attribute_taxonomy_name])) $attribute = $attributes[$attribute_taxonomy_name];
		$position = (isset($attribute['position'])) ? $attribute['position'] : 0;

		$allterms = wp_get_post_terms( $post->ID, 'pa_'.$attribute_taxonomy_name );

		$has_terms = ( is_wp_error( $allterms ) || !$allterms || sizeof( $allterms ) == 0 ) ? 0 : 1;
		$term_slugs = array();
		if ( !is_wp_error( $allterms ) && $allterms ) :
			foreach ($allterms as $term) :
				$term_slugs[] = $term->slug;
			endforeach;
		endif;
	?>

	<div class="postbox attribute <?php if ( $has_terms ) echo 'closed'; ?> <?php echo $attribute_taxonomy_name; ?>" data-attribute-name="<?php echo $attribute_taxonomy_name; ?>" rel="<?php echo $position; ?>"  <?php if ( !$has_terms ) echo 'style="display:none"'; ?>>
		<button type="button" class="hide_row button">Remove</button>
		<div class="handlediv" title="Click to toggle"><br></div>
		<h3 class="handle"><?php echo $tax->attribute_name; ?></h3>

		<input type="hidden" name="attribute_names[<?php echo $i; ?>]" value="<?php echo $tax->attribute_name; ?>" />
		<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="1" />
		<input type="hidden" name="attribute_enabled[<?php echo $i; ?>]" value="1" />
		<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo $position; ?>" />

		<div class="inside">
			<table>
				<tr>
					<td class="options">
						<input type="text" class="attribute-name" name="attribute_names[<?php echo $i; ?>]" value="<?php echo $tax->attribute_name; ?>" disabled="disabled" />

						<div>
							<label>
								<input type="checkbox" <?php checked(boolval( isset($attribute) ? $attribute['visible'] : 0 ), true); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /><?php _e('Display on product page', 'jigoshop'); ?>
							</label>

							<?php if ($tax->attribute_type!="select") : // always disable variation for select elements ?>
							<label class="attribute_is_variable">
								<input type="checkbox" <?php checked(boolval( isset($attribute) ? $attribute['variation'] : 0 ), true); ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /><?php _e('Is for variations', 'jigoshop'); ?>
							</label>
							<?php endif; ?>
						</div>
					</td>
					<td class="value">
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
									<a class="toggle" href="#"><?php _e('Toggle');?></a>&nbsp;|
									<a class="show-all" href="#"><?php _e('Show all'); ?></a>
								</div>

							<?php elseif ($tax->attribute_type=="text") : ?>
								<textarea name="attribute_values[<?php echo $i; ?>]">
									<?php 												
									if ($allterms) :
										$prettynames = array();
										foreach ($allterms as $term) :
											$prettynames[] = $term->name;
										endforeach;
										echo implode(',', $prettynames);
									endif;
									?>
								</textarea>
							<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<?php endforeach; ?>
	<?php
	// Custom Attributes
	if ( $attributes ) foreach ($attributes as $attribute) : 
		if ($attribute['is_taxonomy']) continue;

		$i++;

		$position = (isset($attribute['position'])) ? $attribute['position'] : 0;

	?>
	<div class="postbox attribute closed <?php echo sanitize_title($attribute['name']); ?>" rel="<?php echo isset($attribute['position']) ? $attribute['position'] : 0; ?>">
		<button type="button" class="hide_row button">Remove</button>
		<div class="handlediv" title="Click to toggle"><br></div>
		<h3 class="handle"><?php echo esc_attr( $attribute['name'] ); ?></h3>

		<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="0" />
		<input type="hidden" name="attribute_enabled[<?php echo $i; ?>]" value="1" />
		<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo $position; ?>" />

		<div class="inside">
			<table>
				<tr>
					<td class="options">
						<input type="text" class="attribute-name" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $attribute['name'] ); ?>" />

						<div>
							<label>
								<input type="checkbox" <?php checked(boolval( isset($attribute) ? $attribute['visible'] : 0 ), true); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /><?php _e('Display on product page', 'jigoshop'); ?>
							</label>

							<?php if ($tax->attribute_type!="select") : // always disable variation for select elements ?>
							<label class="attribute_is_variable">
								<input type="checkbox" <?php checked(boolval( isset($attribute) ? $attribute['variation'] : 0 ), true); ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /><?php _e('Is for variations', 'jigoshop'); ?>
							</label>
							<?php endif; ?>
						</div>
					</td>

					<td class="value">
						<textarea name="attribute_values[<?php echo $i; ?>]" cols="5" rows="2"><?php echo esc_textarea( $attribute['value'] ); ?></textarea>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<?php endforeach; ?>
<?php }