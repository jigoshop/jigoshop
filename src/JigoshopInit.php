<?php

use Jigoshop\Integration;
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
		require_once(JIGOSHOP_DIR.'/integration/autoload.php');
		$loader = new \Symfony\Component\ClassLoader\ClassLoader();
		$loader->addPrefix('WPAL', JIGOSHOP_DIR.'/vendor/megawebmaster/wpal');
		$loader->addPrefix('Jigoshop', JIGOSHOP_DIR.'/src');
		$loader->register();

		// Prepare logger
		$logger = new \Monolog\Logger('jigoshop');
		if (WP_DEBUG) {
			$logger->pushHandler(new \Monolog\Handler\StreamHandler(JIGOSHOP_DIR.'/log/jigoshop.debug.log', \Monolog\Logger::DEBUG));
		}
		$logger->pushHandler(new \Monolog\Handler\StreamHandler(JIGOSHOP_DIR.'/log/jigoshop.log', \Monolog\Logger::WARNING));
		$logger->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());
		$logger->pushProcessor(new \Monolog\Processor\WebProcessor());
		\Monolog\Registry::addLogger($logger);

		// Initialize Jigoshop Dependency Injection Container
		$file = JIGOSHOP_DIR.'/cache/container.php';
		$is_debug = WP_DEBUG;
		$cache = new ConfigCache($file, $is_debug);

		if (!$cache->isFresh()) {
			$logger->addDebug('Refreshing container definition.');

			$diDefinition = new \Symfony\Component\DependencyInjection\Definition();
			$diDefinition->setSynthetic(true);

			$builder = new ContainerBuilder();
			$builder->setDefinition('di', $diDefinition);
			$builder->addCompilerPass(new Jigoshop\Core\Types\CompilerPass());
			$builder->addCompilerPass(new Jigoshop\Core\Installer\CompilerPass());
			$builder->addCompilerPass(new Jigoshop\Payment\CompilerPass());
			$builder->addCompilerPass(new Jigoshop\Shipping\CompilerPass());
			$builder->addCompilerPass(new Jigoshop\Admin\CompilerPass());
			$builder->addCompilerPass(new Jigoshop\Admin\Settings\CompilerPass());

			$loader = new YamlFileLoader($builder, new FileLocator(JIGOSHOP_DIR.'/config'));
			$loader->load('admin.yml');
			$loader->load('admin/settings.yml');
			$loader->load('admin/pages.yml');
			$loader->load('factories.yml');
			$loader->load('helpers.yml');
			$loader->load('main.yml');
			$loader->load('pages.yml');
			$loader->load('payment.yml');
			$loader->load('services.yml');
			$loader->load('shipping.yml');
			// Load extension configuration
			do_action('jigoshop\plugins\configure', $builder);
			$builder->compile();

			$dumper = new PhpDumper($builder);
			$cache->write(
				$dumper->dump(array('class' => 'JigoshopContainer')),
				$builder->getResources()
			);
		}

		/** @noinspection PhpIncludeInspection */
		require_once($file);
		/** @noinspection PhpUndefinedClassInspection */
		$this->container = new JigoshopContainer();
		$this->container->set('di', $this->container);

		add_filter('admin_footer_text', array($this, 'footer'));
		add_action('admin_bar_menu', array($this, 'toolbar'), 35);
	}

	/**
	 * Initializes Jigoshop.
	 * Sets properly class loader and prepares Jigoshop to start, then sets up external plugins.
	 * Calls `jigoshop\plugins\configure` action with \JigoshopContainer object as parameter - you need to add your extension configuration to the container there.
	 */
	public function init()
	{
		// Override default translations with custom .mo's found in wp-content/languages/jigoshop first.
		load_textdomain('jigoshop', WP_LANG_DIR.'/jigoshop/'.get_locale().'.mo');
		load_plugin_textdomain('jigoshop', false, JIGOSHOP_DIR.'/languages/');

		// Add links in Plugins page
		add_filter('plugin_action_links_'.JIGOSHOP_BASE_NAME, array($this, 'pluginLinks'));

		// Configure container before initializing Jigoshop
		do_action('jigoshop\init', $this->container);

		// Load query interceptor before Jigoshop
		$interceptor = $this->container->get('jigoshop.query.interceptor');

		if (!($interceptor instanceof Jigoshop\Query\Interceptor)) {
			if (is_admin()) {
				add_action('admin_notices', function(){
					echo '<div class="error"><p>';
					echo __('Invalid query interceptor instance in Jigoshop. The shop will remain inactive until configured properly.', 'jigoshop');
					echo '</p></div>';
				});
			}

			\Monolog\Registry::getInstance('jigoshop')->addEmergency('Invalid query interceptor instance in Jigoshop. Unable to proceed.');
			return;
		}

		$interceptor->run();
		/** @var \Jigoshop\Core\Options $options */
		$options = $this->container->get('jigoshop.options');
		/** @var \WPAL\Wordpress $wp */
		$wp = $this->container->get('wpal');
		Jigoshop\Helper\Country::setOptions($options);
		Jigoshop\Helper\Currency::setOptions($options);
		Jigoshop\Helper\Product::setOptions($options);
		Jigoshop\Helper\Order::setOptions($options);
		Jigoshop\Entity\Order\Status::setWordpress($wp);

		/** @var \Jigoshop\Core $jigoshop */
		$jigoshop = $this->container->get('jigoshop');

		// Initialize post types and roles
		$this->container->get('jigoshop.types');
		$this->container->get('jigoshop.roles');
		// Initialize Cron
		$this->container->get('jigoshop.cron');

		// Initialize integration layer
		new Integration($this->container); // TODO: Determine whether to load integration layer

		if (is_admin()) {
			/** @var \Jigoshop\Admin\PageResolver $resolver */
			$resolver = $this->container->get('jigoshop.admin.page_resolver');
			$resolver->resolve($this->container);

			$this->container->get('jigoshop.admin');
		}

		/** @var \Jigoshop\Frontend\PageResolver $resolver */
		$resolver = $this->container->get('jigoshop.frontend.page_resolver');
		$resolver->resolve($this->container);
		$jigoshop->run($this->container);
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

	/**
	 * Adds Jigoshop items to admin bar.
	 */
	public function toolbar()
	{
		/** @var WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;
		$manage_products = current_user_can('manage_jigoshop_products');
		$manage_orders = current_user_can('manage_jigoshop_orders');
		$manage_jigoshop = current_user_can('manage_jigoshop');
		$view_reports = current_user_can('view_jigoshop_reports');

		if (!is_admin() && ($manage_jigoshop || $manage_products || $manage_orders || $view_reports)) {
			$wp_admin_bar->add_node(array(
				'id' => 'jigoshop',
				'title' => __('Jigoshop', 'jigoshop'),
				'href' => $manage_jigoshop ? admin_url('admin.php?page=jigoshop') : '',
				'parent' => false,
				'meta' => array(
					'class' => 'jigoshop-toolbar'
				),
			));

			if ($manage_jigoshop) {
				$wp_admin_bar->add_node(array(
					'id' => 'jigoshop_dashboard',
					'title' => __('Dashboard', 'jigoshop'),
					'parent' => 'jigoshop',
					'href' => admin_url('admin.php?page=jigoshop'),
				));
			}

			if ($manage_products) {
				$wp_admin_bar->add_node(array(
					'id' => 'jigoshop_products',
					'title' => __('Products', 'jigoshop'),
					'parent' => 'jigoshop',
					'href' => admin_url('edit.php?post_type=product'),
				));
			}

			if ($manage_orders) {
				$wp_admin_bar->add_node(array(
					'id' => 'jigoshop_orders',
					'title' => __('Orders', 'jigoshop'),
					'parent' => 'jigoshop',
					'href' => admin_url('edit.php?post_type=shop_order'),
				));
			}

			if ($manage_jigoshop) {
				$wp_admin_bar->add_node(array(
					'id' => 'jigoshop_settings',
					'title' => __('Settings', 'jigoshop'),
					'parent' => 'jigoshop',
					'href' => admin_url('admin.php?page=jigoshop_settings'),
				));
			}
		}
	}

	public function pluginLinks($links)
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
