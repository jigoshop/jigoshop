<?php
/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 * @var $attribute_id string Attribute field ID.
 * @var $attribute_name string Attribute field name.
 * @var $attribute int Selected attribute.
 * @var $attributes array Available attributes.
 */
use Jigoshop\Helper\Forms;

?>
<p>
	<label for="<?php echo $title_id; ?>"><?php _e('Title:', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $title_id; ?>"  name="<?php echo $title_name; ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
	<label for="<?php echo $attribute_id; ?>"><?php _e('Attributes:', 'jigoshop'); ?></label>
	<select id="<?php echo $attribute_id; ?>"  name="<?php echo $attribute_name; ?>">
		<?php foreach ($attributes as $attr): /** @var $attr \Jigoshop\Entity\Product\Attribute */?>
			<option value="<?php echo $attr->getId(); ?>" <?php Forms::selected($attr->getId(), $attribute); ?>><?php echo $attr->getLabel(); ?></option>
		<?php endforeach; ?>
	</select>
</p>
