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

class jigoshop_product_meta_variable extends jigoshop_product_meta
{
	public function __construct() {
		add_action( 'product_type_selector', 				array(&$this, 'register') );
		add_action( 'jigoshop_process_product_meta_variable',	array(&$this, 'save'), 1 );
		add_action( 'jigoshop_product_type_options_box',		array(&$this, 'display') );
		add_action( 'admin_enqueue_scripts', 				array(&$this, 'admin_enqueue_scripts') );

		add_action( 'wp_ajax_jigoshop_remove_variation',		array(&$this, 'remove') );
	}

	/**
	 * Registers scripts for use in the admin
	 * Also localizes variables for use in the javascript, essential for variation addition
	 *
	 * @return		void
	 */
	public function admin_enqueue_scripts() {
		global $post;

		wp_enqueue_script('jigoshop-variable-js', jigoshop::plugin_url() . '/assets/js/variable.js', array('jquery'), true);

		// Shouldn't we namespace? -Rob
		wp_localize_script( 'jigoshop-variable-js', 'varmeta', array(
			'plugin_url'		=> jigoshop::plugin_url(),
			'ajax_url'		=> admin_url('admin-ajax.php'),
			'actions'		=> array(
				'remove'		=> array(
					'action'		=> 'jigoshop_remove_variation',
					'nonce'			=> wp_create_nonce("delete-variation"),
					'confirm'		=> __('Are you sure you want to remove this variation?', 'jigoshop'),
				),
				'create'		=> array(
					'action'		=> 'jigoshop_create_variation',
					'panel'			=> $this->generate_panel(maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) ))
				)
			)
		));
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
	 *
	 * @return		void
	 */
	public function remove() {
		check_ajax_referer( 'delete-variation', 'security' );

		$ID = intval( $_POST['variation_id'] );
		wp_set_object_terms( $ID, null, 'product_type'); // Remove object terms
		wp_delete_post( $ID );

		exit;
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
			if ( strpos($ID, '_new') ) {
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

			// Set the product type
			// NOTE: I think this will work, not sure -Rob
			wp_set_object_terms( $ID, sanitize_title($meta['product-type']), 'product_type');

			// Set variation meta data
			update_post_meta( $ID, 'sku',			$meta['sku'] );
			update_post_meta( $ID, 'regular_price',	$meta['regular_price'] );
			update_post_meta( $ID, 'sale_price',		$meta['sale_price'] );

			update_post_meta( $ID, 'weight',		$meta['weight'] );
			update_post_meta( $ID, 'length',			$meta['length'] );
			update_post_meta( $ID, 'height',		$meta['height'] );
			update_post_meta( $ID, 'width',			$meta['width'] );

			update_post_meta( $ID, 'stock',			$meta['stock'] );
			update_post_meta( $ID, '_thumbnail_id',	$meta['_thumbnail_id'] );

			// Downloadable Only
			if( $meta['product-type'] == 'downloadable' ) {
				update_post_meta( $ID, 'file_path',			$meta['file_path']);
				update_post_meta( $ID, 'download_limit',		$meta['download_limit']);
			}

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

		?>

		<div id='variable_product_options' class='panel'>
			<?php if ( $this->has_variable_attributes($attributes) ): ?>
			<div class="controls">
				<select name="variation_actions">
					<option value=""><?php _e('Bulk Actions', 'jigoshop') ?></option>
					<option value=""><?php _e('Clear All', 'jigoshop') ?></option>
					<option value=""><?php _e('Set all Prices', 'jigoshop') ?></option>
					<option value=""><?php _e('Set all Sale Prices', 'jigoshop') ?></option>
					<option value=""><?php _e('Set all Stock', 'jigoshop') ?></option>
					<option value=""><?php _e('Set all Weight', 'jigoshop') ?></option>
					<option value=""><?php _e('Set all Width', 'jigoshop') ?></option>
					<option value=""><?php _e('Set all Length', 'jigoshop') ?></option>
					<option value=""><?php _e('Set all Height', 'jigoshop') ?></option>
				</select>

				<input id="do_actions" type="submit" class="button-secondary" value="Apply">

				<button type='button' class='button button-primary add_variation'<?php disabled($this->has_variable_attributes($attributes), false) ?>><?php _e('Add Variation', 'jigoshop') ?></button>
			</div>
			<div class='clear'>&nbsp;</div>
			<div class='jigoshop_variations'>
			<?php
				if( $variations ) {
					foreach( $variations as $variation ) {
						echo $this->generate_panel($attributes, $variation);
					}
				}
			?>
			</div>
			<?php else: ?>
			Doh! Seems like we need some variable attributes first
			<?php endif; ?>
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

		// Attribute Variation Selector
		foreach ( $attributes as $attr ) {

			// If not variable attribute then skip
			if ( ! $attr['variation'] )
				continue;

			// Get current value for variation (if set)
			if ( ! is_ajax() ) {
				$selected = get_post_meta( $variation->ID, 'tax_' . sanitize_title($attr['name']), true );
			}

			// Open the select & set a default value
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

			// Close the select
			$html .= '</select>';
		}

		return $html;
	}

	/**
	 * Checks all the product attributes for variation defined attributes
	 *
	 * @param		mixed		Attributes
	 * @return		bool
	 */
	private function has_variable_attributes( $attributes ) {
		if ( ! $attributes )
			return false;

		foreach ( $attributes as $attribute ) {
			if ( isset($attribute['variation']) && $attribute['variation'] )
				return true;
		}

		return false;
	}

	/**
	 * Generates a variation panel
	 *
	 * @param		array		attributes
	 * @param		object		variation
	 * @return		HTML
	 */
	private function generate_panel($attributes, $variation = null) {

		if ( ! $this->has_variable_attributes($attributes) )
			return false;

		// Set the default image as the placeholder
		$image = jigoshop::plugin_url().'/assets/images/placeholder.png';

		if ( ! $variation ) {

			// Create a blank variation object with a unique id
			$variation = new stdClass;
			$variation->ID = '__ID__';
			$variation->post_status = 'publish';
		}
		else {

			// Get the variation meta
			$meta = get_post_custom( $variation->ID );

			// If variation has a thumbnail display that
			if ( $image_id = $meta['_thumbnail_id'][0] )
				$image = wp_get_attachment_url( $image_id );
		}

		// Start buffering the output
		ob_start();
		?>
		<div class="jigoshop_variation">
			<p>
				<button type="button" class="remove_variation button" rel="<?php echo $variation->ID; ?>"><?php _e('Remove', 'jigoshop'); ?></button>
				<?php echo $this->attribute_selector($attributes, $variation); ?>
			</p>

			<table cellpadding="0" cellspacing="0" class="jigoshop_variable_attributes">
				<tbody>
					<tr>
						<td class="upload_image" rowspan="2">
							<a href="#" class="upload_image_button <?php if ($image_id) echo 'remove'; ?>" rel="<?php echo $variation->ID; ?>">
								<img src="<?php echo $image ?>" width="75px" />
								<input type="hidden" name="<?php echo $this->field_name('_thumbnail_id', $variation) ?>" class="upload_image_id" value="<?php echo $image_id; ?>" />
								<!-- TODO: APPEND THIS IN JS <span class="overlay"></span> -->
							</a>
						</td>

						<td>
							<?php
								$terms = wp_get_object_terms( $variation->ID, 'product_type' );
								$product_type = ($terms) ? current($terms)->slug : 'simple';
							?>
							<label><?php _e('Type', 'jigoshop') ?></label>
							<select class="product_type" name="<?php echo $this->field_name('product-type', $variation) ?>">
								<option value="simple" <?php selected('simple', $product_type) ?>>Simple</option>
								<option value="downloadable" <?php selected('downloadable', $product_type) ?>>Downloadable</option>
								<option value="virtual" <?php selected('virtual', $product_type) ?>>Virtual</option>
							</select>
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
					<tr class="simple options" style="display: table-row;">
						<td>
							<label><?php _e('Weight', 'jigoshop') ?>
								<input type="text" size="5" name="<?php echo $this->field_name('weight', $variation) ?>" value="<?php echo isset($meta['weight'][0]) ? $meta['weight'][0] : null; ?>" />
							</label>
						</td>
						<td colspan="2">
							<label><?php _e('Dimensions (lxwxh)', 'jigoshop') ?></label>
							<input type="text" name="<?php echo $this->field_name('length', $variation) ?>" style="width: 32%" size="6" placeholder="Length" value="<?php echo isset($meta['length'][0]) ? $meta['length'][0] : null; ?>" />
							<input type="text" name="<?php echo $this->field_name('width', $variation) ?>" style="width: 32%" size="6" placeholder="Width" value="<?php echo isset($meta['width'][0]) ? $meta['width'][0] : null; ?>" />
							<input type="text" name="<?php echo $this->field_name('height', $variation) ?>" style="width: 32%" size="6" placeholder="Height" value="<?php echo isset($meta['height'][0]) ? $meta['height'][0] : null; ?>" />
							<td colspan="3">
								&nbsp;
							</td>
						</td>
					</tr>
					<tr class="downloadable options" style="display:none;">
						<td colspan="2">
							<label><?php _e('File Location', 'jigoshop') ?>
								<input type="text" name="<?php echo $this->field_name('file_path', $variation) ?>" value="<?php echo isset($meta['file_path'][0]) ? $meta['file_path'][0] : null; ?>" />
							</label>
						</td>
						<td>
							<label>&nbsp;
								<input type="submit" class="button-secondary" value="Upload">
							</label>
						</td>
						<td colspan="4">
								&nbsp;
						</td>
					</tr>
					<tr class="virtual options" style="display: none;">
						<td colspan="7">
							&nbsp;
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php
	// Flush & return the buffer
	return ob_get_clean();
	}
} new jigoshop_product_meta_variable();