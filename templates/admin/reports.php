<?php
use Jigoshop\Admin\Settings;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $orders array List of orders to display.
 * @var $boxes array List of closures that displays each fragment of report page.
 * @var $start_date int Timestamp of report beginning.
 * @var $end_date int Timestamp of report ending.
 */
?>
<div id="jigoshop-metaboxes-reports" class="wrap jigoshop">
	<h1><?php _e('Jigoshop &rang; Reports', 'jigoshop'); ?></h1>
	<?php settings_errors(); ?>
	<?php Render::output('shop/messages', array('messages' => $messages)); ?>

	<?php wp_nonce_field('jigoshop-metaboxes-reports'); ?>
	<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
	<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>

	<div class="tab-content">
		<form method="post" action="admin.php?page=jigoshop_reports">
			<p>
				<label for="from"><?php _e('From:', 'jigoshop'); ?></label>
				<input class="date-pick" type="date" name="start_date" id="from" value="<?php echo esc_attr(date('Y-m-d', $start_date)); ?>" />
				<label for="to"><?php _e('To:', 'jigoshop'); ?></label>
				<input type="date" class="date-pick" name="end_date" id="to" value="<?php echo esc_attr(date('Y-m-d', $end_date)); ?>" />
				<?php do_action('jigoshop\admin\report\form'); ?>
				<input type="submit" class="button" value="<?php _e('Show', 'jigoshop'); ?>" />
			</p>
		</form>
		<div id="report-widgets" class="metabox-holder">
			<?php foreach ($boxes as $box){ $box(); } ?>
			<?php do_action('jigoshop\admin\reports\widgets', $orders); ?>
		</div>
	</div>
</div>
<!-- TODO: Move to JS file -->
<script type="text/javascript">
	jQuery(function($){
		$('.date-pick').datepicker({
			dateFormat: 'yy-mm-dd',
			gotoCurrent: true
		});
	});
</script>
