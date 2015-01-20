<?php
use Jigoshop\Core\Types;

/**
 * @var $image array Image data array.
 */
?>
<tr class="form-field">
	<th scope="row" valign="top"><label><?php _e('Thumbnail', 'jigoshop'); ?></label></th>
	<td>
		<div id="<?php echo Types::PRODUCT_CATEGORY; ?>_thumbnail"
		     style="float:left;margin-right:10px;"><img src="<?php echo $image['image']; ?>"
		                                                width="60px" height="60px" /></div>
		<div style="line-height:60px;">
			<input type="hidden" id="<?php echo Types::PRODUCT_CATEGORY; ?>_thumbnail_id"
			       name="<?php echo Types::PRODUCT_CATEGORY; ?>_thumbnail_id"
			       value="<?php echo $image['thumbnail_id']; ?>" />
			<a id="add-image" href="#" class="button"
			   data-title="<?php esc_attr_e('Choose thumbnail image', 'jigoshop'); ?>"
			   data-button="<?php esc_attr_e('Set as thumbnail', 'jigoshop'); ?>"><?php _e('Change image', 'jigoshop'); ?></a>
			<a id="remove-image" href="#" class="button"
			   style="display: none;"><?php _e('Remove image', 'jigoshop'); ?></a>
		</div>
		<div class="clear"></div>
	</td>
</tr>
