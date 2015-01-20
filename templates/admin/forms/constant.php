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
	<?php elseif(!empty($tip)): ?>
		<a href="#" data-toggle="tooltip" class="badge" data-placement="top" title="<?php echo $tip; ?>">?</a>
	<?php endif; ?>
	<div class="<?php echo 'col-sm-'.($hasLabel ? $size - 1 : 12); ?>">
		<p class="form-control-static <?php echo join(' ', $classes); ?>" id="<?php echo $id; ?>"><?php echo $value; ?></p>
		<?php if(!empty($description)): ?>
			<span class="help-block"><?php echo $description; ?></span>
		<?php endif; ?>
	</div>
</div>
