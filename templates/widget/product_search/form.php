<?php
/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 */
?>
<p>
	<label for="<?php echo $title_id; ?>"><?php _e('Title:', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $title_id; ?>"  name="<?php echo $title_name; ?>" type="text" value="<?php echo $title; ?>" />
</p>
