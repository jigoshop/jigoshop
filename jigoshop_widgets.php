<?php
/**
 * Jigoshop Widgets
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Core
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

foreach(glob( dirname(__FILE__)."/widgets/*.php" ) as $filename) include_once($filename);

function jigoshop_register_widgets() {
	register_widget('Jigoshop_Widget_Recent_Products');
	register_widget('Jigoshop_Widget_Featured_Products');
	register_widget('Jigoshop_Widget_Product_Categories');
	register_widget('Jigoshop_Widget_Tag_Cloud');
	register_widget('Jigoshop_Widget_Cart');
	register_widget('Jigoshop_Widget_Layered_Nav');
	register_widget('Jigoshop_Widget_Price_Filter');
	register_widget('Jigoshop_Widget_Product_Search');
}
add_action('widgets_init', 'jigoshop_register_widgets');