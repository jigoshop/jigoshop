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

class jigoshop_prduct_meta_variable extends jigoshop_product_meta
{
	public function __construct() {
		add_action( 'product_type_selector', 					array(&$this, 'register') );
		add_action( 'jigoshop_process_product_meta_variable',	array(&$this, 'save'), 1 );
		add_action( 'jigoshop_product_type_options_box',		array(&$this, 'display') );
		add_action( 'wp_ajax_jigoshop_remove_variation',		array(&$this, 'remove') );
		add_action( 'product_write_panel_js',					array(&$this, 'variable_write_panel_js') );
		add_action( 'wp_ajax_jigoshop_variable_generate_panel',	array(&$this, 'generate_panel') );
	}

	/**
	 * Product Type Javascript
	 * 
	 * Javascript for the variable product type
	 *
	 * @todo this needs to be moved to some javascript file
	 * @since 		1.0
	 */
	public function variable_write_panel_js() {
		global $post;
	
		$attributes = maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) );
		?>
		jQuery(function(){

			
			
			jQuery('button.add_configuration').live('click', function(){
			
				jQuery('.jigoshop_configurations').block({ message: null, overlayCSS: { background: '#fff url(<?php echo jigoshop::plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
				
				var data2 = {
					action: 'jigoshop_variable_generate_panel',
					post: <?php echo json_encode($post); ?>,
					attributes: <?php echo json_encode($attributes); ?>,
					security: '<?php echo wp_create_nonce("add-variation"); ?>'
				};

				jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data2, function(response) {
					jQuery('.jigoshop_configurations').append( jQuery(response) );

					jQuery('.jigoshop_configurations').unblock();
				});

				/*var data = {
					action: 'jigoshop_add_variation',
					post_id: <?php echo $post->ID; ?>,
					security: '<?php echo wp_create_nonce("add-variation"); ?>'
				};

				jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
					
					var variation_id = parseInt(response);
					
					var loop = jQuery('.jigoshop_configuration').size();


					
					jQuery('.jigoshop_configurations').append('');
					
					jQuery('.jigoshop_configurations').unblock();

				});*/

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

	/**
	 * Echos a variable type option for the product type selector
	 *
	 * @return		void
	 */
	public function register( $type ) {
		echo '<option value="variable" ' . selected($type, 'variable', false) . '>' . __('Variable', 'jigoshop') . '</option>';
	}

	/**
	 * Removes a product variation via XHR
	 * @todo		check this
	 *
	 * @return		void
	 */
	public function remove() {
		check_ajax_referer( 'delete-variation', 'security' );
		$variation_id = intval( $_POST['variation_id'] );
		wp_delete_post( $variation_id );
		die();
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

			// Update post data or Add post if new
			if ( ! is_int($ID) ) {
				$ID = wp_insert_post( array(
					'post_title'		=> "#{$parent_id}: Child Variation",
					'post_status'	=> isset($meta['enabled']) ? 'publish' : 'draft',
					'post_parent'	=> $parent_id,
					'post_type'		=> 'product_variation'
				));
			}
			else {
				$wpdb->update( $wpdb->posts, array(
					'post_title'		=> "#{$parent_id}: Child Variation",
					'post_status'	=> isset($meta['enabled']) ? 'publish' : 'draft'
				), array( 'ID' => $ID ) );
			}

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
		//exit();
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
			<div class="jigoshop_configurations">
				
				<?php foreach( $variations as $variation ): ?>

					<?php echo $this->generate_panel($attributes, $variation); ?>

				<?php endforeach; ?>

			</div>
			<button type="button" class="button button-primary add_configuration <?php disabled($this->has_variable_attributes($attributes), false); ?>"><?php _e('Add Variation', 'jigoshop'); ?></button>
			<div class="clear">&nbsp;</div>
		</div>
		<?php
	}

	public function generate_panel($attributes, $variation = null) { ?>
		<?php

			// Check the ajax request is genuine & obtain the attributes
			if ( is_ajax() ) {
				check_ajax_referer( 'add-variation', 'security' );
				$attributes = $_POST['attributes'];
			}

			// GET THE DATA
			// Get the variation meta
			if ( $variation ) {
				$meta = get_post_custom( $variation->ID );
			} else {
				$variation = new stdClass;
				$variation->ID = uniqid();
			}

			// Get the image url if we have one
			$image = jigoshop::plugin_url().'/assets/images/placeholder.png';
			if ( $meta['_thumbnail_id'][0] ) {
				$image = wp_get_attachment_url( $meta['_thumbnail_id'][0] );
			}
		?>
		<div class="jigoshop_configuration">
			<p>
				<button type="button" class="remove_variation button" rel="<?php echo $variation->ID; ?>"><?php _e('Remove', 'jigoshop'); ?></button>
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
							<label><?php _e('SKU', 'jigoshop'); ?>
								<input type="text" size="5" name="<?php echo $this->field_name('sku', $variation) ?>" value="<?php echo isset($meta['sku'][0]) ? $meta['sku'][0] : null; ?>" />
							</label>
						</td>

						<td>
							<label><?php _e('Stock Qty', 'jigoshop'); ?>
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
							<label><?php _e('Price', 'jigoshop'); ?>
								<input type="text" size="5" name="<?php echo $this->field_name('regular_price', $variation) ?>" value="<?php echo isset($meta['regular_price'][0]) ? $meta['regular_price'][0] : null; ?>" />
							</label>
						</td>

						<td>
							<label><?php _e('Sale Price', 'jigoshop'); ?>
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
	<?php
	}

	/**
	 * Returns a specially formatted field name for variations
	 *
	 * @param		string		Field Name
	 * @param		object		Variation Post Object
	 * @return		string
	 */
	private function field_name( $name, $variation = null ) {
		return "variations[{$variation->ID}][{$name}]";
	}

	/**
	 * Returns all the possible variable attributes in select form
	 *
	 * @param		array		Attributes array
	 * @param		object		Variation Post Object
	 * @return		HTML
	 */
	private function attribute_selector( $attributes, $variation = null ) {
		global $post;

		$html = null;

		// Post object doesn't exist if we're ajaxing
		if ( is_ajax() )
			$post = (object) $_POST['post'];

		// Attribute Variation Selector
		foreach ( $attributes as $attr ) {

			// If not variable attribute then skip
			if ( ! $attr['variation'] )
				continue;

			// Get current value for variation (if set)
			if ( ! is_ajax() ) {
				$selected = get_post_meta( $variation->ID, 'tax_' . sanitize_title($attr['name']), true );
			}

			$html .= '<select name="' . $this->field_name('tax_' . sanitize_title($attr['name']), $variation) . '" >
				<option value="">Any ' . sanitize_title($attr['name']) . '</option>';

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