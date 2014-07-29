<?php
use Jigoshop\Helper\Forms;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $value mixed Current value.
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 */
?>
<div class="form-group <?php echo $id; ?>_field">
	<label for="<?php echo $id; ?>" class="col-sm-2 control-label"><?php echo $label; ?></label>
	<div class="col-sm-9 checkbox-inline">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="<?php echo join(' ', $classes); ?>"
			<?php Forms::checked($value, true); ?> value="on" />
		<?php if(!empty($description)): ?>
			<span class="help"><?php echo $description; ?></span>
		<?php endif; ?>
		<?php if(!empty($tip)): ?>
			<a href="#" tip="<?php echo $tip; ?>" class="tips" tabindex="99"></a>
		<?php endif; ?>
	</div>
</div>
