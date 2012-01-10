<?php
/**
 * External Product Type
 * 
 * Functions specific to external products (for the write panels)
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
 * Product Options
 * 
 * Product Options for the external product type
 *
 * @since 		1.0
 */
function external_product_type_options() {
	global $post;
	?>
	<div id="external_product_options" class="panel jigoshop_options_panel">
		<?php

			// product_url (External URL)
			$product_url = get_post_meta($post->ID, 'product_url', true);
			$field = array( 'id' => 'product_url', 'label' => __('External URL', 'jigoshop') );
			echo '<p class="form-field">
				<label for="'.$field['id'].'">'.$field['label'].':</label>
				<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$product_url.'" placeholder="'.__('An external URL to the product', 'jigoshop').'" /><span class="description">' . 'Eg, http://domain.com/iPod/' . '</span></p>';
			echo "</div>";
}
add_action('jigoshop_product_type_options_box', 'external_product_type_options');

/**
 * Product Type selector
 * 
 * Adds this product type to the product type selector in the product options meta box
 *
 * @since 		1.0
 *
 * @param 		string $product_type Passed the current product type so that if it keeps its selected state
 */
function external_product_type_selector( $product_type ) {
	
	echo '<option value="external" '; if ($product_type == 'external') echo 'selected="selected"'; echo '>'.__('External','jigoshop').'</option>';

}
add_action('product_type_selector', 'external_product_type_selector');

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
function filter_product_meta_external( $data, $post_id ) {

	if (isset($_POST['product_url']) && $_POST['product_url']) update_post_meta( $post_id, 'product_url', esc_attr($_POST['product_url']) );
	
	return $data;

}
add_filter('filter_product_meta_external', 'filter_product_meta_external', 1, 2);
