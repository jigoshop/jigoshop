<?php
if (!defined('ABSPATH'))
{
	exit;
}

/**
 * @var array $allPlugins all Jigoshop plugins.
 * @var string $errors
 */

?>
<div class="wrap jigoshop jigoshop-migration-information-wrap">
	<h2><?php _e('Jigoshop Migration Information', 'jigoshop'); ?></h2>
	<?php if (isset($errors)): ?>
		<div style="border: 1px silver solid; background-color: lightcoral; padding: 10px 16px 5px;">
		<?php echo $errors; ?>
		</div>
	<?php else: ?>
		<table class="wp-list-table widefat fixed striped posts">
			<thead>
			<tr>
				<th scope="col" class="manage-column column-title column-primary desc"><span>Title</span></a></th>
				<th scope="col" class="manage-column column-date desc"><span>Date</span></a></th>
				<th scope="col" class="manage-column column-date desc"><span>Date</span></a></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($allPlugins as $k => $v): ?>
				<tr>
					<td class="title column-title column-primary page-title">
						<strong><?php echo $v['Name']; ?></strong>
					</td>
					<td>Published<br>f</td>
					<td>Published<br>f</td>
				</tr>
			<?php endforeach; ?>
			</tbody>

			<tfoot>
			<tr>
				<th scope="col" class="manage-column column-title column-primary desc"><span>Title</span></a></th>
				<th scope="col" class="manage-column column-date desc"><span>Date</span></a></th>
				<th scope="col" class="manage-column column-date desc"><span>Date</span></a></th>
			</tr>
			</tfoot>
		</table>
	<?php endif; ?>
</div>
