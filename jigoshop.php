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

if (!defined('JIGOSHOP_DIR')) {
	define('JIGOSHOP_DIR', dirname(__FILE__));
}
// Define plugin URL for assets
if (!defined('JIGOSHOP_URL')) {
	define('JIGOSHOP_URL', plugins_url('', __FILE__));
}

class Jigoshop_Init
{
	/** @var \JigoshopContainer */
	private $container;

	public function __construct()
	{
		require_once(JIGOSHOP_DIR.'/vendor/autoload.php');
		$loader = new \Symfony\Component\ClassLoader\ClassLoader();
		$loader->addPrefix('WPAL', JIGOSHOP_DIR.'/vendor/megawebmaster/wpal');
		$loader->addPrefix('Jigoshop', JIGOSHOP_DIR.'/src');
		$loader->register();

		// Initialize Jigoshop Dependency Injection Container
		$file = JIGOSHOP_DIR.'/cache/container.php';
		$is_debug = true; // TODO: Properly fetch developers mode
		$config_cache = new ConfigCache($file, $is_debug);

		if (!$config_cache->isFresh()) {
			$builder = new ContainerBuilder();
			$builder->addCompilerPass(new Jigoshop\Core\Types\CompilerPass());
			$builder->addCompilerPass(new Jigoshop\Admin\Settings\CompilerPass());
			$loader = new YamlFileLoader($builder, new FileLocator(JIGOSHOP_DIR.'/config'));
			$loader->load('services.yml');
			// Load extension configuration
			do_action('jigoshop\plugins\configure', $builder);
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
		$this->container = new JigoshopContainer();
	}

	/**
	 * Initializes Jigoshop.
	 * Sets properly class loader and prepares Jigoshop to start, then sets up external plugins.
	 * Calls `jigoshop\plugins\configure` action with \JigoshopContainer object as parameter - you need to add your extension configuration to the container there.
	 */
	public function init()
	{
		// Override default translations with custom .mo's found in wp-content/languages/jigoshop first.
		load_textdomain('jigoshop', WP_LANG_DIR.'/jigoshop/jigoshop-'.get_locale().'.mo');
		load_plugin_textdomain('jigoshop', false, JIGOSHOP_DIR.'/languages/');

		/** @var \Jigoshop\Core $jigoshop */
		// Initialize post types and roles
		$this->container->get('jigoshop.types');
		$this->container->get('jigoshop.roles');
		$jigoshop = $this->container->get('jigoshop');
		// Initialize Cron and Assets
		$this->container->get('jigoshop.cron');
		$this->container->get('jigoshop.assets');

		$jigoshop->run();
	}

	/**
	 * Installs or updates Jigoshop.
	 */
	public function update($network_wide = false)
	{
		// Require upgrade specific files
		require_once(ABSPATH.'/wp-admin/includes/upgrade.php');

		/** @var $wp \WPAL\Wordpress */
		$wp = $this->container->get('wpal');
		/** @var $options \Jigoshop\Core\Installer */
		$installer = $this->container->get('jigoshop.installer');

		if (!$network_wide) {
			$installer->install();
			return;
		}

		$blog = $wp->getWPDB()->blogid;
		$ids = $wp->getWPDB()->get_col("SELECT blog_id FROM {$wp->getWPDB()->blogs}");

		foreach ($ids as $id) {
			switch_to_blog($id);
			$installer->install();
		}
		switch_to_blog($blog);
	}
}

$jigoshop_init = new Jigoshop_Init();
add_action('init', array($jigoshop_init, 'init'), 0);
register_activation_hook(__FILE__, array($jigoshop_init, 'update'));
