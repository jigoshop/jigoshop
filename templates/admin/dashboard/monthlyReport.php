<?php
/**
 * @var $currentMonth int Current month number.
 * @var $selectedMonth int Selected month number.
 * @var $nextYear int Year number for next month.
 * @var $previousYear int Year number for previous month.
 * @var $nextMonth int Month number for next month.
 * @var $previousMonth int Month number for previous month.
 * @var $orderCounts array Order counts for each day to display in chart.
 * @var $orderAmounts array Order amounts for each day to display in chart.
 */
// TODO: Improve links to use constants
?>
<div class="stats" id="jigoshop-stats">
	<p>
		<?php if ($currentMonth != $selectedMonth) : ?>
			<a href="admin.php?page=jigoshop&amp;month=<?php echo $nextMonth; ?>&amp;year=<?php echo $nextYear; ?>" class="next"><?php _e('Next Month &rarr;', 'jigoshop'); ?></a>
		<?php endif; ?>
		<a href="admin.php?page=jigoshop&amp;month=<?php echo $previousMonth; ?>&amp;year=<?php echo $previousYear; ?>" class="previous"><?php _e('&larr; Previous Month', 'jigoshop'); ?></a>
	</p>

	<div class="inside">
		<div id="jigoshop-monthly-report" style="width:100%; height:300px; position:relative;"></div>
		<script type="text/javascript">
			/* <![CDATA[ */
			// TODO: Move JavaScript code into separate file.
			"use strict";
			jQuery(function($){
				function weekendAreas(axes){
					var markings = [];
					var d = new Date(axes.xaxis.min);
					// go to the first Saturday
					d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7));
					d.setUTCSeconds(0);
					d.setUTCMinutes(0);
					d.setUTCHours(0);
					var i = d.getTime();
					do {
						// when we don't set yaxis, the rectangle automatically
						// extends to infinity upwards and downwards
						markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
						i += 7 * 24 * 60 * 60 * 1000;
					} while(i < axes.xaxis.max);
					return markings;
				}

				var d = <?php echo json_encode($orderCounts); ?>;
				// TODO: Think if this adding is required
//				for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
				var d2 = <?php echo json_encode($orderAmounts); ?>;
				// TODO: Think if this adding is required
//				for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;
				var $plot = $("#jigoshop-monthly-report");
				$.plot(
					$plot,
					[
						{ label: "<?php echo __('Number of sales','jigoshop'); ?>", data: d },
						{ label: "<?php echo\ __('Sales amount','jigoshop'); ?>", data: d2, yaxis: 2 }
					],
					{
						series: {
							lines: { show: true },
							points: { show: true }
						},
						grid: {
							show: true,
							aboveData: false,
							color: '#ccc',
							backgroundColor: '#fff',
							borderWidth: 2,
							borderColor: '#ccc',
							clickable: false,
							hoverable: true,
							markings: weekendAreas
						},
						xaxis: {
							mode: "time",
							timeformat: "%d %b",
							tickLength: 1,
							minTickSize: [1, "day"]
						},
						yaxes: [
							{ min: 0, tickSize: 1, tickDecimals: 0 },
							{ position: "right", min: 0, tickDecimals: 2 }
						],
						colors: ["#21759B", "#ed8432"]
					}
				);
				function showTooltip(x, y, contents){
					jQuery('<div id="tooltip">' + contents + '</div>').css({
						position: 'absolute',
						display: 'none',
						top: y + 5,
						left: x + 5,
						border: '1px solid #fdd',
						padding: '2px',
						'background-color': '#fee',
						opacity: 0.80
					}).appendTo("body").fadeIn(200);
				}

				var previousPoint = null;
				$plot.bind("plothover", function(event, pos, item){
					if(item){
						if(previousPoint != item.dataIndex){
							var y;
							previousPoint = item.dataIndex;
							$("#tooltip").remove();
							if(item.series.label == "<?php echo __('Number of sales','jigoshop'); ?>"){
								y = item.datapoint[1];
								showTooltip(item.pageX, item.pageY, item.series.label + " - " + y);
							} else {
								y = item.datapoint[1].toFixed(2);
								showTooltip(item.pageX, item.pageY, item.series.label + " - <?php //echo get_jigoshop_currency_symbol(); ?>" + y);
							}
						}
					} else {
						$("#tooltip").remove();
						previousPoint = null;
					}
				});
			});
			/* ]]> */
		</script>
	</div>
</div>
