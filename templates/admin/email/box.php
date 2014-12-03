<?php
/**
 * @var $email \Jigoshop\Entity\Email Currently displayed email.
 * @var $emails array List of registered emails.
 */
?>
<div class="jigoshop">
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
<div id="coupon_options" class="panel jigoshop_options_panel">
	<script>
		jQuery(document).ready(function($) {
			$('#jigoshop_email_actions').change(function() {
				$.ajax({
					type: "POST",
					url: jigoshop_params.ajax_url,
					data: {
						'action':'jigoshop.admin.email.update_variable_list',
						'actions' : $('select#jigoshop_email_actions').val()
					},
					success:function(data) {
						$('#available_arguments').replaceWith(data);
					},
					error: function(errorThrown){
						console.log(errorThrown);
					}
				});
			});
		});
	</script>
</div>
