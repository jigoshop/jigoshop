<?php
/**
 * @var $title_guest_id string Title field ID.
 * @var $title_guest_name string Title field name.
 * @var $title_guest string The title.
 * @var $title_user_id string Logged in title field ID.
 * @var $title_user_name string Logged in title field name.
 * @var $title_user string Logged in title.
 */
?>
<p>
	<label for="<?php echo $title_guest_id; ?>"><?php _e('Title (Logged Out):', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $title_guest_id; ?>"  name="<?php echo $title_guest_name; ?>" type="text" value="<?php echo $title_guest; ?>" />
</p>
<p>
	<label for="<?php echo $title_user_id; ?>"><?php _e('Title (Logged In):', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $title_user_id; ?>"  name="<?php echo $title_user_name; ?>" type="text" value="<?php echo $title_user; ?>" />
</p>
