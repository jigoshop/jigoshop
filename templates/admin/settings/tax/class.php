<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Core\Options;

/**
 * @var $class array Class to display
 */
?>
<li class="form-inline">
	<?php Forms::text(array(
		'id' => 'tax_class_label_'.$class['class'],
		'name' => Options::NAME.'[classes][label][]',
		'value' => $class['label'],
		'placeholder' => __('Tax class label', 'jigoshop'),
	)); ?>
	<?php Forms::text(array(
		'id' => 'tax_class_'.$class['class'],
		'name' => Options::NAME.'[classes][class][]',
		'value' => $class['class'],
		'placeholder' => __('Tax class', 'jigoshop'),
	)); ?>
	<button type="button" class="remove-tax-class btn btn-default" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
</li>