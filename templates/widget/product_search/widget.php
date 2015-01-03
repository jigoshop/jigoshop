<?php
use Jigoshop\Core\Options;
use Jigoshop\Helper\Product;

/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 */

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<form role="search" method="get" id="searchform" action="<?php echo home_url(); ?>">
	<?php /* TODO: Properly add support for mixing widgets
 foreach ($fields as $key => $value) {
			if (!in_array($key, array('s', 'post_type'))) {
				$form .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
		}
 */ ?>
	<div>
		<label class="assistive-text" for="s"><?php _e('Search for:', 'jigoshop'); ?></label>
		<input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" placeholder="<?php _e('Search for products', 'jigoshop'); ?>" />
		<input type="submit" id="searchsubmit" value="<?php _e('Search', 'jigoshop'); ?>" />
		<input type="hidden" name="post_type" value="<?php echo \Jigoshop\Core\Types::PRODUCT; ?>" />
	</div>
</form>
<?php echo $after_widget;
