<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<form method="post" action="options.php">
	<?php settings_fields('jigoshop_status_settings_fields'); ?>
	<?php $options = wp_parse_args(get_option('jigoshop_status_options', array()), array(
		'uninstall_data' => 0,
		'template_debug_mode' => 0,
		'shipping_debug_mode' => 0
	)); ?>
	<table class="jigoshop_status_table widefat" cellspacing="0">
		<thead class="tools">
		<tr>
			<th colspan="2"><?php _e('Tools', 'jigoshop'); ?></th>
		</tr>
		</thead>
		<tbody class="tools">
		<?php foreach ($tools as $action => $tool) : ?>
			<tr>
				<td><?php echo esc_html($tool['name']); ?></td>
				<td>
					<p>
						<a href="<?php echo wp_nonce_url(admin_url('admin.php?page=jigoshop_system_info&tab=tools&action='.$action), 'debug_action'); ?>" class="button"><?php echo esc_html($tool['button']); ?></a>
						<span class="description"><?php echo wp_kses_post($tool['desc']); ?></span>
					</p>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'jigoshop') ?>" />
	</p>
</form>
