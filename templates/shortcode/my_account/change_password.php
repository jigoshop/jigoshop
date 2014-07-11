<?php jigoshop::show_messages(); ?>
<form action="<?php echo esc_url(apply_filters('jigoshop_get_change_password_page_id', get_permalink(jigoshop_get_page_id('change_password')))); ?>" method="post">
	<p class="form-row form-row-first">
		<label for="password-1"><?php _e('New password', 'jigoshop'); ?> <span class="required">*</span></label>
		<input type="password" class="input-text" name="password-1" id="password-1" />
	</p>

	<p class="form-row form-row-last">
		<label for="password-2"><?php _e('Re-enter new password', 'jigoshop'); ?> <span class="required">*</span></label>
		<input type="password" class="input-text" name="password-2" id="password-2" />
	</p>

	<div class="clear"></div>
	<?php jigoshop::nonce_field('change_password') ?>
	<p><input type="submit" class="button" name="save_password" value="<?php _e('Save', 'jigoshop'); ?>" /></p>
</form>
