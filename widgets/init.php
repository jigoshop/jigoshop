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
 * @package		Jigoshop
 * @category	Core
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */
include_once( 'best-sellers.php' );
include_once( 'cart.php' );
include_once( 'featured-products.php' );
include_once( 'layered_nav.php' );
include_once( 'price-filter.php' );
include_once( 'product_categories.php' );
include_once( 'product_search.php' );
include_once( 'product_tag_cloud.php' );
include_once( 'recent_products.php' );
include_once( 'recent_reviews.php' );
include_once( 'recently_viewed.php' );
include_once( 'top-rated.php' );
include_once( 'user_login.php' );

function jigoshop_register_widgets() {
	register_widget('Jigoshop_Widget_Recent_Products');
	register_widget('Jigoshop_Widget_Featured_Products');
	register_widget('Jigoshop_Widget_Product_Categories');
	register_widget('Jigoshop_Widget_Tag_Cloud');
	register_widget('Jigoshop_Widget_Cart');
	register_widget('Jigoshop_Widget_Layered_Nav');
	register_widget('Jigoshop_Widget_Price_Filter');
	register_widget('Jigoshop_Widget_Product_Search');
	register_widget('Jigoshop_Widget_Top_Rated');
	register_widget('Jigoshop_Widget_User_Login');
	register_widget('Jigoshop_Widget_Recently_Viewed_Products');
	register_widget('Jigoshop_Widget_Recent_Reviews');
	register_widget('Jigoshop_Widget_Best_Sellers');
}
add_action('widgets_init', 'jigoshop_register_widgets');