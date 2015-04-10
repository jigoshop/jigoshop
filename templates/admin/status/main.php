<?php
if (!defined('ABSPATH')) {
	exit;
}

$current_tab = !empty($_REQUEST['tab']) ? sanitize_title($_REQUEST['tab']) : 'status';
?>
<div class="wrap jigoshop">
	<div class="icon32 icon32-jigoshop-status" id="icon-jigoshop"><br /></div><h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php
		$tabs = array(
			'status' => __('System Status', 'jigoshop'),
			'tools' => __('Tools', 'jigoshop'),
			'logs' => __('Logs', 'jigoshop'),
		);
		foreach ($tabs as $name => $label) {
			echo '<a href="'.admin_url('admin.php?page=jigoshop_system_info&tab='.$name).'" class="nav-tab ';
			if ($current_tab == $name) {
				echo 'nav-tab-active';
			}
			echo '">'.$label.'</a>';
		}
		?>
	</h2><br/>
	<?php
	switch ($current_tab) {
		case "tools":
			Jigoshop_Admin_Status::status_tools();
			break;
		case "logs":
			Jigoshop_Admin_Status::status_logs();
			break;
		default:
			Jigoshop_Admin_Status::status_report();
			break;
	}
	?>
</div>
