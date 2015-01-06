<?php
use Jigoshop\Admin\Settings;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $orders array List of orders to display.
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
			<div id="postbox-container-1" class="postbox-container" style="width:50%;">
				<?php do_meta_boxes('jigoshop-reports', 'side', null); ?>
			</div>
			<div id="post-body" class="has-sidebar">
				<div id="postbox-container-2" class="postbox-container" style="width:50%;">
					<?php do_meta_boxes('jigoshop-reports', 'normal', null); ?>
				</div>
			</div>
			<?php do_action('jigoshop\admin\reports\widgets', $orders); ?>
		</div>
	</div>
</div>
</div>
