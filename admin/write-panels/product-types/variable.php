<?php
/**
 * Variable Product Type
 * 
 * Functions specific to variable products (for the write panels)
 *
 * @author 		Jigowatt
 * @category 	Admin Write Panel Product Types
 * @package 	JigoShop
 */
 
/**
 * Product Type Javascript
 * 
 * Javascript for the variable product type
 *
 * @todo this needs to be moved to some javascript file
 * @since 		1.0
 */
function variable_product_write_panel_js() {
	global $post;
	
	$attributes = maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) );
	if (!isset($attributes)) $attributes = array();
	?>
	jQuery(function(){
		
		jQuery('button.add_configuration').live('click', function(){
		
			jQuery('.jigoshop_configurations').block({ message: null, overlayCSS: { background: '#fff url(<?php echo jigoshop::plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
					
			var data = {
				action: 'jigoshop_add_variation',
				post_id: <?php echo $post->ID; ?>,
				security: '<?php echo wp_create_nonce("add-variation"); ?>'
			};

			jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
				
				var variation_id = parseInt(response);
				
				var loop = jQuery('.jigoshop_configuration').size();
				
				jQuery('.jigoshop_configurations').append('<div class="jigoshop_configuration">\
					<p>\
						<button type="button" class="remove_variation button"><?php _e('Remove', 'jigoshop'); ?></button>\
						<strong><?php _e('Variation:', 'jigoshop'); ?></strong>\
						<?php
							if ($attributes) foreach ($attributes as $attribute) :

								if ( ! $attribute['variation'] ) continue;
								
								$options = $attribute['value'];
								if (!is_array($options)) $options = explode(',', $options);
								
								$sanitized_name = sanitize_title($attribute['name']);
								
								echo '<select name="tax_' . $sanitized_name .'[\' + loop + \']"><option value="">'.__('Any ', 'jigoshop').$attribute['name'].'&hellip;</option>';
								
								if ( taxonomy_exists( 'pa_'.$sanitized_name )) :
									$terms = get_terms( 'pa_'.$sanitized_name, 'orderby=slug&hide_empty=1' );
									foreach ( $terms as $term ):
										echo '<option value="'.$term->slug.'">'.$term->name.'</option>';
									endforeach;
								endif;

								echo '</select>';
	
							endforeach;
					?><input type="hidden" name="variable_post_id[' + loop + ']" value="' + variation_id + '" /></p>\
					<table cellpadding="0" cellspacing="0" class="jigoshop_variable_attributes">\
						<tbody>\
							<tr>\
								<td class="upload_image"><img src="<?php echo jigoshop::plugin_url().'/assets/images/placeholder.png' ?>" width="60px" height="60px" /><input type="hidden" name="upload_image_id[' + loop + ']" class="upload_image_id" /><input type="button" class="upload_image_button button" rel="" value="<?php _e('Product Image', 'jigoshop'); ?>" /></td>\
								<td><label><?php _e('SKU:', 'jigoshop'); ?></label><input type="text" size="5" name="variable_sku[' + loop + ']" /></td>\
								<td><label><?php _e('Weight', 'jigoshop').' ('.get_option('jigoshop_weight_unit').'):'; ?></label><input type="text" size="5" name="variable_weight[' + loop + ']" /></td>\
								<td><label><?php _e('Stock Qty:', 'jigoshop'); ?></label><input type="text" size="5" name="variable_stock[' + loop + ']" /></td>\
								<td><label><?php _e('Price:', 'jigoshop'); ?></label><input type="text" size="5" name="variable_price[' + loop + ']" placeholder="<?php _e('e.g. 29.99', 'jigoshop'); ?>" /></td>\
								<td><label><?php _e('Sale Price:', 'jigoshop'); ?></label><input type="text" size="5" name="variable_sale_price[' + loop + ']" placeholder="<?php _e('e.g. 29.99', 'jigoshop'); ?>" /></td>\
								<td><label><?php _e('Enabled', 'jigoshop'); ?></label><input type="checkbox" class="checkbox" name="variable_enabled[' + loop + ']" checked="checked" /></td>\
							</tr>\
						</tbody>\
					</table>\
				</div>');
				
				jQuery('.jigoshop_configurations').unblock();

			});

			return false;
		
		});
		
		jQuery('button.remove_variation').live('click', function(){
			var answer = confirm('<?php _e('Are you sure you want to remove this variation?', 'jigoshop'); ?>');
			if (answer){
				
				var el = jQuery(this).parent().parent();
				
				var variation = jQuery(this).attr('rel');
				
				if (variation>0) {
				
					jQuery(el).block({ message: null, overlayCSS: { background: '#fff url(<?php echo jigoshop::plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
					
					var data = {
						action: 'jigoshop_remove_variation',
						variation_id: variation,
						security: '<?php echo wp_create_nonce("delete-variation"); ?>'
					};
	
					jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
						// Success
						jQuery(el).fadeOut('300', function(){
							jQuery(el).remove();
						});
					});
					
				} else {
					jQuery(el).fadeOut('300', function(){
						jQuery(el).remove();
					});
				}
				
			}
			return false;
		});
		
		var current_field_wrapper;
		
		window.send_to_editor_default = window.send_to_editor;

		jQuery('.upload_image_button').live('click', function(){
			
			var post_id = jQuery(this).attr('rel');
			
			var parent = jQuery(this).parent();
			
			current_field_wrapper = parent;
			
			window.send_to_editor = window.send_to_cproduct;
			
			formfield = jQuery('.upload_image_id', parent).attr('name');
			tb_show('', 'media-upload.php?post_id=' + post_id + '&amp;type=image&amp;TB_iframe=true');
			return false;
		});

		window.send_to_cproduct = function(html) {
			
			imgurl = jQuery('img', html).attr('src');
			imgclass = jQuery('img', html).attr('class');
			imgid = parseInt(imgclass.replace(/\D/g, ''), 10);
			
			jQuery('.upload_image_id', current_field_wrapper).val(imgid);

			jQuery('img', current_field_wrapper).attr('src', imgurl);
			tb_remove();
			window.send_to_editor = window.send_to_editor_default;
			
		}

	});
	<?php
	
}
add_action('product_write_panel_js', 'variable_product_write_panel_js');


/**
 * Delete variation via ajax function
 *
 * @since 		1.0
 */
add_action('wp_ajax_jigoshop_remove_variation', 'jigoshop_remove_variation');

function jigoshop_remove_variation() {
	
	check_ajax_referer( 'delete-variation', 'security' );
	$variation_id = intval( $_POST['variation_id'] );
	wp_delete_post( $variation_id );
	die();
	
}


/**
 * Add variation via ajax function
 *
 * @since 		1.0
 */
add_action('wp_ajax_jigoshop_add_variation', 'jigoshop_add_variation');

function jigoshop_add_variation() {
	
	check_ajax_referer( 'add-variation', 'security' );
	
	$post_id = intval( $_POST['post_id'] );

	$variation = array(
		'post_title' => 'Product #' . $post_id . ' Variation',
		'post_content' => '',
		'post_status' => 'publish',
		'post_author' => get_current_user_id(),
		'post_parent' => $post_id,
		'post_type' => 'product_variation'
	);
	$variation_id = wp_insert_post( $variation );
	
	echo $variation_id;
	
	die();
	
}

/**
 * Product Type selector
 * 
 * Adds this product type to the product type selector in the product options meta box
 *
 * @since 		1.0
 *
 * @param 		string $product_type Passed the current product type so that if it keeps its selected state
 */
function variable_product_type_selector( $product_type ) {
	
	echo '<option value="variable" '; if ($product_type=='variable') echo 'selected="selected"'; echo '>'.__('Variable','jigoshop').'</option>';

}
add_action('product_type_selector', 'variable_product_type_selector');




	

	


// Possibility to reuse old saving functions for new contexts?
// class jigoshop_product_meta_variable extends jigoshop_product_meta
class jigoshop_prduct_meta_variable
{
	public function __construct() {
		add_action( 'jigoshop_process_product_meta_variable', array(&$this,'save'), 1 );
		add_action( 'jigoshop_product_type_options_box', array(&$this, 'display') );
	}

	/**
	 * Process the product variable meta
	 *
	 * @param		int		Product ID
	 * @return		void
	 */
	public function save( $parent_id ) {
		global $wpdb;

		// Do not run if there are no variations
		if ( ! isset($_POST['variations']) )
			return false;

		// Get the attributes to be used later
		$attributes = (array) maybe_unserialize( get_post_meta($parent_id, 'product_attributes', true) );

		foreach( $_POST['variations'] as $ID => $meta ) {
			
			// Update the post data
			// TODO: Set up titles as #SKU #Variation1 ..2..3
			$wpdb->update( $wpdb->posts, array(
				'post_title'		=> "#{$parent_id}: Variation: #{$ID}",
				'post_status'	=> isset($meta['enabled']) ? 'publish' : 'draft'
			), array( 'ID' => $ID ) );

			// Set variation meta data
			update_post_meta( $ID, 'sku',			$meta['sku'] );
			update_post_meta( $ID, 'regular_price',	$meta['regular_price'] );
			update_post_meta( $ID, 'sale_price',		$meta['sale_price'] );

			update_post_meta( $ID, 'weight',		$meta['weight'] );
			// update_post_meta( $ID, 'length',		$meta['length'] );
			// update_post_meta( $ID, 'height',		$meta['height'] );
			// update_post_meta( $ID, 'width',		$meta['width'] );

			update_post_meta( $ID, 'stock',			$meta['stock'] );
			update_post_meta( $ID, '_thumbnail_id',	$meta['_thumbnail_id'] );

			// Refresh taxonomy attributes
			$current_meta = get_post_custom( $ID );

			foreach ($current_meta as $name => $value) {
				// Skip if there are no attributes
				if ( ! strstr($name, 'tax_'))
					continue;

				// Remove the attribute
				delete_post_meta( $ID, $name );
			}

			// Update taxonomies
			foreach ( $attributes as $attribute ) {

				// Skip if attribute is not for variation
				if ( ! $attribute['variation'] )
					continue;
				
				// Set the data
				$key = 'tax_' . sanitize_title($attribute['name']);
				update_post_meta( $ID, $key, $meta[$key]);
			}
		}
		exit();
	}

	public function display() {
		global $post;

		// Get the attributes
		$attributes = (array) maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) );

		// Get all variations of the product
		$variations = get_posts(array(
			'post_type'		=> 'product_variation',
			'post_status' 	=> array('draft', 'publish'),
			'numberposts' 	=> -1,
			'orderby' 		=> 'id',
			'order' 		=> 'asc',
			'post_parent' 	=> $post->ID
		));

		// Don't display anything if we have no variations
		if ( ! $variations )
			return false;
		?>

		<div id="variable_product_options" class="panel">
		<?php if ( $this->has_variable_attributes( $attributes ) ): ?>

			<!-- <p class='bulk_edit'>
				<strong><?php _e('Bulk edit:', 'jigoshop'); ?></strong>
				<a class="button set set_all_prices" href="#"><?php _e('Prices', 'jigoshop'); ?></a>
				<a class="button set set_all_sale_prices" href="#"><?php _e('Sale prices', 'jigoshop'); ?></a>
				<a class="button set set_all_stock" href="#"><?php _e('Stock', 'jigoshop'); ?></a>
				<a class="button toggle toggle_downloadable" href="#"><?php _e('Downloadable', 'jigoshop'); ?></a>
				<a class="button toggle toggle_virtual" href="#"><?php _e('Virtual', 'jigoshop'); ?></a>
				<a class="button toggle toggle_enabled" href="#"><?php _e('Enabled', 'jigoshop'); ?></a>
				<a class="button set set_all_paths" href="#"><?php _e('File paths', 'jigoshop'); ?></a>
				<a class="button set set_all_limits" href="#"><?php _e('Download limits', 'woothemes'); ?></a>
			</p> -->

			<div class="jigoshop_configurations">

				<?php foreach( $variations as $variation ): ?>

				<?php

					// GET THE DATA
					// Get the variation meta
					$meta = get_post_custom( $variation->ID );

					// Get the image url if we have one
					$image = jigoshop::plugin_url().'/assets/images/placeholder.png';
					if ( $image_id = $meta['_thumbnail_id'][0] ) {
						$image = wp_get_attachment_url( $image_id );
					}
				?>
				<!-- START CONFIG PANEL -->
				<div class="jigoshop_configuration">
					<p>
						<button type="button" class="remove_variation button" rel="<?php echo $variation->ID; ?>"><?php _e('Remove', 'jigoshop'); ?></button>
						<strong>#<?php echo $variation->ID; ?> &mdash; <?php _e('Variation:', 'jigoshop'); ?></strong>
						<?php echo $this->attribute_selector($attributes, $variation); ?>
					</p>

					<table cellpadding="0" cellspacing="0" class="jigoshop_variable_attributes">
						<tbody>
							<tr>

								<td class="upload_image" rowspan="2">
									<a href="#" class="upload_image_button <?php if ($image) echo 'remove'; ?>" rel="<?php echo $variation->ID; ?>">
										<img src="<?php echo $image ?>" width="60px" height="60px" />
										<input type="hidden" name="<?php echo $this->field_name('_thumbnail_id', $variation) ?>" class="upload_image_id" value="<?php echo $image_id; ?>" />
										<!-- TODO: APPEND THIS IN JS <span class="overlay"></span> -->
									</a>
								</td>

								<td>
									<label><?php _e('SKU:', 'jigoshop'); ?>
										<input type="text" size="5" name="<?php echo $this->field_name('sku', $variation) ?>" value="<?php echo isset($meta['sku'][0]) ? $meta['sku'][0] : null; ?>" />
									</label>
								</td>

								<td>
									<label><?php _e('Stock Qty:', 'jigoshop'); ?>
										<input type="text" size="5" name="<?php echo $this->field_name('stock', $variation) ?>" value="<?php echo isset($meta['stock'][0]) ? $meta['stock'][0] : null; ?>" />
									</label>
								</td>

								<td>
									<label><?php _e('Weight', 'jigoshop') ?>
										<input type="text" size="5" name="<?php echo $this->field_name('weight', $variation) ?>" value="<?php echo isset($meta['weight'][0]) ? $meta['weight'][0] : null; ?>" />
									</label>
								</td>

								<?php // TODO: Add lxhxw here ?>

								<td>
									<label><?php _e('Price:', 'jigoshop'); ?>
										<input type="text" size="5" name="<?php echo $this->field_name('regular_price', $variation) ?>" value="<?php echo isset($meta['regular_price'][0]) ? $meta['regular_price'][0] : null; ?>" />
									</label>
								</td>

								<td>
									<label><?php _e('Sale Price:', 'jigoshop'); ?>
										<input type="text" size="5" name="<?php echo $this->field_name('sale_price', $variation) ?>" value="<?php echo isset($meta['sale_price'][0]) ? $meta['sale_price'][0] : null; ?>" />
									</label>
								</td>

								<td>
									<label><?php _e('Enabled', 'jigoshop'); ?>
										<input type="checkbox" class="checkbox" name="<?php echo $this->field_name('enabled', $variation) ?>" <?php checked($variation->post_status, 'publish'); ?> />
									</label>
								</td>
							</tr>
							<tr>
								<td colspan="6"><span><?php //todo ?></span></td>
							</tr>
						</tbody>
					</table>
				</div>
				<!-- END CONFIG PANEL -->
				<?php endforeach; ?>
			</div>
			<button type="button" class="button button-primary add_configuration <?php disabled($this->has_variable_attributes($attributes), false); ?>"><?php _e('Add Variation', 'jigoshop'); ?></button>

			<div class="clear" />

		<?php else : ?>

			<div class='inline updated'>
				<p><?php _e('Before you can start adding variations you must set up and save some variable attributes via the Attributes tab', 'jigoshop' ); ?></p>
			</div>

		<?php endif; ?>

		</div>
		</div> <?php // We're missing an closing div somewhere ?>

	<?php
	}

	/**
	 * Returns a specially formatted field name for variations
	 *
	 * @param		string		Field Name
	 * @param		object		Variation Post Object
	 * @return		string
	 */
	private function field_name( $name, $variation ) {
		return "variations[{$variation->ID}][{$name}]";
	}

	/**
	 * Returns all the possible variable attributes in select form
	 *
	 * @param		array		Attributes array
	 * @param		object		Variation Post Object
	 * @return		HTML
	 */
	private function attribute_selector( $attributes, $variation ) {
		global $post;

		$html = null;

		// Attribute Variation Selector
		foreach ( $attributes as $attr ) {

			// If not variable attribute then skip
			if ( ! $attr['variation'] )
				continue;

			// Get current value for variation (if set)
			$selected = get_post_meta( $variation->ID, 'tax_' . sanitize_title($attr['name']), true );

			$html .= '<select name="' . $this->field_name('tax_' . sanitize_title($attr['name']), $variation) . '" >
				<option value="">Any</option>';

			// Get terms for attribute taxonomy or value if its a custom attribute
			if ( $attr['is_taxonomy'] ) {
				$options = wp_get_post_terms( $post->ID, 'pa_'.sanitize_title($attr['name']));
				foreach( $options as $option ) {
					$html .= '<option value="'.$option->slug.'" '.selected($selected, $option->slug, false).'>'.$option->name.'</option>';
				}
			}
			else {
				$options = explode(', ', $attr['value']);
				foreach( $options as $option ) {
					$option = trim($option);
					$html .= '<option '.selected($selected, $option, false).' value="'.$option.'">'.$option.'</option>';
				}
			}

			$html .= '</select>';
		}

		return $html;
	}

	/**
	 * Checks all the product attributes for variation defined attributes
	 *
	 * @param		array		Attributes
	 * @return		bool
	 */
	private function has_variable_attributes( array $attributes ) {
		if ( ! $attributes )
			return false;

		foreach ( $attributes as $attribute ) {
			if ( $attribute['variation'] )
				return true;
		}

		return false;
	}
} new jigoshop_prduct_meta_variable();


/*********************************************************************************
								LEGACY FUNCTIONS
**********************************************************************************
/**
 * Product Options
 * 
 * Product Options for the variable product type
 *
 * @since 		1.0
 */
/*
function variable_product_type_options() {
	global $post;
	
	$attributes = maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) );
	if (!isset($attributes)) $attributes = array();
	?>
	<div id="variable_product_options" class="panel">
		
		<div class="jigoshop_configurations">
			<?php
			$args = array(
				'post_type'	=> 'product_variation',
				'post_status' => array('private', 'publish'),
				'numberposts' => -1,
				'orderby' => 'id',
				'order' => 'asc',
				'post_parent' => $post->ID
			);
			$variations = get_posts($args);
			$loop = 0;
			if ($variations) foreach ($variations as $variation) : 
			
				$variation_data = get_post_custom( $variation->ID );
				$image = '';
				if (isset($variation_data['_thumbnail_id'][0])) :
					$image = wp_get_attachment_url( $variation_data['_thumbnail_id'][0] );
				endif;
				
				if (!$image) $image = jigoshop::plugin_url().'/assets/images/placeholder.png';
				?>
				<div class="jigoshop_configuration">
					<p>
						<button type="button" class="remove_variation button" rel="<?php echo $variation->ID; ?>"><?php _e('Remove', 'jigoshop'); ?></button>
						<strong>#<?php echo $variation->ID; ?> &mdash; <?php _e('Variation:', 'jigoshop'); ?></strong>
						<?php
							foreach ($attributes as $attribute) :
								// If not variable attribute then skip
								if ( ! $attribute['variation'] ) continue;

								// Get current value for variation (if set)
								$selected = get_post_meta( $variation->ID, 'tax_' . sanitize_title($attribute['name']), true );

								echo '<select name="tax_' . sanitize_title($attribute['name']) . '['.$loop.']"><option value="">'.__('Any ', 'jigoshop').$attribute['name'].' &hellip;</option>';

								if ( $attribute['is_taxonomy']) {
									
									$product_terms = wp_get_post_terms( $post->ID, 'pa_'.sanitize_title($attribute['name']));
									foreach( $product_terms as $term ) {
										echo '<option value="'.$term->slug.'" '.selected($selected, $term->slug, false).'>'.$term->name.'</option>';
									}
								}
								else {
									$options = explode(', ', $attribute['value']);
									foreach( $options as $option ) {
										$option = trim($option);
										echo '<option '.selected($selected, $option, false).' value="'.$option.'">'.$option.'</option>';
									}
								}

								echo '</select>';
								
								/*$options = $attribute['value'];
								$value = get_post_meta( $variation->ID, 'tax_' . sanitize_title($attribute['name']), true );
								
								$custom_attribute = false;
								if ( ! is_array( $options )) :
									$options = explode( ',', $options );
									$custom_attribute = true;
								endif;

							
								echo '<select name="tax_' . sanitize_title($attribute['name']) . '['.$loop.']"><option value="">'.__('Any ', 'jigoshop').$attribute['name'].' &hellip;</option>';
								
								foreach ( $options as $option ) :
									if ( $custom_attribute ) :
										$prettyname = $option;
									else :
										$prettyname = get_term_by( 'slug', $option, 'pa_'.sanitize_title( $attribute['name'] ))->name;
									endif;
									$option = sanitize_title( $option ); /* custom attributes need sanitizing */
								/*	$output = '<option ';
									$output .= selected( $value, $option );
									$output .= ' value="'.$option.'">'.$prettyname.'</option>';
									echo $output;
								endforeach;
								
								echo '</select>';
	
							endforeach;
						?>
						<input type="hidden" name="variable_post_id[<?php echo $loop; ?>]" value="<?php echo $variation->ID; ?>" />
					</p>
					<table cellpadding="0" cellspacing="0" class="jigoshop_variable_attributes">
						<tbody>	
							<tr>
								<td class="upload_image"><img src="<?php echo $image ?>" width="60px" height="60px" /><input type="hidden" name="upload_image_id[<?php echo $loop; ?>]" class="upload_image_id" value="<?php if (isset($variation_data['_thumbnail_id'][0])) echo $variation_data['_thumbnail_id'][0]; ?>" /><input type="button" rel="<?php echo $variation->ID; ?>" class="upload_image_button button" value="<?php _e('Product Image', 'jigoshop'); ?>" /></td>
								<td><label><?php _e('SKU:', 'jigoshop'); ?></label><input type="text" size="5" name="variable_sku[<?php echo $loop; ?>]" value="<?php if (isset($variation_data['SKU'][0])) echo $variation_data['SKU'][0]; ?>" /></td>
								<td><label><?php _e('Weight', 'jigoshop').' ('.get_option('jigoshop_weight_unit').'):'; ?></label><input type="text" size="5" name="variable_weight[<?php echo $loop; ?>]" value="<?php if (isset($variation_data['weight'][0])) echo $variation_data['weight'][0]; ?>" /></td>
								<td><label><?php _e('Stock Qty:', 'jigoshop'); ?></label><input type="text" size="5" name="variable_stock[<?php echo $loop; ?>]" value="<?php if (isset($variation_data['stock'][0])) echo $variation_data['stock'][0]; ?>" /></td>
								<td><label><?php _e('Price:', 'jigoshop'); ?></label><input type="text" size="5" name="variable_price[<?php echo $loop; ?>]" placeholder="<?php _e('e.g. 29.99', 'jigoshop'); ?>" value="<?php if (isset($variation_data['price'][0])) echo $variation_data['price'][0]; ?>" /></td>
								<td><label><?php _e('Sale Price:', 'jigoshop'); ?></label><input type="text" size="5" name="variable_sale_price[<?php echo $loop; ?>]" placeholder="<?php _e('e.g. 29.99', 'jigoshop'); ?>" value="<?php if (isset($variation_data['sale_price'][0])) echo $variation_data['sale_price'][0]; ?>" /></td>
								<td><label><?php _e('Enabled', 'jigoshop'); ?></label><input type="checkbox" class="checkbox" name="variable_enabled[<?php echo $loop; ?>]" <?php checked($variation->post_status, 'publish'); ?> /></td>
							</tr>		
						</tbody>
					</table>
				</div>
			<?php $loop++; endforeach; ?>
		</div>
		<p class="description"><?php _e('Add (optional) pricing/inventory for product variations.<br/>You <b>must</b> save your product attributes in the "Product Data" panel <b>first</b> & <b>mark them for variation</b> to make them available for selection.</strong>', 'jigoshop'); ?></p>

		<button type="button" class="button button-primary add_configuration"><?php _e('Add Configuration', 'jigoshop'); ?></button>
		
		<div class="clear"></div>
	</div>
	<?php
}
//add_action('jigoshop_product_type_options_box', 'variable_product_type_options');

/**
 * Process meta
 * 
 * Processes this product types options when a post is saved
 *
 * @since 		1.0
 *
 * @param 		array $data The $data being saved
 * @param 		int $post_id The post id of the post being saved
 */
/*
function process_product_meta_variable( $post_id ) {

	//var_dump($_POST);
	//exit();

	if ( ! isset( $_POST['variable_sku'] ) )
		return false;
	

    $variable_post_id = $_POST['variable_post_id'];
    $variable_sku = $_POST['variable_sku'];
    $variable_weight = $_POST['variable_weight'];
    $variable_stock = $_POST['variable_stock'];
    $variable_price = $_POST['variable_price'];
    $variable_sale_price = $_POST['variable_sale_price'];
    $upload_image_id = $_POST['upload_image_id'];
    if (isset($_POST['variable_enabled'])) {
        $variable_enabled = $_POST['variable_enabled'];
    }

    $errors = array();
    $attributes = maybe_unserialize(get_post_meta($post_id, 'product_attributes', true));
    if (empty($attributes)) {
        $attributes = array();
    }

    error_log(count($variable_sku));
    
    $attributes_values = array();
    for ($i = 0; $i < count($variable_sku); $i++) {

        $variation_id = (int) $variable_post_id[$i];

        // Enabled or disabled
        if (isset($variable_enabled[$i])) {
            $post_status = 'publish';
        } else {
            $post_status = 'private';
        }

        // Generate a useful post title
        $title = array();
        // Clean up attributes values
        $clean_attributes = array();

        foreach ($attributes as $attribute) {
            if ($attribute['variation']) {
                $value = '';
                $attribute_field = 'tax_' . sanitize_title($attribute['name']);

                if (isset($_POST[$attribute_field][$i])) {
                    $value = trim($_POST[$attribute_field][$i]);

                    if (!empty($value)) {
                        $title[] = ucfirst($attribute['name']) . ': ' . $value;
                    }
                }
                
                $clean_attributes[$attribute['name']] = $value;
            }
        }

        if ($post_status == 'publish') {
            //check if attributes for this variation are not already covered by other variation
            foreach ($attributes_values as $variation_attributes) {
                $duplicate = true;

                foreach ($variation_attributes as $attribute_name => $attribute_value) {
                    $attribute_value2 = $clean_attributes[$attribute_name];

                    if (!empty($attribute_value) && !empty($attribute_value2) && $attribute_value != $attribute_value2) {
                        $duplicate = false;
                        break;
                    }
                }

                //this variation was already covered
                if ($duplicate) {
                    //disable variation
                    $post_status = 'private';
                    //set error message
                    $errors[] = sprintf(__('Variation #%s was disabled as it is already covered by another variation.', 'jigoshop'), $variation_id);
                    break;
                }
            }
        
            $attributes_values[] = $clean_attributes;
        }

        $sku_string = '#' . $variation_id;
        if ($variable_sku[$i]) {
            $sku_string .= ' SKU: ' . $variable_sku[$i];
        }

        $post_title = '#' . $post_id . ' ' . __('Variation') . ' (' . $sku_string . ') - ' . implode(', ', $title);

        // Update or Add post
        if (!$variation_id) { //create variation
            $variation_id = wp_insert_post(array(
                'post_title' => $post_title,
                'post_content' => '',
                'post_status' => $post_status,
                'post_author' => get_current_user_id(),
                'post_parent' => $post_id,
                'post_type' => 'product_variation'
                ));
        } else { //update variation
            global $wpdb;

            $wpdb->update($wpdb->posts, array(
                'post_status' => $post_status,
                'post_title' => $post_title), array('ID' => $variation_id));
        }

        // Update post meta
        update_post_meta($variation_id, 'SKU', $variable_sku[$i]);
        update_post_meta($variation_id, 'price', $variable_price[$i]);
        update_post_meta($variation_id, 'sale_price', $variable_sale_price[$i]);
        update_post_meta($variation_id, 'weight', $variable_weight[$i]);
        update_post_meta($variation_id, 'stock', $variable_stock[$i]);
        update_post_meta($variation_id, '_thumbnail_id', $upload_image_id[$i]);
        
        // Update taxonomies (save attributes)
        foreach($clean_attributes as $attribute => $value) {
            update_post_meta($variation_id, 'tax_' . sanitize_title($attribute), $value);
        }
    }
    return $errors;
}
//add_action('jigoshop_process_product_meta_variable', 'process_product_meta_variable', 1, 2);
*/