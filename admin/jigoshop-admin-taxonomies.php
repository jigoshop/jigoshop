<?php
/**
 * Jigoshop Admin Taxonomies
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 * Category thumbnails
 */
add_action('product_cat_add_form_fields' , 'jigoshop_add_category_thumbnail_field');
add_action('product_cat_edit_form_fields', 'jigoshop_edit_category_thumbnail_field', 10,2);

function jigoshop_add_category_thumbnail_field() {
	$image = jigoshop::assets_url().'/assets/images/placeholder.png';
	?>
	<div class="form-field">
		<label><?php _e('Thumbnail', 'jigoshop'); ?></label>
		<div id="product_cat_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
		<div style="line-height:60px;">
			<input type="hidden" id="product_cat_thumbnail_id" name="product_cat_thumbnail_id" />
			<button type="submit" class="upload_image_button button"><?php _e('Upload/Add image', 'jigoshop'); ?></button>
			<button type="submit" class="remove_image_button button"><?php _e('Remove image', 'jigoshop'); ?></button>
		</div>
		<script type="text/javascript">
			var wpActiveEditor;

			jQuery('.upload_image_button').click(function() {

			    var send_attachment_bkp = wp.media.editor.send.attachment;

			    wp.media.editor.send.attachment = function(props, attachment) {
					jQuery('#product_cat_thumbnail_id').val(attachment.id);
					jQuery('#product_cat_thumbnail img').attr('src', attachment.url);

			        wp.media.editor.send.attachment = send_attachment_bkp;
			    }

			    wp.media.editor.open();

			    return false;
			});

			jQuery('.remove_image_button').live('click', function(){
				jQuery('#product_cat_thumbnail img').attr('src', '<?php echo $image; ?>');
				jQuery('#product_cat_thumbnail_id').val('');
				return false;
			});

		</script>
		<div class="clear"></div>
	</div>
	<?php
}

function jigoshop_edit_category_thumbnail_field( $term, $taxonomy ) {
	$image = jigoshop_product_cat_image($term->term_id);
	?>
	<tr class="form-field">
		<th scope="row" valign="top"><label><?php _e('Thumbnail', 'jigoshop'); ?></label></th>
		<td>
			<div id="product_cat_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image['image']; ?>" width="60px" height="60px" /></div>
			<div style="line-height:60px;">
				<input type="hidden" id="product_cat_thumbnail_id" name="product_cat_thumbnail_id" value="<?php echo $image['thumb_id']; ?>" />
				<button type="submit" class="upload_image_button button"><?php _e('Upload/Add image', 'jigoshop'); ?></button>
				<button type="submit" class="remove_image_button button"><?php _e('Remove image', 'jigoshop'); ?></button>
			</div>
			<script type="text/javascript">
				var wpActiveEditor;

				jQuery('.upload_image_button').click(function() {

				    var send_attachment_bkp = wp.media.editor.send.attachment;

				    wp.media.editor.send.attachment = function(props, attachment) {
						jQuery('#product_cat_thumbnail_id').val(attachment.id);
						jQuery('#product_cat_thumbnail img').attr('src', attachment.url);

				        wp.media.editor.send.attachment = send_attachment_bkp;
				    }

				    wp.media.editor.open();

				    return false;
				});

				jQuery('.remove_image_button').live('click', function(){
					jQuery('#product_cat_thumbnail img').attr('src', '<?php echo jigoshop::assets_url().'/assets/images/placeholder.png'; ?>');
					jQuery('#product_cat_thumbnail_id').val('');
					return false;
				});

			</script>
			<div class="clear"></div>
		</td>
	</tr>
	<?php
}

add_action('created_term', 'jigoshop_category_thumbnail_field_save', 10,3);
add_action('edit_term'   , 'jigoshop_category_thumbnail_field_save', 10,3);

function jigoshop_category_thumbnail_field_save( $term_id, $tt_id, $taxonomy ) {

	if (!isset($_POST['product_cat_thumbnail_id']))
		return false;

	update_metadata( 'jigoshop_term', $term_id, 'thumbnail_id', $_POST['product_cat_thumbnail_id'] );

}

/**
* Thumbnail column for categories
*/
add_filter("manage_edit-product_cat_columns", 'jigoshop_product_cat_columns');
add_filter("manage_product_cat_custom_column", 'jigoshop_product_cat_column', 10, 3);

function jigoshop_product_cat_columns( $columns ) {

	$new_columns = array(
		'cb'    => $columns['cb'],
		'thumb' => null
	);

	unset($columns['cb']);
	$columns = array_merge( $new_columns, $columns );

	return $columns;

}

function jigoshop_product_cat_column( $columns, $column, $id ) {

	if ($column != 'thumb')
		return false;

	$image = jigoshop_product_cat_image($id);
	$columns .= '<a class="row-title" href="'.get_edit_term_link( $id, 'product_cat', 'product' ).'">';
	$columns .= '<img src="'.$image['image'].'" alt="Thumbnail" class="wp-post-image" height="32" width="32" />';
	$columns .= '</a>';

	return $columns;

}
