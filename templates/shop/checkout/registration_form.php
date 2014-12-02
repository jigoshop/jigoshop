<?php
use Jigoshop\Helper\Forms;

/**
 * @var $showRegistrationForm bool Whether to show registration form.
 */
?>
<?php if (!$showRegistrationForm): ?>
	<?php Forms::checkbox(array(
		'label' => __('Would you like to create an account?', 'jigoshop'),
		'name' => 'jigoshop_account[create]',
		'id' => 'create-account',
		'size' => 9
	)); ?>
<?php endif; ?>
<div class="row clearfix<?php !$showRegistrationForm and print ' not-active'; ?>" id="registration-form">
	<h4><?php _e('Registration', 'jigoshop'); ?></h4>
	<div class="col-md-12">
		<?php Forms::text(array(
			'label' => __('Username', 'jigoshop'),
			'name' => 'jigoshop_account[login]',
			'placeholder' => __('Enter username', 'jigoshop'),
		)); ?>
		<?php Forms::text(array(
			'label' => __('Password', 'jigoshop'),
			'type' => 'password',
			'name' => 'jigoshop_account[password]',
			'placeholder' => __('Your password', 'jigoshop'),
		)); ?>
		<?php Forms::text(array(
			'label' => __('Re-type password', 'jigoshop'),
			'type' => 'password',
			'name' => 'jigoshop_account[password2]',
			'placeholder' => __('Re-type your password', 'jigoshop'),
		)); ?>
		<?php if ($showRegistrationForm): ?>
			<?php Forms::checkbox(array(
				'label' => __('I agree to account creation', 'jigoshop'),
				'name' => 'jigoshop_account[create]',
				'size' => 9
			)); ?>
		<?php endif; ?>
	</div>
</div>
