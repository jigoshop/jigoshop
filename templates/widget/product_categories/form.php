<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 * @var $dropdown_id string Dropdown field ID.
 * @var $dropdown_name string Dropdown field name.
 * @var $dropdown bool Display as dropdown?
 * @var $count_id string Count field ID.
 * @var $count_name string Count field name.
 * @var $count bool Display count of products?
 * @var $hierarchical_id string Hierarchical field ID.
 * @var $hierarchical_name string Hierarchical field name.
 * @var $hierarchical bool Display hierarchical data?
 */
?>
<p>
	<label for="<?php echo $title_id; ?>"><?php _e('Title:', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $title_id; ?>"  name="<?php echo $title_name; ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
	<label for="<?php echo $dropdown_id; ?>">
		<input class="checkbox" id="<?php echo $dropdown_id; ?>"  name="<?php echo $dropdown_name; ?>" type="checkbox" value="on" <?php echo Forms::checked($dropdown, true); ?> />
		<?php _e('Show as dropdown', 'jigoshop'); ?>
	</label>
	<br/>
	<label for="<?php echo $count_id; ?>">
		<input class="checkbox" id="<?php echo $count_id; ?>"  name="<?php echo $count_name; ?>" type="checkbox" value="on" <?php echo Forms::checked($count, true); ?> />
		<?php _e('Show product counts', 'jigoshop'); ?>
	</label>
	<br/>
	<label for="<?php echo $hierarchical_id; ?>">
		<input class="checkbox" id="<?php echo $hierarchical_id; ?>"  name="<?php echo $hierarchical_name; ?>" type="checkbox" value="on" <?php echo Forms::checked($hierarchical, true); ?> />
		<?php _e('Show hierarchy', 'jigoshop'); ?>
	</label>
</p>
