<?php
use Jigoshop\Admin\Helper\Forms;
/**
 * @var $depth int Current category depth.
 * @var $value string Current category value.
 * @var $name string Category name.
 * @var $selected string Currently selected item.
 * @var $show_count bool Whether to show count of products in the category.
 * @var $count int Count of items in category.
 */
?>
<option class="level-<?php echo $depth; ?>" value="<?php echo $value; ?>" <?php echo Forms::selected($value, $selected); ?>>
	<?php echo str_repeat('&nbsp;', $depth*3).$name; ?>
	<?php if ($show_count): ?>
		(<?php echo $count; ?>)
	<?php endif; ?>
</option>
