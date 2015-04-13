<?php

class Jigoshop_Report_Customers extends Jigoshop_Admin_Report
{
	public $chart_colours = array();
	public $customers = array();

	public function get_chart_legend()
	{
		$legend = array();

		$legend[] = array(
			'title' => sprintf(__('%s signups in this period', 'jigoshop'), '<strong>'.sizeof($this->customers).'</strong>'),
			'color' => $this->chart_colours['signups'],
			'highlight_series' => 2
		);

		return $legend;
	}

	public function get_chart_widgets()
	{
		$widgets = array();

		$widgets[] = array(
			'title' => '',
			'callback' => array($this, 'customers_vs_guests')
		);

		return $widgets;
	}

	public function customers_vs_guests()
	{
		$customer_order_totals = $this->get_order_report_data(array(
			'data' => array(
				'ID' => array(
					'type' => 'post_data',
					'function' => 'COUNT',
					'name' => 'total_orders'
				)
			),
			'where_meta' => array(
				array(
					'meta_key' => 'customer_user',
					'meta_value' => '0',
					'operator' => '>'
				)
			),
			'filter_range' => true,
			'order_types' => array('shop_order'),
		));

		$guest_order_totals = $this->get_order_report_data(array(
			'data' => array(
				'ID' => array(
					'type' => 'post_data',
					'function' => 'COUNT',
					'name' => 'total_orders'
				)
			),
			'where_meta' => array(
				array(
					'meta_key' => 'customer_user',
					'meta_value' => '0',
					'operator' => '='
				)
			),
			'filter_range' => true,
			'order_types' => array('shop_order'),
		));
		?>
		<div class="chart-container">
			<div class="chart-placeholder customers_vs_guests pie-chart" style="height:200px"></div>
			<ul class="pie-chart-legend">
				<li style="border-color: <?php echo $this->chart_colours['customers']; ?>"><?php _e('Customer Sales', 'jigoshop'); ?></li>
				<li style="border-color: <?php echo $this->chart_colours['guests']; ?>"><?php _e('Guest Sales', 'jigoshop'); ?></li>
			</ul>
		</div>
		<script type="text/javascript">
			jQuery(function($){
				var $plot = $('.chart-placeholder.customers_vs_guests');
 				$.plot(
					$plot,
					[
						{
							label: '<?php _e( 'Customer Orders', 'jigoshop' ); ?>',
							data:  "<?php echo $customer_order_totals->total_orders ?>",
							color: '<?php echo $this->chart_colours['customers']; ?>'
						},
						{
							label: '<?php _e( 'Guest Orders', 'jigoshop' ); ?>',
							data:  "<?php echo $guest_order_totals->total_orders ?>",
							color: '<?php echo $this->chart_colours['guests']; ?>'
						}
					],
					{
						grid: {
							hoverable: true
						},
						series: {
							pie: {
								show: true,
								radius: 1,
								innerRadius: 0.6,
								label: {
									show: false
								}
							},
							enable_tooltip: true,
							append_tooltip: "<?php echo ' ' . __( 'orders', 'jigoshop' ); ?>"
						},
						legend: {
							show: false
						}
					}
				);

				$plot.resize();
			});
		</script>
		<?php
	}

	public function output()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$ranges = array(
			'year' => __('Year', 'jigoshop'),
			'last_month' => __('Last Month', 'jigoshop'),
			'month' => __('This Month', 'jigoshop'),
			'7day' => __('Last 7 Days', 'jigoshop'),
			'today' => __('Today', 'jigoshop'),
		);

		$this->chart_colours = array(
			'signups' => '#3498db',
			'customers' => '#1abc9c',
			'guests' => '#8fdece'
		);

		$current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : '7day';
		if (!in_array($current_range, array('custom', 'year', 'last_month', 'month', '7day', 'today'))) {
			$current_range = '7day';
		}

		$this->calculate_current_range($current_range);

		$admin_users = new WP_User_Query(
			array(
				'role' => 'administrator',
				'fields' => 'ID'
			)
		);

		$manager_users = new WP_User_Query(
			array(
				'role' => 'shop_manager',
				'fields' => 'ID'
			)
		);

		$users_query = new WP_User_Query(
			array(
				'fields' => array('user_registered'),
				'exclude' => array_merge($admin_users->get_results(), $manager_users->get_results())
			)
		);

		$this->customers = $users_query->get_results();

		foreach ($this->customers as $key => $customer) {
			if (strtotime($customer->user_registered) < $this->start_date || strtotime($customer->user_registered) > $this->end_date) {
				unset($this->customers[$key]);
			}
		}

		$template = jigoshop_locate_template('admin/reports/by-date');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	public function get_export_button()
	{
		$current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : '7day';
		?>
		<a href="#" download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo date_i18n( 'Y-m-d', current_time('timestamp') ); ?>.csv" class="export_csv" data-export="chart" data-xaxes="<?php _e( 'Date', 'jigoshop' ); ?>" data-groupby="<?php echo $this->chart_groupby; ?>">
			<?php _e( 'Export CSV', 'jigoshop' ); ?>
		</a>
		<?php
	}

	public function get_main_chart()
	{
		global $wp_locale;

		$customer_orders = $this->get_order_report_data(array(
			'data' => array(
				'ID' => array(
					'type' => 'post_data',
					'function' => 'COUNT',
					'name' => 'total_orders'
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'where_meta' => array(
				array(
					'meta_key' => '_customer_user',
					'meta_value' => '0',
					'operator' => '>'
				)
			),
			'group_by' => $this->group_by_query,
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true
		));

		$guest_orders = $this->get_order_report_data(array(
			'data' => array(
				'ID' => array(
					'type' => 'post_data',
					'function' => 'COUNT',
					'name' => 'total_orders'
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'where_meta' => array(
				array(
					'meta_key' => '_customer_user',
					'meta_value' => '0',
					'operator' => '='
				)
			),
			'group_by' => $this->group_by_query,
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true
		));

		$signups = $this->prepare_chart_data($this->customers, 'user_registered', '', $this->chart_interval, $this->start_date, $this->chart_groupby);
		$customer_orders = $this->prepare_chart_data($customer_orders, 'post_date', 'total_orders', $this->chart_interval, $this->start_date, $this->chart_groupby);
		$guest_orders = $this->prepare_chart_data($guest_orders, 'post_date', 'total_orders', $this->chart_interval, $this->start_date, $this->chart_groupby);

		// Encode in json format
		$chart_data = json_encode(array(
			'signups' => array_values($signups),
			'customer_orders' => array_values($customer_orders),
			'guest_orders' => array_values($guest_orders)
		));
		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">
			var main_chart;

			jQuery(function($){
				var chart_data = $.parseJSON('<?php echo $chart_data; ?>');
				var drawGraph = function(highlight){
					var series = [
						{
							label: "<?php echo esc_js( __( 'Customer Orders', 'jigoshop' ) ) ?>",
							data: chart_data.customer_orders,
							color: '<?php echo $this->chart_colours['customers']; ?>',
							bars: {
								fillColor: '<?php echo $this->chart_colours['customers']; ?>',
								fill: true,
								show: true,
								lineWidth: 0,
								align: 'center',
								barWidth: 0<?php echo $this->barwidth; ?> * 0.5
							},
							shadowSize: 0,
							enable_tooltip: true,
							append_tooltip: "<?php echo ' ' . __( 'customer orders', 'jigoshop' ); ?>",
							stack: true
						},
						{
							label: "<?php echo esc_js( __( 'Guest Orders', 'jigoshop' ) ) ?>",
							data: chart_data.guest_orders,
							color: '<?php echo $this->chart_colours['guests']; ?>',
							bars: {
								fillColor: '<?php echo $this->chart_colours['guests']; ?>',
								fill: true,
								show: true,
								lineWidth: 0,
								align: 'center',
								barWidth: 0<?php echo $this->barwidth; ?> * 0.5
							},
							shadowSize: 0,
							enable_tooltip: true,
							append_tooltip: "<?php echo ' ' . __( 'guest orders', 'jigoshop' ); ?>",
							stack: true
						},
						{
							label: "<?php echo esc_js( __( 'Signups', 'jigoshop' ) ) ?>",
							data: chart_data.signups,
							color: '<?php echo $this->chart_colours['signups']; ?>',
							points: {show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 4, fill: false},
							shadowSize: 0,
							enable_tooltip: true,
							append_tooltip: "<?php echo ' ' . __( 'new users', 'jigoshop' ); ?>",
							stack: false
						}
					];
					if(highlight !== 'undefined' && series[highlight]){
						highlight_series = series[highlight];
						highlight_series.color = '#98c242';
						if(highlight_series.bars)
							highlight_series.bars.fillColor = '#98c242';
						if(highlight_series.lines){
							highlight_series.lines.lineWidth = 5;
						}
					}
					main_chart = $.plot(
						$('.chart-placeholder.main'),
						series,
						{
							legend: {
								show: false
							},
							grid: {
								color: '#aaa',
								borderColor: 'transparent',
								borderWidth: 0,
								hoverable: true
							},
							xaxes: [{
								color: '#aaa',
								position: "bottom",
								tickColor: 'transparent',
								mode: "time",
								timeformat: "<?php if ($this->chart_groupby == 'hour') {echo '%H';} elseif ($this->chart_groupby == 'day') {echo '%d %b';} else {echo '%b';} ?>",
								<?php if ($this->chart_groupby == 'hour'): ?>
								min: 0,
								max: 24*3600000,
								<?php endif; ?>
								monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
								tickLength: 1,
								minTickSize: [1, "<?php echo $this->chart_groupby; ?>"],
								tickSize: [1, "<?php echo $this->chart_groupby; ?>"],
								font: {
									color: "#aaa"
								}
							}],
							yaxes: [
								{
									min: 0,
									minTickSize: 1,
									tickDecimals: 0,
									color: '#ecf0f1',
									font: {color: "#aaa"}
								}
							]
						}
					);
					$('.chart-placeholder').resize();
				};

				drawGraph();
				$('.highlight_series').hover(
					function(){
						drawGraph($(this).data('series'));
					},
					function(){
						drawGraph();
					}
				);
			});
		</script>
		<?php
	}
}
