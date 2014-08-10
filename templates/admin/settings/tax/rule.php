<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Core\Options;

/**
 * @var $rule array Rule to display
 * @var $classes array List of currently available tax classes
 */
?>
<tr>
	<td>
	<?php Forms::text(array(
		'id' => 'tax_rule_label_'.$rule['id'],
		'name' => Options::NAME.'[rules][label][]',
		'value' => $rule['label'],
		'placeholder' => __('Rule label', 'jigoshop'),
	)); ?>
	</td>
	<td>
	<?php Forms::select(array(
		'id' => 'tax_rule_class_'.$rule['id'],
		'name' => Options::NAME.'[rules][class][]',
		'value' => $rule['class'],
		'options' => $classes,
		'placeholder' => __('Tax class', 'jigoshop'),
	)); ?>
	</td>
	<td>
	<?php Forms::text(array(
		'id' => 'tax_rule_rate_'.$rule['id'],
		'name' => Options::NAME.'[rules][rate][]',
		'value' => $rule['rate'],
		'placeholder' => __('Tax rate', 'jigoshop'),
	)); ?>
	</td>
	<td class="vert-align">
		<input type="hidden" name="<?php echo Options::NAME.'[rules][id][]'; ?>" value="<?php echo $rule['id']; ?>" />
		<button type="button" class="remove-tax-rule btn btn-default" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</td>
</tr>