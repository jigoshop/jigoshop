<?php

class Jigoshop_Report_Sales_By_Product extends Jigoshop_Admin_Report
{
	public $chart_colours = array();
	public $product_ids = array();
	public $product_ids_titles = array();
	protected $report_data;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		if (isset($_GET['product_ids']) && is_array($_GET['product_ids'])) {
			$this->product_ids = array_filter(array_map('absint', $_GET['product_ids']));
		} elseif (isset($_GET['product_ids'])) {
			$this->product_ids = array_filter(array(absint($_GET['product_ids'])));
		}

		$this->report_data = new stdClass();
	}

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array
	 */
	public function get_chart_legend()
	{
		if (!$this->product_ids) {
			return array();
		}

		$legend = array();

		$this->report_data->order_item_counts = $this->get_order_report_data(array(
			'data' => array(
				'order_items' => array(
					'type' => 'meta',
					'name' => 'order_item_count',
					'process' => true,
					'where' => array(
						'type' => 'item_id',
						'keys' => array('id', 'variation_id'),
						'value' => $this->product_ids,
					),
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'order_types' => array('shop_order'),
			'query_type' => 'get_results',
			'filter_range' => true
		));

		$this->report_data->order_item_amounts = $this->get_order_report_data(array(
			'data' => array(
				'order_items' => array(
					'type' => 'meta',
					'name' => 'order_item_amount',
					'process' => true,
					'where' => array(
						'type' => 'item_id',
						'keys' => array('id', 'variation_id'),
						'value' => $this->product_ids,
					),
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'order_types' => array('shop_order'),
			'query_type' => 'get_results',
			'filter_range' => true,
		));

		$total_sales = array_sum(array_map(function($item){
			return $item->order_item_amount;
		}, $this->report_data->order_item_amounts));
		$total_items = array_sum(array_map(function($item){
			return $item->order_item_count;
		}, $this->report_data->order_item_counts));

		$legend[] = array(
			'title' => sprintf(__('%s sales for the selected items', 'jigoshop'), '<strong>'.jigoshop_price($total_sales).'</strong>'),
			'color' => $this->chart_colours['sales_amount'],
			'highlight_series' => 1
		);

		$legend[] = array(
			'title' => sprintf(__('%s purchases for the selected items', 'jigoshop'), '<strong>'.$total_items.'</strong>'),
			'color' => $this->chart_colours['item_count'],
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
			'7day' => __('Last 7 Days', 'jigoshop')
		);

		$this->chart_colours = array(
			'sales_amount' => '#3498db',
			'item_count' => '#d4d9dc',
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
	 * [get_chart_widgets description]
	 *
	 * @return array
	 */
	public function get_chart_widgets()
	{
		$widgets = array();

		if (!empty($this->product_ids)) {
			$widgets[] = array(
				'title' => __('Showing reports for:', 'jigoshop'),
				'callback' => array($this, 'current_filters')
			);
		}

		$widgets[] = array(
			'title' => '',
			'callback' => array($this, 'products_widget')
		);

		return $widgets;
	}

	/**
	 * Show current filters
	 */
	public function current_filters()
	{
		$this->product_ids_titles = array();
		foreach ($this->product_ids as $product_id) {
			$product = new jigoshop_product($product_id);

			if ($product) {
				$this->product_ids_titles[] = $product->get_title();
			} else {
				$this->product_ids_titles[] = '#'.$product_id;
			}
		}

		echo '<p>'.' <strong>'.implode(', ', $this->product_ids_titles).'</strong></p>';
		echo '<p><a class="button" href="'.esc_url(remove_query_arg('product_ids')).'">'.__('Reset', 'jigoshop').'</a></p>';
	}

	/**
	 * Product selection
	 */
	public function products_widget() {
		?>
		<h4 class="section_title"><span><?php _e('Product Search', 'jigoshop'); ?></span></h4>
		<div class="section">
			<form method="GET">
				<div>
					<input type="hidden" class="jigoshop-product-search" style="width:203px;" name="product_ids[]" data-placeholder="<?php _e('Search for a product&hellip;', 'jigoshop'); ?>" data-action="jigoshop_json_search_products_and_variations" />
					<input type="submit" class="submit button" value="<?php _e('Show', 'jigoshop'); ?>" />
					<input type="hidden" name="range" value="<?php if (!empty($_GET['range'])) echo esc_attr($_GET['range']) ?>" />
					<input type="hidden" name="start_date" value="<?php if (!empty($_GET['start_date'])) echo esc_attr($_GET['start_date']) ?>" />
					<input type="hidden" name="end_date" value="<?php if (!empty($_GET['end_date'])) echo esc_attr($_GET['end_date']) ?>" />
					<input type="hidden" name="page" value="<?php if (!empty($_GET['page'])) echo esc_attr($_GET['page']) ?>" />
					<input type="hidden" name="tab" value="<?php if (!empty($_GET['tab'])) echo esc_attr($_GET['tab']) ?>" />
					<input type="hidden" name="report" value="<?php if (!empty($_GET['report'])) echo esc_attr($_GET['report']) ?>" />
				</div>
			</form>
		</div>
		<h4 class="section_title"><span><?php _e('Top Sellers', 'jigoshop'); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$top_sellers = $this->get_order_report_data(array(
					'data' => array(
						'order_items' => array(
							'type' => 'meta',
							'name' => 'top_products',
							'process' => true,
							'limit' => 12,
							'order' => 'most_sold',
						),
					),
					'order_types' => array('shop_order'),
					'query_type' => 'get_results',
					'filter_range' => true,
				));

				if ($top_sellers) {
					foreach ($top_sellers as $product) {
						echo '<tr class="'.(in_array($product->product_id, $this->product_ids) ? 'active' : '').'">
							<td class="count">'.$product->order_item_qty.'</td>
							<td class="name"><a href="'.esc_url(add_query_arg('product_ids', $product->product_id)).'">'.get_the_title($product->product_id).'</a></td>
							<td class="sparkline">'.$this->sales_sparkline($product->product_id, 7, 'count').'</td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="3">'.__('No products found in range', 'jigoshop').'</td></tr>';
				}
				?>
			</table>
		</div>
		<h4 class="section_title"><span><?php _e('Top Freebies', 'jigoshop'); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$top_freebies = $this->get_order_report_data(array(
					'data' => array(
						'order_items' => array(
							'type' => 'meta',
							'name' => 'top_products',
							'process' => true,
							'where' => array(
								'type' => 'comparison',
								'key' => 'cost',
								'value' => '0',
								'operator' => '0'
							)
						),
					),
					'order_types' => array('shop_order'),
					'query_type' => 'get_results',
					'limit' => 12,
					'nocache' => true
				));

				if ($top_freebies) {
					foreach ($top_freebies as $product) {
						echo '<tr class="'.(in_array($product->product_id, $this->product_ids) ? 'active' : '').'">
							<td class="count">'.$product->order_item_qty.'</td>
							<td class="name"><a href="'.esc_url(add_query_arg('product_ids', $product->product_id)).'">'.get_the_title($product->product_id).'</a></td>
							<td class="sparkline">'.$this->sales_sparkline($product->product_id, 7, 'count').'</td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="3">'.__('No products found in range', 'jigoshop').'</td></tr>';
				}
				?>
			</table>
		</div>
		<h4 class="section_title"><span><?php _e('Top Earners', 'jigoshop'); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$top_earners = $this->get_order_report_data(array(
					'data' => array(
						'order_items' => array(
							'type' => 'meta',
							'name' => 'top_products',
							'process' => true,
							'limit' => 12,
							'order' => 'most_earned',
						),
					),
					'order_types' => array('shop_order'),
					'query_type' => 'get_results',
					'filter_range' => true
				));

				if ($top_earners) {
					foreach ($top_earners as $product) {
						echo '<tr class="'.(in_array($product->product_id, $this->product_ids) ? 'active' : '').'">
							<td class="count">'.jigoshop_price($product->order_item_total).'</td>
							<td class="name"><a href="'.esc_url(add_query_arg('product_ids', $product->product_id)).'">'.get_the_title($product->product_id).'</a></td>
							<td class="sparkline">'.$this->sales_sparkline($product->product_id, 7, 'sales').'</td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="3">'.__('No products found in range', 'jigoshop').'</td></tr>';
				}
				?>
			</table>
		</div>
		<script type="text/javascript">
			jQuery(function($){
				$('.section_title').click(function(){
					var next_section = $(this).next('.section');

					if ( $(next_section).is(':visible') )
						return false;

					$('.section:visible').slideUp();
					$('.section_title').removeClass('open');
					$(this).addClass('open').next('.section').slideDown();

					return false;
				});
				$('.section').slideUp( 100, function() {
					<?php if (empty($this->product_ids)): ?>
					$('.section_title:eq(1)').click();
					<?php endif; ?>
				});
			});
		</script>
		<?php
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

		if (!$this->product_ids) {
			?>
			<div class="chart-container">
				<p class="chart-prompt"><?php _e('&larr; Choose a product to view stats', 'jigoshop'); ?></p>
			</div>
		<?php
		} else {
			// Get orders and dates in range - we want the SUM of order totals, COUNT of order items, COUNT of orders, and the date
			$order_item_counts = $this->get_order_report_data(array(
				'data' => array(
					'_qty' => array(
						'type' => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function' => 'SUM',
						'name' => 'order_item_count'
					),
					'post_date' => array(
						'type' => 'post_data',
						'function' => '',
						'name' => 'post_date'
					),
					'_product_id' => array(
						'type' => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function' => '',
						'name' => 'product_id'
					)
				),
				'where_meta' => array(
					'relation' => 'OR',
					array(
						'type' => 'order_item_meta',
						'meta_key' => array('_product_id', '_variation_id'),
						'meta_value' => $this->product_ids,
						'operator' => 'IN'
					),
				),
				'group_by' => 'product_id,'.$this->group_by_query,
				'order_by' => 'post_date ASC',
				'query_type' => 'get_results',
				'filter_range' => true
			));

			$order_item_amounts = $this->get_order_report_data(array(
				'data' => array(
					'_line_total' => array(
						'type' => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function' => 'SUM',
						'name' => 'order_item_amount'
					),
					'post_date' => array(
						'type' => 'post_data',
						'function' => '',
						'name' => 'post_date'
					),
					'_product_id' => array(
						'type' => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function' => '',
						'name' => 'product_id'
					),
				),
				'where_meta' => array(
					'relation' => 'OR',
					array(
						'type' => 'order_item_meta',
						'meta_key' => array('_product_id', '_variation_id'),
						'meta_value' => $this->product_ids,
						'operator' => 'IN'
					),
				),
				'group_by' => 'product_id, '.$this->group_by_query,
				'order_by' => 'post_date ASC',
				'query_type' => 'get_results',
				'filter_range' => true
			));

			// Prepare data for report
			$order_item_counts = $this->prepare_chart_data($this->report_data->order_item_counts, 'post_date', 'order_item_count', $this->chart_interval, $this->start_date, $this->chart_groupby);
			$order_item_amounts = $this->prepare_chart_data($this->report_data->order_item_amounts, 'post_date', 'order_item_amount', $this->chart_interval, $this->start_date, $this->chart_groupby);

			// Encode in json format
			$chart_data = json_encode(array(
				'order_item_counts' => array_values($order_item_counts),
				'order_item_amounts' => array_values($order_item_amounts)
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
								label: "<?php echo esc_js(__('Number of items sold', 'jigoshop')) ?>",
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
								label: "<?php echo esc_js(__('Sales amount', 'jigoshop')) ?>",
								data: order_data.order_item_amounts,
								yaxis: 2,
								color: '<?php echo $this->chart_colours['sales_amount']; ?>',
								points: {show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true},
								lines: {show: true, lineWidth: 4, fill: false},
								shadowSize: 0,
								<?php echo $this->get_currency_tooltip(); ?>
							}
						];
						if(highlight !== 'undefined' && series[highlight]){
							highlight_series = series[highlight];
							highlight_series.color = '#9c5d90';
							if(highlight_series.bars)
								highlight_series.bars.fillColor = '#9c5d90';
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
}
