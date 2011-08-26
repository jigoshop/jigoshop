<?php
/**
 * Virtual Product Type
 * 
 * Functions specific to virtual products (for the write panels)
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Admin
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
  
/**
 * Product Options
 * 
 * Product Options for the virtual product type
 *
 * @since 		1.0
 */
function virtual_product_type_options() {
	?>
	<div id="virtual_product_options">
		<?php
			_e('Virtual products have no specific options.', 'jigoshop');
		?>
	</div>
	<?php
}
add_action('jigoshop_product_type_options_box', 'virtual_product_type_options');

/**
 * Product Type selector
 * 
 * Adds this product type to the product type selector in the product options meta box
 *
 * @since 		1.0
 *
 * @param 		string $product_type Passed the current product type so that if it keeps its selected state
 */
function virtual_product_type_selector( $product_type ) {
	
	echo '<option value="virtual" '; if ($product_type=='virtual') echo 'selected="selected"'; echo '>'.__('Virtual','jigoshop').'</option>';

}
add_action('product_type_selector', 'virtual_product_type_selector');