<?php
/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 * @var $number_id string Number field ID.
 * @var $number_name string Number field name.
 * @var $number string Number of products in widget.
 */
?>
<p>
	<label for="<?php echo $title_id; ?>"><?php _e('Title:', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $title_id; ?>"  name="<?php echo $title_name; ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
	<label for="<?php echo $number_id; ?>"><?php _e('Number of products to show:', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $number_id; ?>"  name="<?php echo $number_name; ?>" type="number" min="1" value="<?php echo $number; ?>" />
</p>
