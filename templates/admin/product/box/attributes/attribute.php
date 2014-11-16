<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product\Attributes\Attribute;

/**
 * @var $attribute Attribute Attribute to display.
 */
?>
<li class="list-group-item" data-id="<?php echo $attribute->getId(); ?>">
	<h4 class="list-group-item-heading">
		<?php echo $attribute->getLabel(); ?>
		<button type="button" class="remove-attribute btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</h4>
	<p class="list-group-item-text">
	<?php switch($attribute->getType()) {
		case Attribute\Multiselect::TYPE: ?>
				<?php foreach($attribute->getOptions() as $option): /** @var $option Attribute\Option */?>
					<?php Forms::checkbox(array(
						'name' => 'product[attributes]['.$attribute->getId().'][options]',
						'id' => 'product_attributes_'.$attribute->getId().'_option_'.$option->getId(),
						'classes' => array('attribute-'.$attribute->getId()),
						'label' => $option->getLabel(),
						'value' => $option->getId(),
						'multiple' => true,
						'checked' => in_array($option->getId(), $attribute->getValue()),
						)); ?>
				<?php endforeach; ?>
			<?php
			break;
		case Attribute\Select::TYPE: ?>
			<div class="panel-body"><?php
				Forms::select(array(
					'name' => 'product[attributes]['.$attribute->getId().']',
					'classes' => array('attribute-'.$attribute->getId()),
					'value' => $attribute->getValue(),
					'options' => array_map(function($item){ return $item->getLabel(); }, $attribute->getOptions()),
					'size' => 12,
				)); ?>
			</div><?php
			break;
		case Attribute\Text::TYPE: ?>
			<div class="panel-body"><?php
			Forms::text(array(
				'name' => 'product[attributes]['.$attribute->getId().']',
				'classes' => array('attribute-'.$attribute->getId()),
				'value' => $attribute->getValue(),
				'size' => 12,
			)); ?>
			</div><?php
			break;
	} ?>
	</p>
</li>
