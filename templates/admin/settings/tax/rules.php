<?php
use Jigoshop\Helper\Render;

/**
 * @var $rules array List of current tax rules
 * @var $classes array List of currently available tax classes
 */
?>
<table class="table table-striped" id="tax-rules">
	<thead>
		<tr>
			<th scope="col"><?php _e('Label', 'jigoshop'); ?></th>
			<th scope="col"><?php _e('Class', 'jigoshop'); ?></th>
			<th scope="col"><?php _e('Rate', 'jigoshop'); ?></th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($rules as $rule): ?>
			<?php Render::output('admin/settings/tax/rule', array('rule' => $rule, 'classes' => $classes)); ?>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3">
				<button type="button" class="btn btn-default" id="add-tax-rule"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
			</td>
		</tr>
	</tfoot>
</table>