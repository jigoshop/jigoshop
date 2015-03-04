<?php

// Define plugin directory for inclusions
if(!defined('JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_DIR'))
{
	define('JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_DIR', dirname(__FILE__));
}
// Define cache directory
if(!defined('JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_CACHE'))
{
	define('JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_CACHE', JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_DIR.DIRECTORY_SEPARATOR.'cache');
}
// Define plugin URL for assets
if(!defined('JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_URL'))
{
	define('JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_URL', plugins_url('', __FILE__));
}

function init_jigoshop_web_optimization_system()
{
	if(class_exists('jigoshop'))
	{
		load_plugin_textdomain('jigoshop_web_optimization_system', false, dirname(plugin_basename(__FILE__)).'/languages/');

		// Set up class loaders
		require_once(JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_DIR.'/vendor/JigoshopSplClassLoader.php');
		$loader = new JigoshopSplClassLoader('Assetic', JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_DIR.'/vendor/assetic/src');
		$loader->register();
		$loader = new JigoshopSplClassLoader('Jigoshop\\Web', JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_DIR.'/src');
		$loader->register();

		new \Jigoshop\Web\Optimization();
	}
}
add_action('jigoshop_initialize_plugins', 'init_jigoshop_web_optimization_system');

