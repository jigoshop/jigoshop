<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $option \Jigoshop\Entity\Product\Attribute\Option Option to display.
 * @var $id int ID of the attribute.
 * @var $option_id int ID of the option.
 */
?>
<tr data-id="<?php echo $option_id; ?>">
	<td>
		<?php Forms::text(array(
			'name' => 'attributes['.$id.'][options]['.$option_id.'][label]',
			'classes' => array('option-label'),
			'value' => $option->getLabel(),
		)); ?>
	</td>
	<td>
		<?php Forms::text(array(
			'name' => 'attributes['.$id.'][options]['.$option_id.'][value]',
			'classes' => array('option-value'),
			'value' => $option->getValue(),
		)); ?>
	</td>
	<td>
		<button type="button" class="remove-attribute-option btn btn-default" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</td>
</tr>
