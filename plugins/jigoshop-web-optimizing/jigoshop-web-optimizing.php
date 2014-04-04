<?php

// Define plugin directory for inclusions
if(!defined('JIGOSHOP_WEB_OPTIMIZING_DIR'))
{
	define('JIGOSHOP_WEB_OPTIMIZING_DIR', dirname(__FILE__));
}
// Define cache directory
if(!defined('JIGOSHOP_WEB_OPTIMIZING_CACHE'))
{
	define('JIGOSHOP_WEB_OPTIMIZING_CACHE', JIGOSHOP_WEB_OPTIMIZING_DIR.DIRECTORY_SEPARATOR.'cache');
}
// Define plugin URL for assets
if(!defined('JIGOSHOP_WEB_OPTIMIZING_URL'))
{
	define('JIGOSHOP_WEB_OPTIMIZING_URL', plugins_url('', __FILE__));
}

function init_jigoshop_web_optimizing()
{
	if(class_exists('jigoshop') && Jigoshop_Base::get_options()->get_option('jigoshop_enable_jwof', 'no') == 'yes')
	{
		load_plugin_textdomain('jigoshop_web_optimizing', false, dirname(plugin_basename(__FILE__)).'/languages/');

		// Set up class loaders
		require_once(JIGOSHOP_WEB_OPTIMIZING_DIR.'/vendor/SplClassLoader.php');
		$loader = new SplClassLoader('Assetic', JIGOSHOP_WEB_OPTIMIZING_DIR.'/vendor/assetic/src');
		$loader->register();
		$loader = new SplClassLoader('Jigoshop\\Web', JIGOSHOP_WEB_OPTIMIZING_DIR.'/src');
		$loader->register();

		new \Jigoshop\Web\Optimizing();
	}
}
add_action('jigoshop_initialize_plugins', 'init_jigoshop_web_optimizing');

