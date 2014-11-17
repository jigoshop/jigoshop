<?php

function jigoshop_email_data_box($post)
{
	wp_nonce_field('jigoshop_save_data', 'jigoshop_meta_nonce');
	?>

	<div id="coupon_options" class="panel jigoshop_options_panel">
		<style>
			.jigoshop_options_panel .mid, .jigoshop_options_panel .form-field input.mid{
				width:100%;
				margin-right:10px;
			}
		</style>
		<script>
			jQuery(document).ready(function($) {
				$('#jigoshop_email_actions').change(function() {
					var posting = $.post('post.php', {
						jigoshop_email_get_variables: '1',
						jigoshop_email_actions: $('#jigoshop_email_actions').val()
					});
					// Put the results in a div
					posting.done(function(data) {
						$('#avalible_arguments').replaceWith    (data);
					});
				});
			});
		</script>
		<div class="options_group">
			<?php
			$args = array(
				'id' => 'jigoshop_email_subject',
				'label' => __('Subject', 'jigoshop'),
				'class' => 'mid',
				'multiple' => true,
			);
			echo Jigoshop_Forms::input($args);

			$registered_mails = jigoshop_emails::get_mail_list();
			$mails = array();
			if (!empty($registered_mails)) {
				foreach ($registered_mails as $hook => $detalis) {
					$mails[$hook] = $detalis['description'];
				}
			}

			$args = array(
				'id' => 'jigoshop_email_actions',
				'label' => __('Actions', 'jigoshop'),
				'multiple' => true,
				'class' => 'select mid',
				'placeholder' => __('No email action', 'jigoshop'),
				'options' => $mails,
				'selected' => ''
			);
			echo Jigoshop_Forms::select($args);
			?>
		</div>
	</div>
<?php
}

function jigoshop_email_variable_box($post)
{
	?>
	<div id="coupon_options" class="panel jigoshop_options_panel">
		<div class="options_group">
			<div id="avalible_arguments">
				<table width="100%">
					<?php
					$i = 0;
					$selected = (array)get_post_meta($post->ID, 'jigoshop_email_actions', true);
					$registered_mails = jigoshop_emails::get_mail_list();
					if (!empty($selected[0]) && !empty($registered_mails[$selected[0]])) {

						$keys = array_keys($registered_mails[$selected[0]]['accepted_args']);

						if (count($selected) > 1) {
							foreach ($selected as $hook) {
								$keys = array_intersect(array_keys($registered_mails[$hook]['accepted_args']), $keys);
							}
						}
						asort ($keys);
						if(empty($keys)){
							_e('Selected actions have not common variables', 'jigoshop');
						}
						foreach ($keys as $key) : ?>
							<?php if($i % 3 == 0): ?> <tr> <?php endif; ?>
								<td width="33.33%"><strong>[<?php echo $key ?>] </strong> - <?php echo $registered_mails[$selected[0]]['accepted_args'][$key] ?> <br/>
							<?php if($i % 3 == 2): ?> </tr> <?php endif; ?>
							<?php $i++; ?>
						<?php endforeach;
					} else {
						_e('Select email action to see avalible variables', 'jigoshop');
					}?>
				</table>
			</div>
		</div>
	</div>
<?php
}

add_action('jigoshop_process_shop_email_meta', 'jigoshop_process_shop_email_meta', 1, 2);

function jigoshop_process_shop_email_meta($post_id, $post)
{
	update_post_meta($post_id, 'jigoshop_email_subject', isset($_POST['jigoshop_email_subject']) ? $_POST['jigoshop_email_subject'] : '');
	update_post_meta($post_id, 'jigoshop_email_actions', isset($_POST['jigoshop_email_actions']) ? $_POST['jigoshop_email_actions'] : '');
	jigoshop_emails::set_actions($post_id, isset($_POST['jigoshop_email_actions']) ? $_POST['jigoshop_email_actions'] : '');
}

add_action('init', function (){
	if (isset($_POST['jigoshop_email_get_variables'])) {
		?><div id="avalible_arguments">
			<table width="100%">
				<?php
				$i = 0;
				$selected = $_POST['jigoshop_email_actions'];
				$registered_mails = jigoshop_emails::get_mail_list();
				if (!empty($selected[0]) && !empty($registered_mails[$selected[0]])) {

					$keys = array_keys($registered_mails[$selected[0]]['accepted_args']);

					if ($selected[1]) {
						foreach ($selected as $hook) {
							$keys = array_intersect(array_keys($registered_mails[$hook]['accepted_args']), $keys);
						}
					}
					asort($keys);
					if(empty($keys)){
						_e('Selected actions have not common variables', 'jigoshop');
					}
					foreach ($keys as $key) : ?>
						<?php if($i % 3 == 0): ?> <tr> <?php endif; ?>
							<td width="33.33%"><strong>[<?php echo $key ?>] </strong> - <?php echo $registered_mails[$selected[0]]['accepted_args'][$key] ?> <br/>
						<?php if($i % 3 == 2): ?> </tr> <?php endif; ?>
						<?php $i++; ?>
					<?php endforeach;
				} else {
					_e('Select email action to see avalible variables', 'jigoshop');
				}?>
			</table>
		</div><?php
		exit;
	}
}, 11);


