<form method="post" class="login">
	<p class="form-row form-row-first">
		<label for="username"><?php _e('Username', 'jigoshop'); ?> <span class="required">*</span></label>
		<span class="input-text"><input type="text" name="username" id="username" /></span>
	</p>
	<p class="form-row form-row-last">
		<label for="password"><?php _e('Password', 'jigoshop'); ?> <span class="required">*</span></label>
		<span class="input-text"><input type="password" name="password" id="password" /></span>
	</p>
	<div class="clear"></div>
	
	<p class="form-row">
		<input type="submit" class="button" name="login" value="<?php _e('Login', 'jigoshop'); ?>" />
	</p>
</form>