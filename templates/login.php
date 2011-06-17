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
		<?php jigoshop::nonce_field('login', 'login') ?>
		<input type="submit" class="button" name="login" value="<?php _e('Login', 'jigoshop'); ?>" />
		<a class="lost_password" href="<?php echo home_url('wp-login.php?action=lostpassword'); ?>"><?php _e('Lost Password?', 'jigoshop'); ?></a>
	</p>
</form>