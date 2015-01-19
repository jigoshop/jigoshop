<?php
use Jigoshop\Core\Options;
use Jigoshop\Helper\Product;

/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $fields array
 */

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<form role="search" method="get" id="searchform" action="<?php echo home_url(); ?>">
	<div>
		<label class="assistive-text" for="s"><?php _e('Search for:', 'jigoshop'); ?></label>
		<input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" placeholder="<?php _e('Search for products', 'jigoshop'); ?>" />
		<input type="submit" id="searchsubmit" value="<?php _e('Search', 'jigoshop'); ?>" />
		<?php \Jigoshop\Helper\Forms::printHiddenFields($fields, array('s')); ?>
	</div>
</form>
<?php echo $after_widget;
