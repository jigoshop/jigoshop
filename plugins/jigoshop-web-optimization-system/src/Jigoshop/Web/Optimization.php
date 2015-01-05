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
use Jigoshop\Web\Optimization\Filter\WordpressCssRewriteFilter;

/**
 * Main class of Jigoshop Web Optimization System.
 *
 * @package Jigoshop\Web
 * @author Amadeusz Starzykiewicz
 */
class Optimization
{
	private $_scripts = array();
	private $_styles = array();
	private $_dependencies = array();
	private $_localizations = array();

	/** @var \Assetic\Factory\AssetFactory */
	private $_factory;

	public function __construct()
	{
		if(is_admin())
		{
			\Jigoshop_Base::get_options()->install_external_options_tab(__('Web Optimization System', 'jigoshop_web_optimization_system'), $this->admin_settings());
		}

		if(\Jigoshop_Base::get_options()->get('jigoshop_web_optimization_system_enable', 'yes') == 'yes')
		{
			require_once(JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_DIR.'/vendor/CSSMin/cssmin.php');
			require_once(JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_DIR.'/vendor/minify/JSMinPlus.php');

			$filterManager = new FilterManager();
			$filterManager->set('jsmin', new JSMinPlusFilter());
			$filterManager->set('cssmin', new CssMinFilter());
			$filterManager->set('cssrewrite', new WordpressCssRewriteFilter(JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_CACHE));
			$this->_factory = new AssetFactory(JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_CACHE);
			$this->_factory->setAssetManager(new AssetManager());
			$this->_factory->setFilterManager($filterManager);
			$this->_factory->addWorker(new CacheBustingWorker());

			add_filter('jigoshop_add_script', array($this, 'add_script'), 99999, 4);
			add_filter('jigoshop_remove_script', array($this, 'remove_script'), 99999, 2);
			add_filter('jigoshop_localize_script', array($this, 'localize_script'), 99999, 3);
			add_filter('jigoshop_add_style', array($this, 'add_style'), 99999, 4);
			add_filter('jigoshop_remove_style', array($this, 'remove_style'), 99999, 2);

			// Admin
			if(is_admin())
			{
				add_action('admin_enqueue_scripts', array($this, 'admin_print_scripts'), 1000);
				add_action('admin_enqueue_scripts', array($this, 'admin_print_styles'), 1000);
			}
			// Front
			else
			{
				add_action('wp_enqueue_scripts', array($this, 'front_print_scripts'), 1000);
				add_action('wp_enqueue_scripts', array($this, 'front_print_styles'), 1000);
			}

			// Initialize script and style arrays
			$available = jigoshop_get_available_pages();
			foreach($available as $page)
			{
				$this->_scripts[$page] = array();
			}
			$this->_styles = $this->_scripts;
		}
	}

	/** Plugin page in administration panel. */
	public function admin_settings()
	{
		return array(
			array(
				'name' => __('Jigoshop Web Optimization System', 'jigoshop_web_optimization_system'),
				'type' => 'title',
				'desc' => '',
				'id' => '',
			),
			array(
				'name' => __('Enable Jigoshop Web Optimization System', 'jigoshop_web_optimization_system'),
				'id' => 'jigoshop_web_optimization_system_enable',
				'desc' => __('This feature improves performance of the jigoshop along with its extenstions by combining and compressing all the css and js files. As a result browser will have to download one compressed java script file and one compressed css file for all jigoshop related functionality. Note: Extensions should use jigoshop_add_style(), jigoshop_add_script() and jigoshop_localize_script() API in order to utilise JWOS functionality.', 'jigoshop_web_optimization_system'),
				'type' => 'checkbox',
				'std' => 'no',
				'choices' => array(
					'yes' => __('Yes'),
					'no' => __('No'),
				),
			),
			array(
				'name' => __('Plugin status', 'jigoshop_web_optimization_system'),
				'id' => 'jigoshop_web_optimization_system_clear_cache',
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
		$files = iterator_count(new \FilesystemIterator(JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_CACHE, \FilesystemIterator::SKIP_DOTS));
		ob_start();
		include(JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_DIR.'/templates/plugin_status.php');

		return ob_get_clean();
	}

	public function admin_clear_cache()
	{
		if(isset($_POST['clear_cache']) && $_POST['clear_cache'] == 'on')
		{
			foreach(new \DirectoryIterator(JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_CACHE) as $file)
			{
				/** @var $file \DirectoryIterator */
				if(!$file->isDot() && $file->getFilename() != '.ignore')
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
			$this->_dependencies = array_merge($this->_dependencies, $dependencies);
			$manager->set($handle, new FileAsset($this->get_source_path($src)));
			foreach($pages as $page)
			{
				$this->_scripts[$page][] = '@'.$handle;
			}
		}

		return '';
	}

	private function prepare_script_handle($handle)
	{
		return 'script_'.str_replace(array('-', '.'), '_', $handle);
	}

	private function check_script_dependencies(array &$dependencies)
	{
		/** @var $wp_scripts \WP_Scripts */
		global $wp_scripts;

		if($wp_scripts !== null)
		{
			$diff = array_diff($dependencies, array_keys($wp_scripts->registered));
		}
		else
		{
			$diff = $dependencies;
		}

		$current_page = $this->get_current_page();

		// Check if non-WordPress dependencies are present.
		foreach($diff as $key => $dependency)
		{
			unset($dependencies[$key]);
			$dependency = '@'.$this->prepare_script_handle($dependency);
			if(!in_array($dependency, $this->_scripts[JIGOSHOP_ALL]) && !in_array($dependency, $this->_scripts[$current_page]))
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

	private function get_source_path($src)
	{
		return str_replace(str_replace(array('http://', 'https://'), '', WP_CONTENT_URL), WP_CONTENT_DIR, str_replace(array('http://', 'https://'), '', $src);
	}

	/**
	 * Removes file from managed scripts.

	 * Available options:
	 *   * page - list of pages to use the script.
	 *
	 * @param $handle string Name of script.
	 * @param $options array List of options.
	 * @return string Empty string for jigoshop_remove_script() function if script was found or handle name otherwise.
	 */
	public function remove_script($handle, array $options = array())
	{
		$pages = isset($options['page']) ? (array)$options['page'] : array('all');
		$script_handle = $this->prepare_script_handle($handle);
		$manager = $this->_factory->getAssetManager();
		if($manager->has($script_handle))
		{
			$manager->set($script_handle, null);
			foreach($pages as $page)
			{
				$position = array_search('@'.$script_handle, $this->_scripts[$page]);
				if($position !== false)
				{
					unset($this->_scripts[$page][$position]);
				}
			}

			return '';
		}

		return $handle;
	}

	/**
	 * Localizes selected handle.
	 *
	 * Use this function for each script added through {@link add_script()} as it won't be accessible otherwise.
	 *
	 * Function checks if script is managed by Optimization so be sure you have added it earlier.
	 *
	 * @param $handle string Name of script.
	 * @param $object string Name of object to store values.
	 * @param $values array Values to store.
	 * @return string
	 */
	public function localize_script($handle, $object, array $values)
	{
		$handle = '@'.$this->prepare_script_handle($handle);
		$current_page = $this->get_current_page();
		if(in_array($handle, $this->_scripts[$current_page]) || in_array($handle, $this->_scripts[JIGOSHOP_ALL]))
		{
			$this->_localizations[$object] = $values;
			return '';
		}

		return $handle;
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

	private function prepare_style_handle($handle)
	{
		return 'style_'.str_replace(array('-', '.'), '_', $handle);
	}

	private function check_style_dependencies(array &$dependencies)
	{
		/** @var $wp_styles \WP_Styles */
		global $wp_styles;

		if($wp_styles !== null)
		{
			$diff = array_diff($dependencies, array_keys($wp_styles->registered));
		}
		else
		{
			$diff = $dependencies;
		}

		$current_page = $this->get_current_page();

		// Check if non-WordPress dependencies are present.
		foreach($diff as $key => $dependency)
		{
			unset($dependencies[$key]);
			$dependency = '@'.$this->prepare_style_handle($dependency);
			if(!in_array($dependency, $this->_styles[JIGOSHOP_ALL]) && !in_array($dependency, $this->_styles[$current_page]))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Removes file from managed styles.

	 * Available options:
	 *   * page - list of pages to use the stylesheet.
	 *
	 * @param $handle string Name of style.
	 * @param $options array List of options.
	 * @return string Empty string for jigoshop_remove_style() function if script was found or handle name otherwise.
	 */
	public function remove_style($handle, array $options = array())
	{
		$pages = isset($options['page']) ? (array)$options['page'] : array('all');
		$style_handle = $this->prepare_style_handle($handle);
		$manager = $this->_factory->getAssetManager();
		if($manager->has($style_handle))
		{
			$manager->set($style_handle, null);
			foreach($pages as $page)
			{
				$position = array_search('@'.$style_handle, $this->_styles[$page]);
				if($position !== false)
				{
					unset($this->_styles[$page][$position]);
				}
			}

			return '';
		}

		return $handle;
	}

	/**
	 * Prints admin styles.
	 */
	public function admin_print_styles()
	{
		// TODO: Think how to improve this call
		$styles = call_user_func_array('array_merge', $this->_styles);
		$asset = new Optimization\Asset\Minified\Stylesheet($this->_factory, $styles, array('page' => 'all', 'location' => 'admin'));
		$css = $asset->getAsset();

		wp_enqueue_style('jigoshop_web_optimization_admin_styles', JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_URL.'/cache/'.$this->get_asset_name($css));
	}

	private function get_asset_name(AssetInterface $asset)
	{
		return VarUtils::resolve(
			$asset->getTargetPath(),
			$asset->getVars(),
			$asset->getValues()
		);
	}

	/**
	 * Prints admin scripts.
	 */
	public function admin_print_scripts()
	{
		// TODO: Think how to improve this call
		$scripts = call_user_func_array('array_merge', $this->_scripts);
		$asset = new Optimization\Asset\Javascript($this->_factory, $scripts, array('page' => 'all', 'location' => 'admin'));
		$js = $asset->getAsset();

		wp_enqueue_script('jigoshop_web_optimization_admin_scripts', JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_URL.'/cache/'.$this->get_asset_name($js), array_unique($this->_dependencies));

		foreach($this->_localizations as $object => $values)
		{
			wp_localize_script('jigoshop_web_optimization_admin_scripts', $object, $values);
		}
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
		$asset = new Optimization\Asset\Minified\Stylesheet($this->_factory, $styles, array('page' => $current_page, 'location' => 'frontend'));
		$css = $asset->getAsset();

		wp_enqueue_style('jigoshop_web_optimization', JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_URL.'/cache/'.$this->get_asset_name($css));
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
		$asset = new Optimization\Asset\Minified\Javascript($this->_factory, $scripts, array('page' => $current_page, 'location' => 'frontend'));
		$js = $asset->getAsset();

		wp_enqueue_script('jigoshop_web_optimization', JIGOSHOP_WEB_OPTIMIZATION_SYSTEM_URL.'/cache/'.$this->get_asset_name($js), array_unique($this->_dependencies));

		foreach($this->_localizations as $object => $values)
		{
			wp_localize_script('jigoshop_web_optimization', $object, $values);
		}
	}
}
