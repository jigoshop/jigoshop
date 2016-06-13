<?php
if (!defined('ABSPATH'))
{
	exit;
}

/**
 * @var
 */

?>
<div class="wrap jigoshop jigoshop-migration-ask-wrap">
	<h2><?php _e('Jigoshop Migration Information - ask for plugin availability', 'jigoshop'); ?></h2>
	<?php if (isset($errors)): ?>
		<div style="border: 1px silver solid; background-color: lightcoral; padding: 10px 16px 5px;">
			<?php echo $errors; ?>
		</div>
	<?php else: ?>
		<form action="" method="POST">
			<table class="wp-list-tablef widefat strfiped posts" style="width: 100%">
				<tr>
					<td style="width: 10%;"><?php _e('Plugin', 'jigoshop'); ?></td>
					<td style="width: 90%;">
						<b><?php echo esc_attr($_POST['askPluginName']); ?></b>
					</td>
				</tr>
				<tr>
					<td><?php _e('Reply to', 'jigoshop'); ?></td>
					<td>
						<input style="width: 50%;" type="text" name="askEmail" value="<?php echo Jigoshop_Base::get_options()->get('jigoshop_company_email') ?>">
					</td>
				</tr>
				<tr>
					<td><?php _e('Message', 'jigoshop'); ?></td>
					<td>
						<textarea style="width: 50%; height: 200px;" name="askMsg"><?php echo esc_attr('Hey Jigoshop, when is this gonna be ready?');
							?></textarea>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="hidden" name="askPluginName2" value="<?php echo esc_attr($_POST['askPluginName']); ?>">
						<input type="hidden" name="askRepoUrl" value="<?php echo esc_attr($_POST['askRepoUrl']); ?>">
						<button name="sendAsk"><?php _e('Send Ask', 'jigoshop'); ?></button>
					</td>
				</tr>
			</table>
		</form>
	<?php endif; ?>
</div>
