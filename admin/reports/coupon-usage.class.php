<?php

class Jigoshop_Report_Coupon_Usage extends Jigoshop_Admin_Report
{
	public $chart_colours = array();
	public $coupon_codes = array();
	private $report_data;

	public function __construct()
	{
		if (isset($_GET['coupon_codes']) && is_array($_GET['coupon_codes'])) {
			$this->coupon_codes = array_filter(array_map('sanitize_text_field', $_GET['coupon_codes']));
		} elseif (isset($_GET['coupon_codes'])) {
			$this->coupon_codes = array_filter(array(sanitize_text_field($_GET['coupon_codes'])));
		}
	}

	public function get_chart_legend()
	{
		$legend = array();

		$query = array(
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
			'query_type' => 'get_results',
			'filter_range' => true,
			'order_types' => array('shop_order'),
		);

		if ($this->coupon_codes) {
			$query['data']['order_data']['where'] = array(
				'type' => 'object_comparison',
				'key' => 'order_discount_coupons',
				'value' => $this->coupon_codes,
				'operator' => 'intersection',
				'map' => function($item){
					return $item['code'];
				},
			);
		}

		$items = $this->get_order_report_data($query);
		$total_discount = array_sum(array_map(function($item){
			return $item->discount_amount;
		}, $items));
		$total_coupons = absint(array_sum(array_map(function($item){
			return $item->coupons_used;
		}, $items)));

		$legend[] = array(
			'title' => sprintf(__('%s discounts in total', 'jigoshop'), '<strong>'.jigoshop_price($total_discount).'</strong>'),
			'color' => $this->chart_colours['discount_amount'],
			'highlight_series' => 1
		);

		$legend[] = array(
			'title' => sprintf(__('%s coupons used in total', 'jigoshop'), '<strong>'.$total_coupons.'</strong>'),
			'color' => $this->chart_colours['coupon_count'],
			'highlight_series' => 0
		);

		return $legend;
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
			'discount_amount' => '#3498db',
			'coupon_count' => '#d4d9dc',
		);

		$current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : '7day';

		if (!in_array($current_range, array('custom', 'year', 'last_month', 'month', '7day', 'today'))) {
			$current_range = '7day';
		}

		$this->calculate_current_range($current_range);

		$template = jigoshop_locate_template('admin/reports/by-date');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	public function get_chart_widgets()
	{
		$widgets = array();

		$widgets[] = array(
			'title' => '',
			'callback' => array($this, 'coupons_widget')
		);

		return $widgets;
	}

	public function coupons_widget()
	{
		?>
		<h4 class="section_title"><span><?php _e('Filter by coupon', 'jigoshop'); ?></span></h4>
		<div class="section">
			<form method="GET">
				<div>
					<?php
					$data = $this->get_report_data();
					$used_coupons = array();
					foreach ($data as $coupons) {
						foreach ($coupons->coupons as $coupon) {
							if(!empty($coupon)){
								if (!isset($used_coupons[$coupon['code']])) {
									$used_coupons[$coupon['code']] = $coupon;
									$used_coupons[$coupon['code']]['usage'] = 0;
								}

								$used_coupons[$coupon['code']]['usage'] += $coupons->usage[$coupon['code']];
							}
						}
					}

					if ($used_coupons) :
					?>
						<select id="coupon_codes" name="coupon_codes" class="wc-enhanced-select" data-placeholder="<?php _e('Choose coupons&hellip;', 'jigoshop'); ?>" style="width:100%;">
							<option value=""><?php _e('All coupons', 'jigoshop'); ?></option>
							<?php
							foreach ($used_coupons as $coupon) {
								echo '<option value="'.esc_attr($coupon['code']).'" '.selected(in_array($coupon['code'], $this->coupon_codes), true, false).'>'.$coupon['code'].'</option>';
							}
							?>
						</select>
						<input type="submit" class="submit button" value="<?php _e('Show', 'jigoshop'); ?>" />
						<input type="hidden" name="range" value="<?php if (!empty($_GET['range'])) echo esc_attr($_GET['range']) ?>" />
						<input type="hidden" name="start_date" value="<?php if (!empty($_GET['start_date'])) echo esc_attr($_GET['start_date']) ?>" />
						<input type="hidden" name="end_date" value="<?php if (!empty($_GET['end_date'])) echo esc_attr($_GET['end_date']) ?>" />
						<input type="hidden" name="page" value="<?php if (!empty($_GET['page'])) echo esc_attr($_GET['page']) ?>" />
						<input type="hidden" name="tab" value="<?php if (!empty($_GET['tab'])) echo esc_attr($_GET['tab']) ?>" />
						<input type="hidden" name="report" value="<?php if (!empty($_GET['report'])) echo esc_attr($_GET['report']) ?>" />
					<?php else : ?>
						<span><?php _e('No used coupons found', 'jigoshop'); ?></span>
					<?php endif; ?>
				</div>
			</form>
		</div>
		<h4 class="section_title"><span><?php _e('Most Popular', 'jigoshop'); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$most_popular = $used_coupons;
				usort($most_popular, function($a, $b){
					return $b['usage'] - $a['usage'];
				});
				$most_popular = array_slice($most_popular, 0, 12);

				if ($most_popular) {
					foreach ($most_popular as $coupon) {
						echo '<tr class="'.(in_array($coupon['code'], $this->coupon_codes) ? 'active' : '').'">
							<td class="count" width="1%">'.$coupon['usage'].'</td>
							<td class="name"><a href="'.esc_url(add_query_arg('coupon_codes', $coupon['code'])).'">'.$coupon['code'].'</a></td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="2">'.__('No coupons found in range', 'jigoshop').'</td></tr>';
				}
				?>
			</table>
		</div>
		<h4 class="section_title"><span><?php _e('Most Discount', 'jigoshop'); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$most_discount = $used_coupons;
				usort($most_discount, function($a, $b){
					return $b['amount'] * $b['usage'] - $a['amount'] * $a['usage'];
				});
				$most_discount = array_slice($most_discount, 0, 12);

				if ($most_discount) {

					foreach ($most_discount as $coupon) {
						echo '<tr class="'.(in_array($coupon['code'], $this->coupon_codes) ? 'active' : '').'">
							<td class="count" width="1%">'.jigoshop_price($coupon['amount'] * $coupon['usage']).'</td>
							<td class="name"><a href="'.esc_url(add_query_arg('coupon_codes', $coupon['code'])).'">'.$coupon['code'].'</a></td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="3">'.__('No coupons found in range', 'jigoshop').'</td></tr>';
				}
				?>
			</table>
		</div>
		<script type="text/javascript">
			jQuery(function($){
				$('.section_title').click(function(){
					var next_section = $(this).next('.section');
					if($(next_section).is(':visible'))
						return false;
					$('.section:visible').slideUp();
					$('.section_title').removeClass('open');
					$(this).addClass('open').next('.section').slideDown();
					return false;
				});
				$('.section').slideUp(100, function(){
					<?php if ( empty( $this->coupon_codes ) ) : ?>
					$('.section_title:eq(1)').click();
					<?php else : ?>
					$('.section_title:eq(0)').click();
					<?php endif; ?>
				});
			});
		</script>
		<?php
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
		$this->report_data = $this->get_order_report_data(array(
			'data' => array(
				'order_data' => array(
					'type' => 'meta',
					'name' => 'order_coupons',
					'process' => true,
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'query_type' => 'get_results',
			'filter_range' => false,
			'order_types' => array('shop_order'),
		));
	}

	public function get_export_button()
	{
		$current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : '7day';
		?>
		<a href="#" download="report-<?php echo esc_attr($current_range); ?>-<?php echo date_i18n('Y-m-d', current_time('timestamp')); ?>.csv" class="export_csv" data-export="chart" data-xaxes="<?php _e('Date', 'jigoshop'); ?>" data-groupby="<?php echo $this->chart_groupby; ?>">
			<?php _e('Export CSV', 'jigoshop'); ?>
		</a>
	<?php
	}

	public function get_main_chart()
	{
		global $wp_locale;

		$data = $this->get_report_data();

		$coupon_codes = $this->coupon_codes;
		if(!empty($coupon_codes[0])){
			$data = array_filter($data, function($item) use ($coupon_codes)	{
				return isset($item->usage[$coupon_codes[0]]);
			});
		};


		$order_coupon_counts = array_map(function($item) use ($coupon_codes){
			$time = new stdClass();
			$time->post_date = $item->post_date;
			if(!empty($coupon_codes))
			{
				$time->order_coupon_count = $item->usage[$coupon_codes[0]];
			} else {
				$time->order_coupon_count = count($item->coupons);
			}

			return $time;
		}, $data);
		$order_discount_amounts = array_map(function($item) use ($coupon_codes){
			$time = new stdClass();
			$time->post_date = $item->post_date;
			if(!empty($item->coupons)){
				$time->discount_amount = array_sum(array_map(function($inner_item) use ($item, $coupon_codes){
					if(empty($inner_item)){
						return 0;
					}
					if(!empty($coupon_codes[0])) {
						return $coupon_codes[0] == $inner_item['code'] ? $item->usage[$inner_item['code']] * $inner_item['amount'] : 0;
					} else {
						return $item->usage[$inner_item['code']] * $inner_item['amount'];
					}
				}, $item->coupons));
			} else {
				$time->discount_amount = 0;
			}


			return $time;
		}, $data);

		$start_time = $this->start_date;
		$end_time = $this->end_date;
		$filter_times = function($item) use ($start_time, $end_time){
			$time = strtotime($item->post_date);
			return $time >= $start_time && $time < $end_time;
		};

		$order_coupon_counts = array_filter($order_coupon_counts, $filter_times);
		$order_discount_amounts = array_filter($order_discount_amounts, $filter_times);

		// Prepare data for report
		$order_coupon_counts = $this->prepare_chart_data($order_coupon_counts, 'post_date', 'order_coupon_count', $this->chart_interval, $this->start_date, $this->chart_groupby);
		$order_discount_amounts = $this->prepare_chart_data($order_discount_amounts, 'post_date', 'discount_amount', $this->chart_interval, $this->start_date, $this->chart_groupby);

		// Encode in json format
		$chart_data = json_encode(array(
			'order_coupon_counts' => array_values($order_coupon_counts),
			'order_discount_amounts' => array_values($order_discount_amounts)
		));
		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">
			var main_chart;

			jQuery(function($){
				var order_data = $.parseJSON('<?php echo $chart_data; ?>');
				var drawGraph = function(highlight){
					var series = [
						{
							label: "<?php echo esc_js(__('Number of coupons used', 'jigoshop')) ?>",
							data: order_data.order_coupon_counts,
							color: '<?php echo $this->chart_colours['coupon_count' ]; ?>',
							bars: {
								fillColor: '<?php echo $this->chart_colours['coupon_count' ]; ?>',
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
							label: "<?php echo esc_js(__('Discount amount', 'jigoshop')) ?>",
							data: order_data.order_discount_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['discount_amount']; ?>',
							points: {show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 4, fill: false},
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
								monthNames: <?php echo json_encode(array_values($wp_locale->month_abbrev)) ?>,
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
									color: '#ecf0f1',
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
