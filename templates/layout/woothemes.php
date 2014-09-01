<?php
/**
 * @var $content string Contelnt to display.
 */
get_header('shop');
?>
<div id="content" class="col-full">
	<div id="main" class="col-left">
		<div class="post">
			<?php echo $content; ?>
		</div>
	</div>
</div>
<?php get_footer('shop'); ?>