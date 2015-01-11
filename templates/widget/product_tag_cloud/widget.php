<?php
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Helper\Product;

/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $products array
 */

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<div class="tagcloud">
	<?php echo wp_tag_cloud(apply_filters('widget_tag_cloud_args', array('taxonomy' => Types::PRODUCT_TAG))); ?>
</div>
<?php echo $after_widget;
