<?php
/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $value mixed Current value.
 */
?>
<div class="form-group <?php echo $id; ?>_field clearfix">
	<input type="hidden" id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="form-control <?php echo join(' ', $classes); ?>" value="<?php echo $value; ?>" />
</div>
