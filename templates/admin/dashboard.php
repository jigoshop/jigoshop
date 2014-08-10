<?php
/**
 * @var $submenu array Sub-menu items list.
 */
?>
<div id="jigoshop-metaboxes-main" class="wrap">
	<form action="admin-post.php" method="post">
		<h2><?php _e('Jigoshop Dashboard', 'jigoshop'); ?></h2>

		<p id="wp-version-message"><?php _e('You are using', 'jigoshop'); ?>
			<strong>Jigoshop <?php echo \Jigoshop\Core::VERSION; ?></strong>
		</p>

		<?php wp_nonce_field('jigoshop-metaboxes-main'); ?>
		<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
		<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>

		<div class="pages">
			<ul class="pages">
				<?php foreach ($submenu['jigoshop'] as $item): ?>
					<li><a href="<?php echo (strpos($item[2], 'edit.php') === false ? 'admin.php?page=' : '').$item[2]; ?>"><?php echo $item[0]; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container" style="width:50%;">
				<?php do_meta_boxes('jigoshop', 'side', null); ?>
			</div>
			<div id="post-body" class="has-sidebar">
				<div id="postbox-container-2" class="postbox-container" style="width:50%;">
					<?php do_meta_boxes('jigoshop', 'normal', null); ?>
				</div>
			</div>
			<br class="clear" />
		</div>
	</form>
</div>
<script type="text/javascript">
	//<![CDATA[
	jQuery(function($){
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('jigoshop');
	});
	//]]>
</script>