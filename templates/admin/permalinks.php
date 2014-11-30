<?php
use Jigoshop\Helper\Forms;

/**
 * @var $structures array
 * @var $permalink string
 * @var $shopPageId int
 * @var $base string
 * @var $productBase string
 * @var $homeUrl string
 */
?>
<table class="form-table">
	<tbody>
	<tr>
		<th><label><input name="product_permalink" type="radio" value="<?php echo $structures[0]; ?>" class="jigoshop-structure" <?php echo Forms::checked($structures[0], $permalink); ?> /> <?php _e('Default', 'jigoshop'); ?></label></th>
		<td><code><?php echo $homeUrl; ?>/?product=sample-product</code></td>
	</tr>
	<tr>
		<th><label><input name="product_permalink" type="radio" value="<?php echo $structures[1]; ?>" class="jigoshop-structure" <?php echo Forms::checked($structures[1], $permalink); ?> /> <?php _e('Product', 'jigoshop'); ?></label></th>
		<td><code><?php echo $homeUrl; ?>/<?php echo $productBase; ?>/sample-product/</code></td>
	</tr>
	<?php if ($shopPageId) : ?>
		<tr>
			<th><label><input name="product_permalink" type="radio" value="<?php echo $structures[2]; ?>" class="jigoshop-structure" <?php echo Forms::checked($structures[2], $permalink); ?> /> <?php _e('Shop base', 'jigoshop'); ?></label></th>
			<td><code><?php echo $homeUrl; ?>/<?php echo $base; ?>/sample-product/</code></td>
		</tr>
		<tr>
			<th><label><input name="product_permalink" type="radio" value="<?php echo $structures[3]; ?>" class="jigoshop-structure" <?php echo Forms::checked($structures[3], $permalink); ?> /> <?php _e('Shop base with category', 'jigoshop'); ?></label></th>
			<td><code><?php echo $homeUrl; ?>/<?php echo $base; ?>/product-category/sample-product/</code></td>
		</tr>
	<?php endif; ?>
	<tr>
		<th><label><input name="product_permalink" id="jigoshop_custom_selection" type="radio" value="custom" <?php echo Forms::checked(in_array($permalink, $structures), false); ?> /> <?php _e('Custom Base', 'jigoshop'); ?></label></th>
		<td>
			<input name="product_permalink_structure" id="jigoshop_permalink_structure" type="text" value="<?php echo $permalink; ?>" class="regular-text code">
			<span class="description"><?php _e('Enter a custom base to use. A base <strong>must</strong> be set or WordPress will use default instead.', 'jigoshop'); ?></span>
		</td>
	</tr>
	</tbody>
</table>
<script type="text/javascript">
	jQuery(function(){
		jQuery('input.jigoshop-structure').change(function(){
			jQuery('#jigoshop_permalink_structure').val(jQuery(this).val());
		});
		jQuery('#jigoshop_permalink_structure').focus(function(){
			jQuery('#jigoshop_custom_selection').click();
		});
	});
</script>
