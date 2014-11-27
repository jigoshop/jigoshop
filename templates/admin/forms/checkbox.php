<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $value mixed Current value.
 * @var $checked boolean Whether checkbox is checked.
 * @var $multiple boolean Whether checkbox is with multiple values.
 * @var $disabled bool Is field disabled?
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 * @var $hidden boolean Whether the field is hidden.
 * @var $size int Size of form widget.
 */
$hasLabel = !empty($label);
?>
<div class="form-group <?php echo $id; ?>_field <?php echo join(' ', $classes); ?><?php $hidden and print ' not-active'; ?>">
	<?php if($hasLabel): ?>
	<label for="<?php echo $id; ?>" class="col-sm-<?php echo 12 - $size; ?> control-label">
		<?php echo $label; ?>
		<?php if(!empty($tip)): ?>
			<a href="#" data-toggle="tooltip" class="badge" data-placement="top" title="<?php echo $tip; ?>">?</a>
		<?php endif; ?>
	</label>
	<?php endif; ?>
	<div class="checkbox-inline">
		<?php if(!$multiple): ?>
			<input type="hidden" name="<?php echo $name; ?>" value="off" />
		<?php endif; ?>
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="<?php echo join(' ', $classes); ?>" <?php echo Forms::checked($checked, true); ?> value="<?php echo $value; ?>" />
		<?php if(!$hasLabel && !empty($tip)): ?>
		<a href="#" data-toggle="tooltip" class="badge" data-placement="top" title="<?php echo $tip; ?>">?</a>
		<?php endif; ?>
		<?php if(!empty($description)): ?>
			<span class="help"><?php echo $description; ?></span>
		<?php endif; ?>
	</div>
</div>
