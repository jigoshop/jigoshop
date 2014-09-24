<?php

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class initializing and loading of all required files.
 *
 * Separated from main plugin file to achieve PHP 5.2 compatibility - to show proper error messages
 * about required version.
 */
class JigoshopInit
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
		$is_debug = WP_DEBUG;
		$config_cache = new ConfigCache($file, $is_debug);

		if (!$config_cache->isFresh()) {
			$builder = new ContainerBuilder();
			$builder->addCompilerPass(new Jigoshop\Core\Types\CompilerPass());
			$builder->addCompilerPass(new Jigoshop\Admin\CompilerPass());
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

		add_filter('admin_footer_text', array($this, 'footer'));
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
		add_filter('plugin_action_links_'.JIGOSHOP_BASE_NAME, array($this, 'pluginLinks'));

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

	public function footer($text) {
		$screen = get_current_screen();

		if (strpos($screen->base, 'jigoshop') === false && strpos($screen->parent_base, 'jigoshop') === false && !in_array($screen->post_type, array('product', 'shop_order'))) {
			return $text;
		}

		return sprintf(
			'<a target="_blank" href="https://www.jigoshop.com/support/">%s</a> | %s',
			__('Contact support', 'jigoshop'),
			str_replace(
				array('[stars]','[link]','[/link]'),
				array(
					'<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/jigoshop#postform" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
					'<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/jigoshop#postform" >',
					'</a>'
				),
				__('Add your [stars] on [link]wordpress.org[/link] and keep this plugin essentially free.', 'jigoshop')
			)
		);
	}

	function pluginLinks($links)
	{
		return array_merge(array(
			'<a href="'.admin_url('admin.php?page=jigoshop_settings').'">'.__('Settings', 'jigoshop').'</a>',
			'<a href="https://www.jigoshop.com/documentation/">'.__('Docs', 'jigoshop').'</a>',
			'<a href="https://www.jigoshop.com/support/">'.__('Support', 'jigoshop').'</a>',
		), $links);
	}

	/**
	 * Installs or updates Jigoshop.
	 *
	 * @param bool $network_wide
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
