<?php
/**
 * Product Type
 * 
 * Function for displaying the product type meta (specific) meta boxes.
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

foreach(glob( dirname(__FILE__)."/product-types/*.php" ) as $filename) include_once($filename);

/**
 * Product type meta box
 * 
 * Display the product type meta box which contains a hook for product types to hook into and show their options
 *
 * @since 		1.0
 */
function jigoshop_product_type_options_box() {

	global $post;
	?>
	<div id="simple_product_options" class="panel jigoshop_options_panel">
		<?php
			_e('Simple products have no specific options.', 'jigoshop');
		?>
	</div>
	<?php 
	do_action('jigoshop_product_type_options_box');
}