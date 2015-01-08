<?php

// Define classes
use Jigoshop\Integration;
use Jigoshop\Widget;

class Jigoshop_Widget_Best_Sellers extends Widget\BestSellers {}
class Jigoshop_Widget_Cart extends Widget\Cart {}
class Jigoshop_Widget_Featured_Products extends Widget\FeaturedProducts {}
class Jigoshop_Widget_Product_Search extends Widget\ProductSearch {}
class Jigoshop_Widget_Products_On_Sale extends Widget\ProductsOnSale {}
class Jigoshop_Widget_Random_Products extends Widget\RandomProducts {}
class Jigoshop_Widget_Recent_Products extends Widget\RecentProducts {}
class Jigoshop_Widget_Top_Rated extends Widget\TopRated {}
class Jigoshop_Widget_User_Login extends Widget\UserLogin {}

add_action('widgets_init', function(){
	register_widget('Jigoshop_Widget_Best_Sellers');
	Jigoshop_Widget_Best_Sellers::setProductService(Integration::getProductService());
	register_widget('Jigoshop_Widget_Cart');
	Jigoshop_Widget_Cart::setCart(Integration::getCartService());
	Jigoshop_Widget_Cart::setOptions(Integration::getOptions());
	register_widget('Jigoshop_Widget_Featured_Products');
	Jigoshop_Widget_Featured_Products::setProductService(Integration::getProductService());
	register_widget('Jigoshop_Widget_Product_Search');
	register_widget('Jigoshop_Widget_Products_On_Sale');
	Jigoshop_Widget_Products_On_Sale::setProductService(Integration::getProductService());
	register_widget('Jigoshop_Widget_Random_Products');
	Jigoshop_Widget_Random_Products::setProductService(Integration::getProductService());
	register_widget('Jigoshop_Widget_Recent_Products');
	Jigoshop_Widget_Recent_Products::setProductService(Integration::getProductService());
	register_widget('Jigoshop_Widget_Top_Rated');
	Jigoshop_Widget_Top_Rated::setProductService(Integration::getProductService());
	register_widget('Jigoshop_Widget_User_Login');
	Jigoshop_Widget_User_Login::setOptions(Integration::getOptions());

//	register_widget('Jigoshop_Widget_Product_Categories');
//	register_widget('Jigoshop_Widget_Tag_Cloud');
//	register_widget('Jigoshop_Widget_Layered_Nav');
//	register_widget('Jigoshop_Widget_Price_Filter');
//	register_widget('Jigoshop_Widget_Recently_Viewed_Products');
//	register_widget('Jigoshop_Widget_Recent_Reviews');
});
