<?php
use Jigoshop\Helper\Forms;

/**
 * @var $types array List of available types.
 * @var $current string Currently selected type.
 */
?>
<select name="product_type" id="dropdown_product_type">
	<option value='0'><?php echo __('Show all types', 'jigoshop'); ?></option>
	<?php foreach($types as $type => $options): ?>
	<option value="<?php echo $type; ?>" <?php echo Forms::selected($type, $current); ?>><?php echo $options['label']; ?> (<?php echo absint($options['count']); ?>)</option>
	<?php endforeach; ?>
</select>
