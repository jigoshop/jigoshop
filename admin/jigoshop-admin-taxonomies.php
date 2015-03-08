<?php
/**
 * Jigoshop Admin Taxonomies
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * Category thumbnails and optional fields
 *
 * The list of optional fields can be extended with the "jigoshop_optional_product_category_fields" filter.
 * Only simple types are supported: text or number.
 * The filter needs to add an array element like:
 * array(
 *     'id'   => 'field_id',
 *     'name' => 'Label for the field',
 *     'desc' => 'Longer description under the field',
 *     'type' => 'text' or 'number'
 * );
 */
add_action('product_cat_add_form_fields', 'jigoshop_add_category_thumbnail_field');
add_action('product_cat_edit_form_fields', 'jigoshop_edit_category_thumbnail_field', 10, 2);

function jigoshop_add_category_thumbnail_field()
{
	foreach( apply_filters( 'jigoshop_optional_product_category_fields', array() ) as $field ) {
		?>
		<div class="form-field">
			<label><?php echo $field['name']; ?></label>
			<input id="product_cat_<?php echo $field['id']; ?>" type="text" size="40" value="" name="product_cat_<?php echo $field['id']; ?>">
			<p><?php echo $field['desc']; ?></p>
		</div>
		<?php
	}

	$image = JIGOSHOP_URL.'/assets/images/placeholder.png';
	?>
	<div class="form-field">
		<label><?php _e('Thumbnail', 'jigoshop'); ?></label>

		<div id="product_cat_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
		<div style="line-height:60px;">
			<input type="hidden" id="product_cat_thumbnail_id" name="product_cat_thumbnail_id" />
			<a id="add-image" href="#" class="button"
			   data-title="<?php esc_attr_e('Choose thumbnail image', 'jigoshop'); ?>"
			   data-button="<?php esc_attr_e('Set as thumbnail', 'jigoshop'); ?>"><?php _e('Change image', 'jigoshop'); ?>
			</a>
			<a id="remove-image" href="#" class="button" style="display: none;"><?php _e('Remove image', 'jigoshop'); ?></a>
		</div>
		<script type="text/javascript">
			jQuery(function($){
				$('#add-image').jigoshop_media({
					field: $('#product_cat_thumbnail_id'),
					thumbnail: $('#product_cat_thumbnail > img'),
					callback: function(){
						if($('#product_cat_thumbnail_id').val() != ''){
							$('#remove-image').show()
						}
					},
					library: {
						type: 'image'
					}
				});
				$('#remove-image').on('click', function(e){
					e.preventDefault();
					$('#product_cat_thumbnail_id').val('');
					$('#product_cat_thumbnail > img').attr('src', '<?php echo $image; ?>');
					$(this).hide();
				});
			});
		</script>
		<div class="clear"></div>
	</div>
<?php
}

function jigoshop_edit_category_thumbnail_field($term, $taxonomy)
{
	foreach( apply_filters( 'jigoshop_optional_product_category_fields', array() ) as $field ) {
		$value = get_metadata( 'jigoshop_term', $term->term_id, $field['id'], true );
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label><?php echo $field['name']; ?></label></th>
			<td>
				<input id="product_cat_<?php echo $field['id']; ?>" type="<?php echo $field['type']; ?>" size="40" value="<?php echo $value; ?>" name="product_cat_<?php echo $field['id']; ?>">
				<p class="description"><?php echo $field['desc']; ?></p>
			</td>
		</tr>
		<?php
	}

	$image = jigoshop_product_cat_image($term->term_id);
	?>
	<tr class="form-field">
		<th scope="row" valign="top"><label><?php _e('Thumbnail', 'jigoshop'); ?></label></th>
		<td>
			<div id="product_cat_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image['image']; ?>" width="60px" height="60px" /></div>
			<div style="line-height:60px;">
				<input type="hidden" id="product_cat_thumbnail_id" name="product_cat_thumbnail_id" value="<?php echo $image['thumb_id']; ?>" />
				<a id="add-image" href="#" class="button"
				   data-title="<?php esc_attr_e('Choose thumbnail image', 'jigoshop'); ?>"
				   data-button="<?php esc_attr_e('Set as thumbnail', 'jigoshop'); ?>"><?php _e('Change image', 'jigoshop'); ?>
				</a>
				<a id="remove-image" href="#" class="button" style="display: none;"><?php _e('Remove image', 'jigoshop'); ?></a>
			</div>
			<script type="text/javascript">
				jQuery(function($){
					$('#add-image').jigoshop_media({
						field: $('#product_cat_thumbnail_id'),
						thumbnail: $('#product_cat_thumbnail > img'),
						callback: function(){
							if($('#product_cat_thumbnail_id').val() != ''){
								$('#remove-image').show()
							}
						},
						library: {
							type: 'image'
						}
					});
					$('#remove-image').on('click', function(e){
						e.preventDefault();
						$('#product_cat_thumbnail_id').val('');
						$('#product_cat_thumbnail > img').attr('src', '<?php echo JIGOSHOP_URL.'/assets/images/placeholder.png'; ?>');
						$(this).hide();
					});
				});
			</script>
			<div class="clear"></div>
		</td>
	</tr>
<?php
}

add_action('created_term', 'jigoshop_category_thumbnail_field_save', 10, 3);
add_action('edit_term', 'jigoshop_category_thumbnail_field_save', 10, 3);

function jigoshop_category_thumbnail_field_save($term_id, $tt_id, $taxonomy)
{
	if( $taxonomy !== 'product_cat' ) return;

	if( isset( $_POST['product_cat_thumbnail_id'] ) )
		update_metadata( 'jigoshop_term', $term_id, 'thumbnail_id', $_POST['product_cat_thumbnail_id'] );

	foreach( apply_filters( 'jigoshop_optional_product_category_fields', array() ) as $field )
		if( isset( $_POST['product_cat_' . $field['id']] ) ) {
			$value = $_POST['product_cat_' . $field['id']];
			update_metadata( 'jigoshop_term', $term_id, $field['id'], ( $field['type'] === 'number' ? jigoshop_sanitize_num( $value ) : $value ) );
		}
}

/**
 * Thumbnail column for categories
 */
add_filter("manage_edit-product_cat_columns", 'jigoshop_product_cat_columns');
add_filter("manage_product_cat_custom_column", 'jigoshop_product_cat_column', 10, 3);

function jigoshop_product_cat_columns($columns)
{

	$new_columns = array(
		'cb' => $columns['cb'],
		'thumb' => null
	);

	unset($columns['cb']);
	$columns = array_merge($new_columns, $columns);

	return $columns;

}

function jigoshop_product_cat_column($columns, $column, $id)
{

	if ($column != 'thumb') {
		return false;
	}

	$image = jigoshop_product_cat_image($id);
	$columns .= '<a class="row-title" href="'.get_edit_term_link($id, 'product_cat', 'product').'">';
	$columns .= '<img src="'.$image['image'].'" alt="Thumbnail" class="wp-post-image" height="32" width="32" />';
	$columns .= '</a>';

	return $columns;

}

/**
 * Gets a product category field.
 * Returns all the fields (as strings) if the field is not specified.
 * Returns FALSE if the category or the field cannot be found.
 */
function jigoshop_get_category_field_for_product( $category_id, $field_id = '' ) {
	return maybe_unserialize( get_metadata( 'jigoshop_term', $category_id, $field_id, true ) );
}
