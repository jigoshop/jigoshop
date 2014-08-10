<?php
use Jigoshop\Helper\Render;

/**
 * @var $classes array List of currently available tax classes
 */
?>
<ul class="list-unstyled" id="tax-classes">
	<?php foreach($classes as $class): ?>
		<?php Render::output('admin/settings/tax/class', array('class' => $class)); ?>
	<?php endforeach; ?>
</ul>
<button type="button" class="btn btn-default" id="add-tax-class"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>