<?php
/**
 * Downloadable Product Type
 * 
 * Functions specific to downloadable products (for the write panels)
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
 * Product Options for the downloadable product type
 *
 * @since 		1.0
 */
function downloadable_product_type_options() {
	global $post;
	?>
	<div id="downloadable_product_options" class="panel jigoshop_options_panel">
		<?php

			// File URL
			$file_path = get_post_meta($post->ID, 'file_path', true);
			$field = array( 'id' => 'file_path', 'label' => __('File path', 'jigoshop') );
			echo '<p class="form-field">
				<label for="'.$field['id'].'">'.$field['label'].':</label>
				<span style="float:left">'.ABSPATH.'</span><input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('path to file on your server', 'jigoshop').'" /></p>';
				
			// Download Limit
			$download_limit = get_post_meta($post->ID, 'download_limit', true);
			$field = array( 'id' => 'download_limit', 'label' => __('Download Limit', 'jigoshop') );
			echo '<p class="form-field">
				<label for="'.$field['id'].'">'.$field['label'].':</label>
				<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$download_limit.'" /> <span class="description">' . __('Leave blank for unlimited re-downloads.', 'jigoshop') . '</span></p>';
			do_action( 'additional_downloadable_product_type_options' )
		?>
	</div>
	<?php
}
add_action('jigoshop_product_type_options_box', 'downloadable_product_type_options');

/**
 * Product Type selector
 * 
 * Adds this product type to the product type selector in the product options meta box
 *
 * @since 		1.0
 *
 * @param 		string $product_type Passed the current product type so that if it keeps its selected state
 */
function downloadable_product_type_selector( $product_type ) {
	
	echo '<option value="downloadable" '; if ($product_type=='downloadable') echo 'selected="selected"'; echo '>'.__('Downloadable','jigoshop').'</option>';

}
add_action('product_type_selector', 'downloadable_product_type_selector');

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
function filter_product_meta_downloadable( $data, $post_id ) {
	
	if (isset($_POST['file_path']) && $_POST['file_path']) update_post_meta( $post_id, 'file_path', $_POST['file_path'] );
	if (isset($_POST['download_limit'])) update_post_meta( $post_id, 'download_limit', $_POST['download_limit'] );
	
	return $data;

}
add_filter('filter_product_meta_downloadable', 'filter_product_meta_downloadable', 1, 2);
