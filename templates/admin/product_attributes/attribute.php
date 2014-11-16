<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Helper\Render;

/**
 * @var $attribute Attribute Attribute to display.
 * @var $id int ID of the attribute.
 * @var $types array List of available attribute types.
 */
?>
<tr class="attribute" data-id="<?php echo $id; ?>">
	<td>
		<?php Forms::text(array(
			'name' => 'attributes['.$id.'][label]',
			'classes' => array('attribute-label'),
			'value' => $attribute->getLabel(),
		)); ?>
	</td>
	<td>
		<?php Forms::text(array(
			'name' => 'attributes['.$id.'][slug]',
			'classes' => array('attribute-slug'),
			'value' => $attribute->getSlug(),
		)); ?>
	</td>
	<td>
		<?php Forms::select(array(
			'name' => 'attributes['.$id.'][type]',
			'classes' => array('attribute-type'),
			'value' => $attribute->getType(),
			'options' => $types,
		)); ?>
	</td>
	<td>
		<?php if ($attribute->getType() != Attribute\Text::TYPE): ?>
		<button type="button" class="configure-attribute btn btn-default"><?php _e('Configure', 'jigoshop'); ?></button>
		<?php endif; ?>
		<button type="button" class="remove-attribute btn btn-default" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</td>
</tr>
<?php if ($attribute->getType() != Attribute\Text::TYPE): ?>
<tr class="options not-active" data-id="<?php echo $id; ?>">
	<td colspan="4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 class="panel-title">
					<?php _e('Attribute options', 'jigoshop'); ?>
					<button type="button" class="btn btn-default pull-right"><?php _e('Close', 'jigoshop'); ?></button>
				</h5>
			</div>
			<table class="table table-condensed">
				<thead>
				<tr>
					<th scope="col"><?php _e('Label', 'jigoshop'); ?></th>
					<th scope="col"><?php _e('Value', 'jigoshop'); ?></th>
					<th scope="col"></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach($attribute->getOptions() as $option): ?>
					<?php Render::output('admin/product_attributes/option', array('id' => $id, 'option_id' => $option->getId(), 'option' => $option)); ?>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
				<tr>
					<td>
						<?php Forms::text(array(
							'name' => 'option_label',
							'classes' => array('new-option-label'),
							'placeholder' => __('New option label', 'jigoshop'),
						)); ?>
					</td>
					<td>
						<?php Forms::text(array(
							'name' => 'option_value',
							'classes' => array('new-option-value'),
							'placeholder' => __('New option value', 'jigoshop'),
						)); ?>
					</td>
					<td>
						<button type="button" class="btn btn-default add-option"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
					</td>
				</tr>
				</tfoot>
			</table>
		</div>
	</td>
</tr>
<?php endif; ?>
