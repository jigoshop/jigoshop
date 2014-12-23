<?php
if(!defined('JIGOSHOP_CART_FAVICON_COUNT_DIR'))
{
	define('JIGOSHOP_CART_FAVICON_COUNT_DIR', dirname(__FILE__));
}
// Define plugin URL for assets
if(!defined('JIGOSHOP_CART_FAVICON_COUNT_URL'))
{
	define('JIGOSHOP_CART_FAVICON_COUNT_URL', plugins_url('', __FILE__));
}

function init_cart_favicon()
{
	if(class_exists('jigoshop'))
	{
		load_plugin_textdomain('jigoshop_cart_favicon_count', false, dirname(plugin_basename(__FILE__)).'/languages/');

		// Set up class loaders
		require_once(JIGOSHOP_CART_FAVICON_COUNT_DIR.'/src/Jigoshop/Extension/CartFaviconCount.php');
		new \Jigoshop\Extension\CartFaviconCount();
	}
}
add_action('jigoshop_initialize_plugins', 'init_cart_favicon');
