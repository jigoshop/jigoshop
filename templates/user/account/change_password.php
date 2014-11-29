<?php
use Jigoshop\Entity\Customer;
use Jigoshop\Helper\Render;

/**
 * @var $customer Customer
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $myAccountUrl string URL to my account.
 */
?>
<h1><?php _e('My account &rang; Change password', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<form class="form-horizontal" role="form" method="post">
	<?php \Jigoshop\Helper\Forms::text(array(
		'name' => 'password',
		'type' => 'password',
		'label' => __('Current password', 'jigoshop'),
		'value' => '',
	)); ?>
	<?php \Jigoshop\Helper\Forms::text(array(
		'name' => 'new-password',
		'type' => 'password',
		'label' => __('New password', 'jigoshop'),
		'value' => '',
	)); ?>
	<?php \Jigoshop\Helper\Forms::text(array(
		'name' => 'new-password-2',
		'type' => 'password',
		'label' => __('Re-type new password', 'jigoshop'),
		'value' => '',
	)); ?>
	<a href="<?php echo $myAccountUrl; ?>" class="btn btn-default"><?php _e('Go back to My account', 'jigoshop'); ?></a>
	<button class="btn btn-success pull-right" name="action" value="change_password"><?php _e('Change password', 'jigoshop'); ?></button>
</form>
