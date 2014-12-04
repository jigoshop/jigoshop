<?php
/**
 * @var $email \Jigoshop\Entity\Email Currently displayed email.
 * @var $emails array List of registered emails.
 */
?>
<div class="jigoshop" data-id="<?php echo $email->getId(); ?>">
	<?php echo \Jigoshop\Admin\Helper\Forms::text(array(
		'name' => 'jigoshop_email[subject]',
		'label' => __('Subject', 'jigoshop'),
		'value' => $email->getSubject(),
	)); ?>
	<?php echo \Jigoshop\Admin\Helper\Forms::select(array(
		'id' => 'jigoshop_email_actions',
		'name' => 'jigoshop_email[actions]',
		'label' => __('Actions', 'jigoshop'),
		'multiple' => true,
		'placeholder' => __('Select action...', 'jigoshop'),
		'options' => $emails,
		'value' => $email->getActions(),
	)); ?>
</div>
