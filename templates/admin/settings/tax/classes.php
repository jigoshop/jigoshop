<?php
use Jigoshop\Helper\Render;

/**
 * @var $classes array List of currently available tax classes
 */
?>
<table class="table table-striped" id="tax-classes">
	<thead>
		<tr>
			<th scope="col"><?php _e('Label', 'jigoshop'); ?></th>
			<th scope="col"><?php _e('Internal class name', 'jigoshop'); ?></th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($classes as $class): ?>
			<?php Render::output('admin/settings/tax/class', array('class' => $class)); ?>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3">
				<button type="button" class="btn btn-default" id="add-tax-class"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
			</td>
		</tr>
	</tfoot>
</table>