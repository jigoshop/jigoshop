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
<!-- TODO: Move to CSS file -->
<style type="text/css">
	[class*="span"]{float:left;margin-left:20px;}
	.span3{width:220px;}
	.span2{width:140px;}
	.span1{width:60px;}
	table{max-width:100%;background-color:transparent;border-collapse:collapse;border-spacing:0;}
	.table{width:100%;margin-bottom:18px;}.table th,.table td{padding:8px;line-height:18px;text-align:left;vertical-align:top;border-top:1px solid #dddddd;}
	.table th{font-weight:bold;}
	.table thead th{vertical-align:bottom;}
	.table caption+thead tr:first-child th,.table caption+thead tr:first-child td,.table colgroup+thead tr:first-child th,.table colgroup+thead tr:first-child td,.table thead:first-child tr:first-child th,.table thead:first-child tr:first-child td{border-top:0;}
	.table tbody+tbody{border-top:2px solid #dddddd;}
	.table-condensed th,.table-condensed td{padding:4px 5px;}
	.table tbody tr:hover td,.table tbody tr:hover th{background-color:#f5f5f5;}
	h1,h2,h3,h4,h5,h6{margin:0;font-family:inherit;font-weight:bold;color:inherit;text-rendering:optimizelegibility;}h1 small,h2 small,h3 small,h4 small,h5 small,h6 small{font-weight:normal;color:#999999;}
	h1{font-size:30px;line-height:36px;}h1 small{font-size:18px;}
	h2{font-size:24px;line-height:36px;}h2 small{font-size:18px;}
	h3{font-size:18px;line-height:27px;}h3 small{font-size:14px;}
	h4,h5,h6{line-height:18px;}
	h4{font-size:14px;}h4 small{font-size:12px;}
	h5{font-size:12px;}
	h6{font-size:11px;color:#999999;text-transform:uppercase;}
	.thumbnail h4, .thumbnail h3, .thumbnail h2, .thumbnail h1 {text-align:center;}
	.thumbnail{display: block;padding: 4px;line-height: 1;border: 1px solid #DDD;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;-webkit-box-shadow: 0 1px 1px  rgba(0, 0, 0, 0.075);-moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075);box-shadow: 0 1px 1px  rgba(0, 0, 0, 0.075);}
</style>
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
