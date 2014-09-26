<?php
/**
 * @var $content string Contelnt to display.
 */
get_header('shop');
?>
<div id="content" class="col-full">
	<div id="main" class="col-left">
		<div class="post jigoshop">
			<?php do_action('jigoshop\shop\content\before'); ?>
			<?php echo $content; ?>
			<?php do_action('jigoshop\shop\content\after'); ?>
		</div>
	</div>
</div>
<?php do_action('jigoshop\sidebar'); ?>
<?php get_sidebar('shop'); // TODO: Remove on implementation of jigoshop\sidebar ?>
<?php get_footer('shop'); ?>
