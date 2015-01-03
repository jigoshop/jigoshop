<?php
use Jigoshop\Core\Options;
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
<?php if(!empty($links)): ?>
	<nav role="navigation">
		<ul class="pagenav">
			<?php foreach ($links as $title => $href): ?>
				<li><a title="<?php printf(__('Go to %s', 'jigoshop'), $title); ?>" href="<?php echo $href; ?>"><?php echo $title; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
<?php endif; ?>
<?php
do_action('jigoshop_widget_login_after_form');
echo $after_widget;
