<?php
use Jigoshop\Helper\Forms;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $value mixed Current value.
 * @var $disabled bool Is field disabled?
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 */
?>
<div class="form-group <?php echo $id; ?>_field">
	<label for="<?php echo $id; ?>" class="col-sm-2 control-label">
		<?php echo $label; ?>
		<?php if(!empty($tip)): ?>
			<a href="#" data-toggle="tooltip" class="badge" data-placement="top" title="<?php echo $tip; ?>">?</a>
		<?php endif; ?>
	</label>
	<div class="col-sm-9 checkbox-inline">
		<input type="hidden" name="<?php echo $name; ?>" value="off" />
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="<?php echo join(' ', $classes); ?>"
			<?php echo Forms::checked($value, true); ?> value="on"<?php $disabled and print ' disabled'; ?> />
		<?php if(!empty($description)): ?>
			<span class="help"><?php echo $description; ?></span>
		<?php endif; ?>
	</div>
</div>
