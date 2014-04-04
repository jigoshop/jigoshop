<?php

namespace Jigoshop\Web;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\FileAsset;
use Assetic\AssetManager;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\CacheBustingWorker;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\JSMinPlusFilter;
use Assetic\FilterManager;
use Assetic\Util\VarUtils;
use Jigoshop\Web\Optimizing\Filter\WordpressCssRewriteFilter;

/**
 * Main class of Jigoshop Web Optimizing framework.
 *
 * @package Jigoshop\Web
 * @author Jigoshop
 */
class Optimizing
{
	const VERSION = '1.0';

	private $_scripts = array();
	private $_styles = array();

	/** @var \Assetic\Factory\AssetFactory */
	private $_factory;

	public function __construct()
	{
		require_once(JIGOSHOP_WEB_OPTIMIZING_DIR.'/vendor/CSSMin/cssmin.php');
		require_once(JIGOSHOP_WEB_OPTIMIZING_DIR.'/vendor/minify/JSMinPlus.php');

		$filterManager = new FilterManager();
		$filterManager->set('jsmin', new JSMinPlusFilter());
		$filterManager->set('cssmin', new CssMinFilter());
		$filterManager->set('cssrewrite', new WordpressCssRewriteFilter(JIGOSHOP_WEB_OPTIMIZING_CACHE));
		$this->_factory = new AssetFactory(JIGOSHOP_WEB_OPTIMIZING_CACHE);
		$this->_factory->setAssetManager(new AssetManager());
		$this->_factory->setFilterManager($filterManager);
		$this->_factory->addWorker(new CacheBustingWorker());

		add_filter('jigoshop_add_script', array($this, 'add_script'), 99999, 4);
		add_filter('jigoshop_add_style', array($this, 'add_style'), 99999, 4);

		// Admin
		if(is_admin())
		{
			add_action('admin_print_scripts', array($this, 'admin_print_scripts'));
			add_action('admin_print_styles', array($this, 'admin_print_styles'));
			\Jigoshop_Base::get_options()->install_external_options_tab(__('Web Optimizing', 'jigoshop_web_optimizing'), $this->admin_settings());
		}
		// Front
		else
		{
			add_action('wp_print_scripts', array($this, 'front_print_scripts'));
			add_action('wp_print_styles', array($this, 'front_print_styles'));
		}

		// Initialize script and style arrays
		$available = jigoshop_get_available_pages();
		foreach($available as $page)
		{
			$this->_scripts[$page] = array();
		}
		$this->_styles = $this->_scripts;
	}

	/** Plugin page in administration panel. */
	public function admin_settings()
	{
		return array(
			array(
				'name' => __('Jigoshop Web Optimizing', 'jigoshop_web_optimizing'),
				'type' => 'title',
				'desc' => '',
				'id' => '',
			),
			array(
				'name' => __('Plugin status', 'jigoshop_web_optimizing'),
				'id' => 'jigoshop_web_optimizing_clear_cache',
				'type' => 'user_defined',
				'display' => array($this, 'admin_plugin_status'),
				'update' => array($this, 'admin_clear_cache'),
			),
		);
	}

	/** Plugin status item in administration panel. */
	public function admin_plugin_status()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$files = iterator_count(new \FilesystemIterator(JIGOSHOP_WEB_OPTIMIZING_CACHE, \FilesystemIterator::SKIP_DOTS));
		ob_start();
		include(JIGOSHOP_WEB_OPTIMIZING_DIR.'/templates/plugin_status.php');

		return ob_get_clean();
	}

	public function admin_clear_cache()
	{
		if(isset($_POST['clear_cache']) && $_POST['clear_cache'] == 'on')
		{
			foreach(new \DirectoryIterator(JIGOSHOP_WEB_OPTIMIZING_CACHE) as $file)
			{
				/** @var $file \DirectoryIterator */
				if(!$file->isDot())
				{
					unlink($file->getPathname());
				}
			}
		}
	}

	/**
	 * Adds file to managed scripts.
	 *
	 * Scripts added through this function will be minimized and packed into single file.
	 *
	 * Available options:
	 *   * page - list of pages to use the stylesheet.
	 *
	 * @param $handle string Name of script.
	 * @param $src string Source path to script.
	 * @param $dependencies array List of dependencies.
	 * @param $options array List of options.
	 * @return string Empty string for jigoshop_add_script() function.
	 */
	public function add_script($handle, $src, array $dependencies = array(), array $options = array())
	{
		$pages = isset($options['page']) ? (array)$options['page'] : array('all');
		$handle = $this->prepare_script_handle($handle);
		$manager = $this->_factory->getAssetManager();
		if(!$manager->has($handle) && $src && $this->check_script_dependencies($dependencies))
		{
			$manager->set($handle, new FileAsset($this->get_source_path($src)));
			foreach($pages as $page)
			{
				$this->_scripts[$page][] = '@'.$handle;
			}
		}

		return '';
	}

	/**
	 * Adds file to managed stylesheets.
	 *
	 * Stylesheets added through this function will be minimized and packed into single file.
	 *
	 * Available options:
	 *   * page - list of pages to use the stylesheet.
	 *
	 * @param $handle string Name of style.
	 * @param $src string Source path to stylesheet.
	 * @param $dependencies array List of dependencies.
	 * @param $options array List of options.
	 * @return string Empty string for jigoshop_add_style() function.
	 */
	public function add_style($handle, $src, array $dependencies = array(), array $options = array())
	{
		$pages = isset($options['page']) ? (array)$options['page'] : array('all');
		$handle = $this->prepare_style_handle($handle);
		$manager = $this->_factory->getAssetManager();
		if(!$manager->has($handle) && $src && $this->check_style_dependencies($dependencies))
		{
			$manager->set($handle, new FileAsset($this->get_source_path($src)));
			foreach($pages as $page)
			{
				$this->_styles[$page][] = '@'.$handle;
			}
		}

		return '';
	}

	/**
	 * Prints admin styles.
	 */
	public function admin_print_styles()
	{
		$styles = call_user_func_array('array_merge', $this->_styles);
		$asset = new Optimizing\Asset\Minified\Stylesheet($this->_factory, $styles, array('page' => 'all', 'location' => 'admin'));
		$css = $asset->getAsset();

		wp_enqueue_style('jigoshop_web_optimized_admin_styles', JIGOSHOP_WEB_OPTIMIZING_URL.'/cache/'.
			VarUtils::resolve(
        $css->getTargetPath(),
        $css->getVars(),
        $css->getValues()
			)
		);
	}

	/**
	 * Prints admin scripts.
	 */
	public function admin_print_scripts()
	{
		$scripts = call_user_func_array('array_merge', $this->_scripts);
		$asset = new Optimizing\Asset\Javascript($this->_factory, $scripts, array('page' => 'all', 'location' => 'admin'));
		$js = $asset->getAsset();

		wp_enqueue_script(
			'jigoshop_web_optimized_admin_scripts', JIGOSHOP_WEB_OPTIMIZING_URL.'/cache/'.
			VarUtils::resolve(
				$js->getTargetPath(),
				$js->getVars(),
				$js->getValues()
			)
		);
	}

	/**
	 * Prints frontend styles.
	 */
	public function front_print_styles()
	{
		$current_page = $this->get_current_page();
		$styles = array_merge(
			$this->_styles[JIGOSHOP_ALL],
			$this->_styles[$current_page]
		);
		$asset = new Optimizing\Asset\Minified\Stylesheet($this->_factory, $styles, array('page' => $current_page, 'location' => 'frontend'));
		$css = $asset->getAsset();

		wp_enqueue_style('jigoshop_web_optimized', JIGOSHOP_WEB_OPTIMIZING_URL.'/cache/'.$this->get_asset_name($css));
	}

	/**
	 * Prints frontend scripts.
	 */
	public function front_print_scripts()
	{
		$current_page = $this->get_current_page();
		$scripts = array_merge(
			$this->_scripts[JIGOSHOP_ALL],
			$this->_scripts[$current_page]
		);
		$asset = new Optimizing\Asset\Minified\Javascript($this->_factory, $scripts, array('page' => $current_page, 'location' => 'frontend'));
		$js = $asset->getAsset();

		wp_enqueue_script('jigoshop_web_optimized', JIGOSHOP_WEB_OPTIMIZING_URL.'/cache/'.$this->get_asset_name($js));
	}

	private function get_source_path($src)
	{
		return str_replace(\jigoshop::assets_url(), \jigoshop::plugin_path(), $src);
	}

	private function check_script_dependencies(array $dependencies)
	{
		/** @var $wp_scripts \WP_Scripts */
		global $wp_scripts;
		$diff = array_diff($dependencies, array_keys($wp_scripts->registered));
		$current_page = $this->get_current_page();

		// Check if non-WordPress dependencies are present.
		foreach($diff as $dependency)
		{
			$dependency = '@'.$this->prepare_script_handle($dependency);
			if(!in_array($dependency, $this->_scripts[JIGOSHOP_ALL]) && !in_array($dependency, $this->_scripts[$current_page]))
			{
				return false;
			}
		}

		return true;
	}

	private function check_style_dependencies(array $dependencies)
	{
		/** @var $wp_styles \WP_Styles */
		global $wp_styles;
		$diff = array_diff($dependencies, array_keys($wp_styles->registered));
		$current_page = $this->get_current_page();

		// Check if non-WordPress dependencies are present.
		foreach($diff as $dependency)
		{
			$dependency = '@'.$this->prepare_style_handle($dependency);
			if(!in_array($dependency, $this->_styles[JIGOSHOP_ALL]) && !in_array($dependency, $this->_styles[$current_page]))
			{
				return false;
			}
		}

		return true;
	}

	private function get_current_page()
	{
		$available = jigoshop_get_available_pages();
		foreach($available as $page)
		{
			if(is_jigoshop_single_page($page))
			{
				return $page;
			}
		}

		return JIGOSHOP_ALL;
	}

	private function prepare_script_handle($handle)
	{
		return 'script_'.str_replace('-', '_', $handle);
	}

	private function prepare_style_handle($handle)
	{
		return 'style_'.str_replace('-', '_', $handle);
	}

	private function get_asset_name(AssetInterface $asset)
	{
		return VarUtils::resolve(
			$asset->getTargetPath(),
			$asset->getVars(),
			$asset->getValues()
		);
	}
}