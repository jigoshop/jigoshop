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
 * Product Options
 * 
 * Product Options for the variable product type
 *
 * @since 		1.0
 */
function variable_product_type_options($post_id=null, $variation_id=null) {
	global $post;

	if ($post_id == null){
		$post_id = $post->ID;
	}

	// Get the parent post attributes
	$attributes = get_post_meta($post_id, 'product_attributes', true);

	$_has_variation_attributes = false;
	foreach((array) $attributes as $attribute){
		if (boolval($attribute['variation'])){
			$_has_variation_attributes = true;
			break;
		}
	}

	$img_upload_src = get_upload_iframe_src('image');

	// Only render the wrapper div if it's not a single variation request
	if ($variation_id === null): ?>
		<div id="variable_product_options" class="panel"><div class="jigoshop_configurations">
	<?php endif;

			// All variations for this product
			$args = array(
				'post_type'	=> 'product_variation',
				'post_status' => array('private', 'publish'),
				'numberposts' => -1,
				'orderby' => 'id',
				'order' => 'asc',
				'post_parent' => $post_id
			);

			// Limit to a specific variation
			if ($variation_id !== null){
				$args['p'] = $variation_id;
			}

			$variations = get_posts($args);

			if ($variations) foreach ($variations as $variation) : 
			
				$variation_data = get_post_custom( $variation->ID );
				if (isset($variation_data['_thumbnail_id'])){
					$image = jigoshop_custom_image_src($variation_data['_thumbnail_id'][0], 60, 60);
				} else {
					$image = jigoshop_custom_image_src(null, 60, 60);
				}

				?>
				<div class="jigoshop_configuration">
					<p>
						<button type="button" class="remove_variation button" rel="<?php echo $variation->ID; ?>"><?php _e('Remove', 'jigoshop'); ?></button>
						<strong>#<?php echo $variation->ID; ?> &mdash; <?php _e('Variation:', 'jigoshop'); ?></strong>
						<?php
							foreach ((array) $attributes as $attribute) :
								
								if ( !boolval($attribute['variation']) ) {
									continue;
								}
								
								$options = $attribute['value'];
								if (!is_array($options)) {
									$options = explode(',', $options);
								}

								$value = get_post_meta( $variation->ID, 'tax_' . sanitize_title($attribute['name']), true );

								printf('<select name="%s[]"><option value="">%s</option>'
									,'tax_' . sanitize_title($attribute['name'])
									, __('Any ', 'jigoshop').$attribute['name'].'&hellip;');

								foreach($options as $option) :
									$option = trim($option);
									printf('<option %s value="%s">%s</option>'
										, selected($value, $option, false)
										, $option
										, ucfirst($option));
								endforeach;	
									
								echo '</select>';
	
							endforeach;

						$variation_upload_src = str_replace('post_id='.$post_id, 'post_id='.$variation->ID, $img_upload_src)
						?>
						<input type="hidden" name="variable_post_id[]" value="<?php echo $variation->ID; ?>" />
					</p>
					<table cellpadding="0" cellspacing="0" class="jigoshop_variable_attributes">
						<tbody>	
							<tr>
								<td class="upload_image">
									<a rel="<?php echo $variation->ID; ?>" title="<?php _e('Click to choose image','jigoshop') ?>" class="upload_image_button media-preview" href="<?php echo $variation_upload_src; ?>">
										<img src="<?php echo $image ?>" width="60px" height="60px" alt="Product Variation Image" />
									</a>
									<input type="hidden" name="upload_image_id[]" class="upload_image_id" value="<?php if (isset($variation_data['_thumbnail_id'][0])) echo $variation_data['_thumbnail_id'][0]; ?>" />
								</td>
								<td>
									<label><?php _e('SKU:', 'jigoshop'); ?></label>
									<input type="text" size="5" name="variable_sku[]" value="<?php if (isset($variation_data['SKU'][0])) echo $variation_data['SKU'][0]; ?>" />
								</td>
								<td>
									<label><?php _e('Weight', 'jigoshop').' ('.get_option('jigoshop_weight_unit').'):'; ?></label><input type="text" size="5" name="variable_weight[]" value="<?php if (isset($variation_data['weight'][0])) echo $variation_data['weight'][0]; ?>" /></td>
								<td>
									<label><?php _e('Stock Qty:', 'jigoshop'); ?></label>
									<input type="text" size="5" name="variable_stock[]" value="<?php if (isset($variation_data['stock'][0])) echo $variation_data['stock'][0]; ?>" />
								</td>
								<td>
									<label><?php _e('Price:', 'jigoshop'); ?></label>
									<input type="text" size="5" name="variable_price[]" placeholder="<?php _e('e.g. 29.99', 'jigoshop'); ?>" value="<?php if (isset($variation_data['price'][0])) echo $variation_data['price'][0]; ?>" />
								</td>
								<td>
									<label><?php _e('Sale Price:', 'jigoshop'); ?></label>
									<input type="text" size="5" name="variable_sale_price[]" placeholder="<?php _e('e.g. 29.99', 'jigoshop'); ?>" value="<?php if (isset($variation_data['sale_price'][0])) echo $variation_data['sale_price'][0]; ?>" />
								</td>
								<td>
									<label><?php _e('Enabled', 'jigoshop'); ?></label>
									<input type="checkbox" class="checkbox" name="variable_enabled[]" <?php checked($variation->post_status, 'publish'); ?> />
								</td>
							</tr>		
						</tbody>
					</table>
				</div>
			<?php endforeach; ?>
	<?php if ($variation_id === null): // Only render the buttons not a single variation request ?>
		</div><!-- .jigoshop_configurations -->
		<p class="description"><?php _e('Add (optional) pricing/inventory for product variations. You must save your product attributes in the "Product Data" panel to make them available for selection.', 'jigoshop'); ?></p>
		<?php if( $_has_variation_attributes ): ?>
		<button type="button" class="button button-primary add_configuration"><?php _e('Add Configuration', 'jigoshop'); ?></button>
		<?php else: ?>
		<button type="button" class="button button-primary add_configuration" disabled="disabled"><?php _e('Add Configuration', 'jigoshop'); ?></button>
		<?php endif; ?>
		<div class="clear"></div>
	</div>
	<?php endif;
}
add_action('jigoshop_product_type_options_box', 'variable_product_type_options');

/**
 * Product Type Javascript
 * 
 * Javascript for the variable product type
 *
 * @todo this needs to be moved to some javascript file
 * @since 		1.0
 */
function variable_product_write_panel_js() {
	global $post; ?>
<script type="text/javascript">
(function($) {

	if (!$.jigoshop){
		$.jigoshop={};
	}

	$.extend($.jigoshop, {

		loadMediaPreview:function(preview, value) {
			var data = {
				action:'jigoshop_media_preview',
				url: value,
				width: 60,
				height: 60
			};

			// Extra data provided?
			if (arguments.length > 2) {
				$.extend(data, arguments[2]);
			}

			preview.load(ajaxurl, data);
		},

		sendToEditor : function(target, fn) {
			$.data(document, 'jigoshop_media_override', target);
			$(target).bind('send_to_editor.jigoshop', function(event, html){
				fn.call(this, event, html);
				$(target).unbind('send_to_editor.jigoshop');
				$.data(document, 'jigoshop_media_override', null);
			});
		}
	});

	var blockUiParams = {
		message: null,
		applyPlatformOpacityRules: false,
		overlayCSS: {
			background: '#fff url(<?php echo jigoshop::plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center',
			opacity: 0.6
		}
	};

	$('button.add_configuration').live('click', function(){
		$('.jigoshop_configurations').block(blockUiParams);

		var data = {
			action: 'jigoshop_add_variation',
			post_id: <?php echo $post->ID; ?>,
			security: '<?php echo wp_create_nonce("add-variation"); ?>'
		};

		$.post( ajaxurl, data, function(response) {
			if (response.length > 0){
				$('.jigoshop_configurations').append(response).unblock();
			}
		}, 'html');

		return false;
	});

	$('button.remove_variation').live('click', function(){
		var answer = confirm('<?php _e('Are you sure you want to remove this variation?', 'jigoshop'); ?>');
		if (answer){

			var el = jQuery(this).parent().parent();
			var variation = jQuery(this).attr('rel');

			if (variation > 0) {
				$(el).block(blockUiParams);

				var data = {
					action: 'jigoshop_remove_variation',
					variation_id: variation,
					security: '<?php echo wp_create_nonce("delete-variation"); ?>'
				};

				$.post( ajaxurl, data, function(response) {
					if (response && response.error){
						alert(response.error);
					} else {
						$(el).fadeOut('300', function(){ $(el).remove(); });
					}
				}, 'json');

			} else {
				$(el).fadeOut('300', function(){ $(el).remove(); });
			}

		}
		return false;
	});

	$('.upload_image_button').live('click', function(){
		var target = $(this);
		var previewContainer = $(this);

		$.jigoshop.sendToEditor(previewContainer, function(event, html){
			var jqel  = $(html);
			var link  = $(jqel).attr('href');
			var match = $(jqel).children('img:first-child').attr('class').match(/wp-image-([0-9]+)/);
			var wpid  = parseInt(match[1]);

			previewContainer.next().val(wpid);
			$.jigoshop.loadMediaPreview(previewContainer, wpid);
		});

		var href = $(this).attr('href');
		tb_show('Select Variation Image', $(this).attr('href'), null);
		return false;
	});

	// On document ready
	$(function(){

		// override the default send_to_editor function with our own
		window._send_to_editor = window.send_to_editor;
		window.send_to_editor = function(html) {
			var override = $($.data(document, 'jigoshop_media_override'));
			if (override.length > 0) {
				override.trigger('send_to_editor.jigoshop', [html]);
				tb_remove();
			} else {
				window._send_to_editor(html);
			}
		};
	});

})(jQuery);
</script><?php
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
	$result = wp_delete_post( $variation_id );
	if ($result === false){
		$error = array('error'=>__('There was an error while attempting to remove this variation, please try again','jigoshop'));
		echo json_encode($error);
	}
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

	variable_product_type_options($post_id, $variation_id);
	
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
	printf('<option %s value="variable">%s</option>', selected($product_type == 'variable', true, false), __('Variable','jigoshop'));
}
add_action('product_type_selector', 'variable_product_type_selector');

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
function process_product_meta_variable( $data, $post_id ) {
	
     if (!isset($_POST['variable_sku'])) {
        return;
    }

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
            if ($attribute['variation'] == 'yes') {
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
                    $errors[] = sprintf(__('Variation #%s was disabled as it is already covered by other variation.', 'jigoshop'), $variation_id);
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
add_action('process_product_meta_variable', 'process_product_meta_variable', 1, 2);