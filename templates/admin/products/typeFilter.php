<?php
/**
 * @var $types array List of available types.
 * @var $current string Currently selected type.
 */
?>
<?php var_dump($types); ?>
<select name="product_type" id="dropdown_product_type">
	<option value='0'><?= __('Show all types', 'jigoshop'); ?></option>
	<?php foreach($types as $type): ?>
	<option value="<?= esc_attr($type->slug); ?>" <?php selected($type->slug, $current); ?>><?= __($type->name, 'jigoshop'); ?> (<?= absint($type->count); ?>)</option>
	<?php endforeach; ?>
</select>