<?php
/**
 * @var $content string Content to display.
 */
get_header('shop');
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main jigoshop" role="main">
		<?php do_action('jigoshop\shop\content\before'); ?>
		<div class="content">
			<?php echo $content; ?>
		</div>
		<?php do_action('jigoshop\shop\content\after'); ?>
	</main>
</div>
<?php do_action('jigoshop\sidebar'); ?>
<?php get_sidebar('shop'); // TODO: Remove on implementation of jigoshop\sidebar ?>
<?php get_footer('shop'); ?>
