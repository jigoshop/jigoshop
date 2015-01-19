<?php
/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $fields array
 * @var $max float maximum price value
 */

use Jigoshop\Helper\Currency;

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<form method="get" action="">
	<div class="price_slider_wrapper">
		<div class="price_slider"></div>
		<div class="price_slider_amount">
			<button type="submit" class="button"><?php _e('Filter', 'jigoshop'); ?></button>
			<?php _e('Price: ', 'jigoshop'); ?><span></span>
			<input type="hidden" id="max_price" name="max_price" value="<?php echo esc_attr($max); ?>" />
			<input type="hidden" id="min_price" name="min_price" value="0" />
			<?php \Jigoshop\Helper\Forms::printHiddenFields($fields, array('max_price', 'min_price')); ?>
		</div>
		<div class="clear"></div>
	</div>
</form>
<script type="text/javascript">
	/*<![CDATA[*/
	jQuery(document).ready(function($){
		// Price slider
		var min_price = parseInt($('.price_slider_amount #min_price').val());
		var max_price = parseInt($('.price_slider_amount #max_price').val());
		var html = '<?php echo sprintf(Currency::format(), Currency::symbol(), Currency::code(), '%s%'); ?>';
		var current_min_price, current_max_price;
		current_min_price = min_price;
		current_max_price = max_price;
		$('.price_slider').slider({
			range: true,
			min: min_price,
			max: max_price,
			values: [min_price, max_price],
			create: function(){
				$(".price_slider_amount span").html(html.replace(/%s%/g, min_price) + " - " + html.replace(/%s%/g, max_price));
				$(".price_slider_amount #min_price").val(current_min_price);
				$(".price_slider_amount #max_price").val(current_max_price);
			},
			slide: function(event, ui){
				$(".price_slider_amount span").html(html.replace(/%s%/g, ui.values[0]) + " - " + html.replace(/%s%/g, ui.values[1]));
				$("input#min_price").val(ui.values[0]);
				$("input#max_price").val(ui.values[1]);
			}
		});
	});
	/*]]>*/
</script>
<?php echo $after_widget;
