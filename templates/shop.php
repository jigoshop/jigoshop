<?php
/**
 * @var $page string Page contents.
 * @var $products array List of products to display.
 */
do_action('jigoshop\shop\content\before'); ?>

<?php if(is_search()): ?>
	<h1 class="page-title"><?php _e('Search Results:', 'jigoshop'); ?> &ldquo;<?php the_search_query(); ?>&rdquo; <?php if (get_query_var('paged')) echo ' &mdash; Page '.get_query_var('paged'); ?></h1>
<?php else: ?>
	<?php echo apply_filters('jigoshop\shop\content\title', '<h1 class="page-title">'.__('All Products', 'jigoshop').'</h1>'); ?>
<?php endif; ?>

<?php if ($page): ?>
	<?php echo apply_filters('the_content', $page); ?>
<?php endif; ?>

<?php
\Jigoshop\Helper\Render::output('shop/list', array(
	'products' => $products,
));
?>

<?php do_action('jigoshop\shop\content\after'); ?>
<?php do_action('jigoshop\sidebar'); ?>