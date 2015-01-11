<?php
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $query array Terms fetching query.
 * @var $terms array Terms to display.
 * @var $value string Current value.
 * @var $walker \Jigoshop\Web\CategoryWalker Walker to traverse list.
 * @var $shopUrl string URL to the shop main page.
 */

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<select name="<?php echo Types::PRODUCT_CATEGORY; ?>" id="dropdown_product_category">
	<option value="" <?php Forms::selected($value, ''); ?>><?php _e('View all categories', 'jigoshop'); ?></option>
	<?php echo $walker->walk($terms, 0, $query); ?>
</select>
<script type='text/javascript'>
	jQuery(function($){
		$('#dropdown_product_category').on('change', function(event){
			var url = $('option:selected', $(event.target)).data('url');
			if (url !== undefined){
				window.location.href = url;
			} else {
				window.location.href = "<?php echo $shopUrl; ?>";
			}
		});
	});
</script>
<?php echo $after_widget;
