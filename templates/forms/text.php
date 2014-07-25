<?php
/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $value mixed Current value.
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 */
?>
<p class="form-field <?php echo $id; ?>_field">
	<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
	<input type="text" id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="<?php echo join(' ', $classes); ?>"
	        placeholder="<?php echo $placeholder; ?>" value="<?php echo $value; ?>" />
	<?php if(!empty($tip)): ?>
		<a href="#" tip="<?php echo $tip; ?>" class="tips" tabindex="99"></a>
	<?php endif; ?>
	<?php if(!empty($description)): ?>
		<span class="description"><?php echo $description; ?></span>
	<?php endif; ?>
</p>
