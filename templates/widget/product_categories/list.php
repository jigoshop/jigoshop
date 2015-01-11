<?php
use Jigoshop\Core\Options;
use Jigoshop\Helper\Product;

/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $args array
 */

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<ul>
	<?php wp_list_categories(apply_filters('widget_product_categories_args', $args)); ?>
</ul>
<?php echo $after_widget;
