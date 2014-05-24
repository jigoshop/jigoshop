<?php
/**
 *   ./////////////////////////////.
 *  //////////////////////////////////
 *  ///////////    ///////////////////
 *  ////////     /////////////////////
 *  //////    ////////////////////////
 *  /////    /////////    ////////////
 *  //////     /////////     /////////
 *  /////////     /////////    ///////
 *  ///////////    //////////    /////
 *  ////////////////////////    //////
 *  /////////////////////    /////////
 *  ///////////////////    ///////////
 *  //////////////////////////////////
 *   `//////////////////////////////`
 *
 * Plugin Name:         Jigoshop
 * Plugin URI:          http://www.jigoshop.com/
 * Description:         Jigoshop, a WordPress eCommerce plugin that works.
 * Author:              Jigoshop
 * Author URI:          http://www.jigoshop.com
 * Version:             2.0
 * Requires at least:   3.8
 * Tested up to:        3.9.1
 * Text Domain:         jigoshop
 * Domain Path:         /languages/
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

// Define plugin directory for inclusions
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

if(!defined('JIGOSHOP_DIR'))
{
	define('JIGOSHOP_DIR', dirname(__FILE__));
}
// Define plugin URL for assets
if(!defined('JIGOSHOP_URL'))
{
	define('JIGOSHOP_URL', plugins_url('', __FILE__));
}

function jigoshop_setup_class_loader()
{
	require_once(JIGOSHOP_DIR.'/src/Jigoshop/ClassLoader.php');
	$loader = new \JigoshopClassLoader('WPAL', JIGOSHOP_DIR.'/vendor/megawebmaster/wpal');
	$loader->register();
	$loader = new \JigoshopClassLoader('Symfony\\Component\\DependencyInjection', JIGOSHOP_DIR.'/vendor/symfony/dependency-injection');
	$loader->register();
	$loader = new \JigoshopClassLoader('Symfony\\Component\\Filesystem', JIGOSHOP_DIR.'/vendor/symfony/filesystem');
	$loader->register();
	$loader = new \JigoshopClassLoader('Symfony\\Component\\Config', JIGOSHOP_DIR.'/vendor/symfony/config');
	$loader->register();
	$loader = new \JigoshopClassLoader('Symfony\\Component\\Yaml', JIGOSHOP_DIR.'/vendor/symfony/yaml');
	$loader->register();
	$loader = new \JigoshopClassLoader('Jigoshop', JIGOSHOP_DIR.'/src');
	$loader->register();
}

/**
 * Initializes Jigoshop.
 *
 * Sets properly class loader and prepares Jigoshop to start, then sets up external plugins.
 *
 * Calls `jigoshop\initialize\plugins` action with \Jigoshop\Core object as parameter.
 */
function jigoshop_init()
{
	// Override default translations with custom .mo's found in wp-content/languages/jigoshop first.
	load_textdomain('jigoshop', WP_LANG_DIR.'/jigoshop/jigoshop-'.get_locale().'.mo');
	load_plugin_textdomain('jigoshop', false, JIGOSHOP_DIR.'/languages/');

	jigoshop_setup_class_loader();

	// Initialize Jigoshop Dependency Injection Container
	$file = JIGOSHOP_DIR.'/cache/container.php';
	$is_debug = true;
	$config_cache = new ConfigCache($file, $is_debug);

	if(!$config_cache->isFresh()){
		$builder = new ContainerBuilder();
		$loader = new YamlFileLoader($builder, new FileLocator(JIGOSHOP_DIR.'/config'));
		$loader->load('services.yml');
		// Load extension configuration
		do_action('jigoshop\\plugins\\configure', $builder);
		$builder->compile();

		$dumper = new PhpDumper($builder);
		$config_cache->write(
			$dumper->dump(array('class' => 'JigoshopContainer')),
			$builder->getResources()
		);
	}

	/** @noinspection PhpIncludeInspection */
	require_once($file);
	/** @noinspection PhpUndefinedClassInspection */
	$container = new JigoshopContainer();

	/** @var \Jigoshop\Core $jigoshop */
	$jigoshop = $container->get('jigoshop');

	// Initialize external plugins
	do_action('jigoshop\\plugins\\initialize', $container, $jigoshop);

	$jigoshop->run();
}
add_action('init', 'jigoshop_init', 0);

/**
 * Installs or updates Jigoshop.
 */
function jigoshop_update($network_wide = false)
{
	jigoshop_setup_class_loader();
	/** @var $wpdb WPDB */
	global $wpdb;

	if(!$network_wide)
	{
		new \Jigoshop\Core\Install($wpdb);
		return;
	}

	$blog = $wpdb->blogid;
	$ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");

	foreach($ids as $id)
	{
		switch_to_blog($id);
		new \Jigoshop\Core\Install($wpdb);
	}
	switch_to_blog($blog);
}
register_activation_hook(__FILE__, 'jigoshop_update');

