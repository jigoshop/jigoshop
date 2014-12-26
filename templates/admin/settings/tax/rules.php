<?php
use Jigoshop\Helper\Render;

/**
 * @var $rules array List of current tax rules
 * @var $classes array List of currently available tax classes
 * @var $countries array List of countries
 */
?>
<table class="table table-striped" id="tax-rules">
	<thead>
		<tr>
			<th scope="col"><?php _e('Label', 'jigoshop'); ?></th>
			<th scope="col">
				<?php _e('Class', 'jigoshop'); ?>
				<a href="#" data-toggle="tooltip" class="badge" data-placement="top" title="<?php _e('Tax classes needs to be saved first before updating rules.', 'jigoshop'); ?>">?</a>
			</th>
			<th scope="col"><?php _e('Is compound?', 'jigoshop'); ?></th>
			<th scope="col"><?php _e('Rate', 'jigoshop'); ?></th>
			<th scope="col"><?php _e('Country', 'jigoshop'); ?></th>
			<th scope="col"><?php _e('State', 'jigoshop'); ?></th>
			<th scope="col">
				<?php _e('Postcodes', 'jigoshop'); ?>
				<a href="#" data-toggle="tooltip" class="badge" data-placement="top" title="<?php _e('Enter list of postcodes, separating with comma.', 'jigoshop'); ?>">?</a>
			</th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($rules as $rule): ?>
			<?php Render::output('admin/settings/tax/rule', array(
				'rule' => $rule, 'classes' => $classes, 'countries' => $countries,
			)); ?>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="6">
				<button type="button" class="btn btn-default" id="add-tax-rule"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
			</td>
		</tr>
	</tfoot>
</table>
