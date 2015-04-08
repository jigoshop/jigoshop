<?php

class Jigoshop_Report_Sales_By_Date extends Jigoshop_Admin_Report
{
	public $chart_colours = array();
	private $report_data;

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array
	 */
	public function get_chart_legend()
	{
		$legend = array();
		$data = $this->get_report_data();

		switch ($this->chart_groupby) {
			case 'day' :
				/** @noinspection PhpUndefinedFieldInspection */
				$average_sales_title = sprintf(__('%s average daily sales', 'jigoshop'), '<strong>'.jigoshop_price($data->average_sales).'</strong>');
				break;
			case 'month' :
			default :
			/** @noinspection PhpUndefinedFieldInspection */
				$average_sales_title = sprintf(__('%s average monthly sales', 'jigoshop'), '<strong>'.jigoshop_price($data->average_sales).'</strong>');
				break;
		}

		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s gross sales in this period', 'jigoshop'), '<strong>'.jigoshop_price($data->total_sales).'</strong>'),
			'placeholder' => __('This is the sum of the order totals including shipping and taxes.', 'jigoshop'),
			'color' => $this->chart_colours['sales_amount'],
			'highlight_series' => 6
		);
		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s net sales in this period', 'jigoshop'), '<strong>'.jigoshop_price($data->net_sales).'</strong>'),
			'placeholder' => __('This is the sum of the order totals excluding shipping and taxes.', 'jigoshop'),
			'color' => $this->chart_colours['net_sales_amount'],
			'highlight_series' => 7
		);
		$legend[] = array(
			'title' => $average_sales_title,
			'color' => $this->chart_colours['average'],
			'highlight_series' => 2
		);
		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s orders placed', 'jigoshop'), '<strong>'.$data->total_orders.'</strong>'),
			'color' => $this->chart_colours['order_count'],
			'highlight_series' => 1
		);

		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s items purchased', 'jigoshop'), '<strong>'.$data->total_items.'</strong>'),
			'color' => $this->chart_colours['item_count'],
			'highlight_series' => 0
		);

		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s charged for shipping', 'jigoshop'), '<strong>'.jigoshop_price($data->total_shipping).'</strong>'),
			'color' => $this->chart_colours['shipping_amount'],
			'highlight_series' => 5
		);
		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s worth of coupons used', 'jigoshop'), '<strong>'.jigoshop_price($data->total_coupons).'</strong>'),
			'color' => $this->chart_colours['coupon_amount'],
			'highlight_series' => 3
		);

		return $legend;
	}

	/**
	 * Get report data
	 *
	 * @return array
	 */
	public function get_report_data()
	{
		if (empty($this->report_data)) {
			$this->query_report_data();
		}

		return $this->report_data;
	}

	/**
	 * Get all data needed for this report and store in the class
	 */
	private function query_report_data()
	{
		$this->report_data = new stdClass;

		$this->report_data->orders = (array)$this->get_order_report_data(array(
			'data' => array(
				'order_data' => array(
					'type' => 'meta',
					'name' => 'order_data',
					'process' => true,
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'group_by' => $this->group_by_query,
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true,
			'order_types' => array('shop_order'),
			'order_status' => array('completed', 'processing', 'on-hold'),
			'parent_order_status' => array('completed', 'processing', 'on-hold'),
		));

		$this->report_data->order_counts = (array)$this->get_order_report_data(array(
			'data' => array(
				'ID' => array(
					'type' => 'post_data',
					'function' => 'COUNT',
					'name' => 'count',
					'distinct' => true,
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				)
			),
			'group_by' => $this->group_by_query,
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true,
			'order_types' => array('shop_order'),
			'order_status' => array('completed', 'processing', 'on-hold')
		));

		$this->report_data->coupons = (array)$this->get_order_report_data(array(
			'data' => array(
				'order_data' => array(
					'type' => 'meta',
					'name' => 'discount_amount',
					'process' => true,
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'group_by' => $this->group_by_query,
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true,
			'order_types' => array('shop_order'),
			'order_status' => array('completed', 'processing', 'on-hold'),
		));

		$this->report_data->order_items = (array)$this->get_order_report_data(array(
			'data' => array(
				'order_items' => array(
					'type' => 'meta',
					'name' => 'order_item_count',
					'process' => true,
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'group_by' => $this->group_by_query,
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true,
			'order_types' => array('shop_order'),
			'order_status' => array('completed', 'processing', 'on-hold'),
		));

		$this->report_data->total_sales = jigoshop_format_decimal(array_sum(wp_list_pluck($this->report_data->orders, 'total_sales')), 2);
		$this->report_data->total_tax = jigoshop_format_decimal(array_sum(wp_list_pluck($this->report_data->orders, 'total_tax')), 2);
		$this->report_data->total_shipping = jigoshop_format_decimal(array_sum(wp_list_pluck($this->report_data->orders, 'total_shipping')), 2);
		$this->report_data->total_shipping_tax = jigoshop_format_decimal(array_sum(wp_list_pluck($this->report_data->orders, 'total_shipping_tax')), 2);
		$this->report_data->total_coupons = number_format(array_sum(wp_list_pluck($this->report_data->coupons, 'discount_amount')), 2);
		$this->report_data->total_orders = absint(array_sum(wp_list_pluck($this->report_data->order_counts, 'count')));
		$this->report_data->total_items = absint(array_sum(wp_list_pluck($this->report_data->order_items, 'order_item_count')) * -1);
		$this->report_data->average_sales = jigoshop_format_decimal($this->report_data->total_sales / ($this->chart_interval + 1), 2);
		$this->report_data->net_sales = jigoshop_format_decimal($this->report_data->total_sales - $this->report_data->total_shipping - $this->report_data->total_tax - $this->report_data->total_shipping_tax, 2);
	}

	/**
	 * Output the report
	 */
	public function output()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$ranges = array(
			'year' => __('Year', 'jigoshop'),
			'last_month' => __('Last Month', 'jigoshop'),
			'month' => __('This Month', 'jigoshop'),
			'7day' => __('Last 7 Days', 'jigoshop')
		);

		$this->chart_colours = array(
			'sales_amount' => '#b1d4ea',
			'net_sales_amount' => '#3498db',
			'average' => '#95a5a6',
			'order_count' => '#dbe1e3',
			'item_count' => '#ecf0f1',
			'shipping_amount' => '#5cc488',
			'coupon_amount' => '#f1c40f',
		);

		$current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : '7day';

		if (!in_array($current_range, array('custom', 'year', 'last_month', 'month', '7day'))) {
			$current_range = '7day';
		}

		$this->calculate_current_range($current_range);

		$template = jigoshop_locate_template('admin/reports/by-date');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	/**
	 * Output an export link
	 */
	public function get_export_button()
	{
		$current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : '7day';
		?>
		<a href="#" download="report-<?php echo esc_attr($current_range); ?>-<?php echo date_i18n('Y-m-d', current_time('timestamp')); ?>.csv" class="export_csv" data-export="chart" data-xaxes="<?php _e('Date', 'jigoshop'); ?>" data-exclude_series="2" data-groupby="<?php echo $this->chart_groupby; ?>">
			<?php _e('Export CSV', 'jigoshop'); ?>
		</a>
	<?php
	}

	/**
	 * Get the main chart
	 *
	 * @return string
	 */
	public function get_main_chart()
	{
		global $wp_locale;

		// Prepare data for report
		$order_counts = $this->prepare_chart_data($this->report_data->order_counts, 'post_date', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby);
		$order_item_counts = $this->prepare_chart_data($this->report_data->order_items, 'post_date', 'order_item_count', $this->chart_interval, $this->start_date, $this->chart_groupby);
		$order_amounts = $this->prepare_chart_data($this->report_data->orders, 'post_date', 'total_sales', $this->chart_interval, $this->start_date, $this->chart_groupby);
		$coupon_amounts = $this->prepare_chart_data($this->report_data->coupons, 'post_date', 'discount_amount', $this->chart_interval, $this->start_date, $this->chart_groupby);
		$shipping_amounts = $this->prepare_chart_data($this->report_data->orders, 'post_date', 'total_shipping', $this->chart_interval, $this->start_date, $this->chart_groupby);
		$shipping_tax_amounts = $this->prepare_chart_data($this->report_data->orders, 'post_date', 'total_shipping_tax', $this->chart_interval, $this->start_date, $this->chart_groupby);
		$tax_amounts = $this->prepare_chart_data($this->report_data->orders, 'post_date', 'total_tax', $this->chart_interval, $this->start_date, $this->chart_groupby);

		$net_order_amounts = array();

		foreach ($order_amounts as $order_amount_key => $order_amount_value) {
			$net_order_amounts[$order_amount_key] = $order_amount_value;
			$net_order_amounts[$order_amount_key][1] = $net_order_amounts[$order_amount_key][1] - $shipping_amounts[$order_amount_key][1] - $shipping_tax_amounts[$order_amount_key][1] - $tax_amounts[$order_amount_key][1];
		}

		// Encode in json format
		$chart_data = json_encode(array(
			'order_counts' => array_values($order_counts),
			'order_item_counts' => array_values($order_item_counts),
			'order_amounts' => array_map(array(
				$this,
				'round_chart_totals'
			), array_values($order_amounts)),
			'net_order_amounts' => array_map(array(
				$this,
				'round_chart_totals'
			), array_values($net_order_amounts)),
			'shipping_amounts' => array_map(array(
				$this,
				'round_chart_totals'
			), array_values($shipping_amounts)),
			'coupon_amounts' => array_map(array(
				$this,
				'round_chart_totals'
			), array_values($coupon_amounts)),
		));
		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">
			var main_chart;
			jQuery(function(){
				var order_data = jQuery.parseJSON('<?php echo $chart_data; ?>');
				var drawGraph = function(highlight){
					var series = [
						{
							label: "<?php echo esc_js( __( 'Number of items sold', 'jigoshop' ) ) ?>",
							data: order_data.order_item_counts,
							color: '<?php echo $this->chart_colours['item_count']; ?>',
							bars: {
								fillColor: '<?php echo $this->chart_colours['item_count']; ?>',
								fill: true,
								show: true,
								lineWidth: 0,
								align: 'center',
								barWidth: 0<?php echo $this->barwidth; ?> * 0.5
							},
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Number of orders', 'jigoshop' ) ) ?>",
							data: order_data.order_counts,
							color: '<?php echo $this->chart_colours['order_count']; ?>',
							bars: {
								fillColor: '<?php echo $this->chart_colours['order_count']; ?>',
								fill: true,
								show: true,
								lineWidth: 0,
								align: 'center',
								barWidth: 0<?php echo $this->barwidth; ?> *	0.5
							},
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Average sales amount', 'jigoshop' ) ) ?>",
							data: [[<?php echo min( array_keys( $order_amounts ) ); ?>, <?php echo $this->report_data->average_sales; ?>], [<?php echo max( array_keys( $order_amounts ) ); ?>, <?php echo $this->report_data->average_sales; ?>]],
							yaxis: 2,
							color: '<?php echo $this->chart_colours['average']; ?>',
							points: {show: false},
							lines: {show: true, lineWidth: 2, fill: false},
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Coupon amount', 'jigoshop' ) ) ?>",
							data: order_data.coupon_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['coupon_amount']; ?>',
							points: {show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 2, fill: false},
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); ?>
						},
						{
							label: "<?php echo esc_js( __( 'Shipping amount', 'jigoshop' ) ) ?>",
							data: order_data.shipping_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['shipping_amount']; ?>',
							points: {show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 2, fill: false},
							shadowSize: 0,
							prepend_tooltip: "<?php echo get_jigoshop_currency_symbol(); ?>"
						},
						{
							label: "<?php echo esc_js( __( 'Gross Sales amount', 'jigoshop' ) ) ?>",
							data: order_data.order_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['sales_amount']; ?>',
							points: {show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 2, fill: false},
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); ?>
						},
						{
							label: "<?php echo esc_js( __( 'Net Sales amount', 'jigoshop' ) ) ?>",
							data: order_data.net_order_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['net_sales_amount']; ?>',
							points: {show: true, radius: 6, lineWidth: 4, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 5, fill: false},
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); ?>
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
					main_chart = jQuery.plot(
						jQuery('.chart-placeholder.main'),
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
								timeformat: "<?php if ( $this->chart_groupby == 'day' ) {echo '%d %b';} else {echo '%b';} ?>",
								monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
								tickLength: 1,
								minTickSize: [1, "<?php echo $this->chart_groupby; ?>"],
								font: {
									color: "#aaa"
								}
							}],
							yaxes: [
								{
									min: 0,
									minTickSize: 1,
									tickDecimals: 0,
									color: '#d4d9dc',
									font: {color: "#aaa"}
								},
								{
									position: "right",
									min: 0,
									tickDecimals: 2,
									alignTicksWithAxis: 1,
									color: 'transparent',
									font: {color: "#aaa"}
								}
							]
						}
					);
					jQuery('.chart-placeholder').resize();
				};

				drawGraph();
				jQuery('.highlight_series').hover(
					function(){
						drawGraph(jQuery(this).data('series'));
					},
					function(){
						drawGraph();
					}
				);
			});
		</script>
	<?php
	}

	/**
	 * Round our totals correctly
	 *
	 * @param  string $amount
	 * @return string
	 */
	private function round_chart_totals($amount)
	{
		if (is_array($amount)) {
			return array_map(array($this, 'round_chart_totals'), $amount);
		} else {
			return jigoshop_format_decimal($amount, '');
		}
	}
}
