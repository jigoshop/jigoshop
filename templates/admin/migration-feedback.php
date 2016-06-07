<?php
if (!defined('ABSPATH'))
{
	exit;
}

?>
<div class="wrap jigoshop jigoshop-migration-ask-wrap">
	<h2><?php _e('Jigoshop Migration Information - report Jigoshop plugin', 'jigoshop'); ?></h2>
	<form action="" method="POST">
		<table class="wp-list-tablef widefat strfiped posts" style="width: 100%">
			<tr>
				<td style="width: 10%;"><?php _e('Plugin', 'jigoshop'); ?></td>
				<td style="width: 90%;">
					<b><?php echo esc_attr($_POST['feedbackPluginName']); ?></b>
				</td>
			</tr>
			<tr>
				<td><?php _e('Message', 'jigoshop'); ?></td>
				<td>
					<textarea style="width: 50%; height: 200px;" name="feedbackMsg">I think this plugin belong to You :)</textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="hidden" name="feedbackPluginName" value="<?php echo esc_attr($_POST['feedbackPluginName']); ?>">
					<input type="hidden" name="feedbackSlug" value="<?php echo esc_attr($_POST['feedbackSlug']); ?>">
					<button name="sendFeedback"><?php _e('Send', 'jigoshop'); ?></button>
				</td>
			</tr>
		</table>
	</form>
</div>
