<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Core\Options;

/**
 * @var $class array Class to display
 */
?>
<tr>
	<td>
	<?php Forms::text(array(
		'id' => 'tax_class_label_'.$class['class'],
		'name' => Options::NAME.'[classes][label][]',
		'value' => $class['label'],
		'placeholder' => __('Tax class label', 'jigoshop'),
	)); ?>
	</td>
	<td>
	<?php Forms::text(array(
		'id' => 'tax_class_'.$class['class'],
		'name' => Options::NAME.'[classes][class][]',
		'value' => $class['class'],
		'placeholder' => __('Tax class', 'jigoshop'),
	)); ?>
	</td>
	<td class="vert-align">
		<button type="button" class="remove-tax-class btn btn-default" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</td>
</tr>