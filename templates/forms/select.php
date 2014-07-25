<?php
use Jigoshop\Helper\Render;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $multiple boolean Is field supposed to accept multiple values?
 * @var $value mixed Currently selected value(s).
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 */
?>
<p class="form-field <?php echo $id; ?>_field">
	<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
	<select id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="<?php echo join(' ', $classes); ?>"
	        placeholder="<?php echo $placeholder; ?>"<?php $multiple and print ' multiple="multiple"'; ?>>
		<?php foreach($options as $option => $item): ?>
			<?php if(is_array($item)): ?>
				<optgroup label="<?php echo $option; ?>">
					<?php foreach($item as $subvalue => $sublabel): ?>
					<?php Render::output('forms/select/option', array('label' => $sublabel, 'value' => $subvalue, 'current' => $value)); ?>
					<?php endforeach; ?>
				</optgroup>
			<?php else: ?>
				<?php Render::output('forms/select/option', array('label' => $item, 'value' => $option, 'current' => $value)); ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</select>
	<?php if(!empty($tip)): ?>
	<a href="#" tip="<?php echo $tip; ?>" class="tips" tabindex="99"></a>
	<?php endif; ?>
	<?php if(!empty($description)): ?>
	<span class="description"><?php echo $description; ?></span>
	<?php endif; ?>
</p>
<!-- TODO: Get rid of this and use better asset script. -->
<script type="text/javascript">
	/*<![CDATA[*/
	jQuery(function($){
		$("#<?php echo $id; ?>").select2();
	});
	/*]]>*/
</script>
