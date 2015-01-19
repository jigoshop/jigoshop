<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Helper\Product;

/**
 * @var $attribute Attribute Attribute to display.
 */
?>
<li class="list-group-item" data-id="<?php echo $attribute->getId(); ?>">
	<h4 class="list-group-item-heading">
		<?php echo $attribute->getLabel(); ?>
		<button type="button" class="remove-attribute btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</h4>
	<div class="list-group-item-text clearfix">
		<div class="col-md-4 options">
			<h5><?php _e('Options', 'jigoshop'); ?></h5>
			<?php Forms::checkbox(array(
				'name' => 'product[attributes]['.$attribute->getId().'][display]',
				'id' => 'product_attributes_'.$attribute->getId().'_display',
				'classes' => array('attribute-options'),
				'label' => __('Display on product page?', 'jigoshop'),
				'checked' => $attribute->isVisible(),
				'size' => 6,
			)); ?>
			<?php do_action('jigoshop\admin\product\attribute\options', $attribute); ?>
		</div>
		<div class="col-md-7 values">
			<h5><?php _e('Values', 'jigoshop'); ?></h5>
			<?php switch($attribute->getType()) {
				case Attribute\Multiselect::TYPE: ?>
						<?php foreach($attribute->getOptions() as $option): /** @var $option Attribute\Option */?>
							<?php Forms::checkbox(array(
								'name' => 'product[attributes]['.$attribute->getId().'][options]',
								'id' => 'product_attributes_'.$attribute->getId().'_option_'.$option->getId(),
								'classes' => array('attribute-'.$attribute->getId()),
								'label' => $option->getLabel(),
								'value' => apply_filters('jigoshop\template\admin\product\attribute\multiselect\value', $option->getId(), $attribute, $option),
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
							'value' => apply_filters('jigoshop\template\admin\product\attribute\select\value', $attribute->getValue(), $attribute),
							'options' => Product::getSelectOption($attribute->getOptions()),
							'size' => 12,
						)); ?>
					</div><?php
					break;
				case Attribute\Text::TYPE: ?>
					<div class="panel-body"><?php
					Forms::text(array(
						'name' => 'product[attributes]['.$attribute->getId().']',
						'classes' => array('attribute-'.$attribute->getId()),
						'value' => apply_filters('jigoshop\template\admin\product\attribute\text\value', $attribute->getValue(), $attribute),
						'size' => 12,
					)); ?>
					</div><?php
					break;
			} ?>
		</div>
	</div>
</li>
