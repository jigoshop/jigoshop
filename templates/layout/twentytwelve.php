<?php
/**
 * @var $content string Content to display.
 */
get_header('shop');
?>
<div id="primary" class="site-content">
	<div id="content" role="main">
		<?php echo $content; ?>
	</div>
</div>
<?php get_footer('shop'); ?>