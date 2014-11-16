<?php
use Jigoshop\Entity\Product\Attribute;

/**
 * @var $variation \Jigoshop\Entity\Product Product to display.
 */
?>
<li class="list-group-item" data-id="<?php echo $variation->getId(); ?>">
	<h4 class="list-group-item-heading">
		<?php echo $variation->getName(); ?>
		<button type="button" class="remove-variation btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</h4>
	<p class="list-group-item-text">
	</p>
</li>
