<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product\Attributes\Attribute;

/**
 * @var $attribute Attribute Attribute to display.
 */
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h5 class="panel-title">
			<?php echo $attribute->getLabel(); ?>
			<button type="button" class="remove-attribute btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
		</h5>
	</div>
	<?php switch($attribute->getType()) {
		case Attribute::MULTISELECT:
			?>
			<ul class="list-group">
				<?php foreach($attribute->getOptions() as $option): /** @var $option Attribute\Option */?>
					<li class="list-group-item"><?php Forms::checkbox(array(
							'name' => 'product[attributes]['.$attribute->getId().'][options]['.$option->getId().']',
							'label' => $option->getLabel(),
							'value' => $option->getValue(),
							'checked' => in_array($option->getValue(), $attribute->getValue()),
						)); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php
			break;
		case Attribute::SELECT:
			?>
			<ul class="list-group">
				<?php foreach($attribute->getOptions() as $option): /** @var $option Attribute\Option */?>
					<li class="list-group-item"><?php Forms::checkbox(array( // TODO: Change into radio buttons
							'name' => 'product[attributes]['.$attribute->getId().']',
							'label' => $option->getLabel(),
							'value' => $option->getValue(),
							'checked' => $option->getValue() == $attribute->getValue(),
						)); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php
			break;
		case Attribute::TEXT:
			?><div class="panel-body"><?php
			Forms::textarea(array(
				'name' => 'product[attributes]['.$attribute->getId().']',
				'value' => $attribute->getValue(),
				'size' => 12,
			));
			?></div><?php
			break;
	} ?>
</div>
