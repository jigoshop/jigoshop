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
use Jigoshop\Core\Options;
use Jigoshop\Frontend\Pages;
use Jigoshop\Web\Optimization\Filter\WordpressCssRewriteFilter;
use WPAL\Wordpress;

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

	public function __construct(Wordpress $wp, Options $options)
	{
		if($options->get('optimization.enabled'))
		{
			// TODO: Do we need to get factory from container?
			$filterManager = new FilterManager();
			$filterManager->set('jsmin', new JSMinPlusFilter());
			$filterManager->set('cssmin', new CssMinFilter());
			$filterManager->set('cssrewrite', new WordpressCssRewriteFilter(JIGOSHOP_DIR.'/cache/assets'));
			$this->_factory = new AssetFactory(JIGOSHOP_DIR.'/cache/assets');
			$this->_factory->setAssetManager(new AssetManager());
			$this->_factory->setFilterManager($filterManager);
			$this->_factory->addWorker(new CacheBustingWorker());

			$wp->addFilter('jigoshop\script\add', array($this, 'addScript'), 99999, 4);
			$wp->addFilter('jigoshop\script\remove', array($this, 'removeScript'), 99999, 2);
			$wp->addFilter('jigoshop\script\localize', array($this, 'localizeScript'), 99999, 3);
			$wp->addFilter('jigoshop\style\add', array($this, 'addStyle'), 99999, 4);
			$wp->addFilter('jigoshop\style\remove', array($this, 'removeStyle'), 99999, 2);

			// Admin
			if($wp->isAdmin())
			{
				$wp->addAction('admin_enqueue_scripts', array($this, 'adminPrintScripts'), 1000);
				$wp->addAction('admin_enqueue_scripts', array($this, 'adminPrintStyles'), 1000);
			}
			// Front
			else
			{
				$wp->addAction('wp_enqueue_scripts', array($this, 'frontPrintScripts'), 1000);
				$wp->addAction('wp_enqueue_scripts', array($this, 'frontPrintStyles'), 1000);
			}

			// Initialize script and style arrays
			$available = Pages::getAvailable();
			foreach($available as $page)
			{
				$this->_scripts[$page] = array();
			}
			$this->_styles = $this->_scripts;
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
	public function addScript($handle, $src, array $dependencies = array(), array $options = array())
	{
		$pages = isset($options['page']) ? (array)$options['page'] : array('all');
		$handle = $this->prepareScriptHandle($handle);
		$manager = $this->_factory->getAssetManager();

		// Check if this is external source
		$src = str_replace(array('http://', 'https://'), '', $src);
		if (strpos($src, str_replace(array('http://', 'https://'), '', WP_CONTENT_URL)) === false) {
			return $src;
		}

		if(!$manager->has($handle) && $src && $this->checkScriptDependencies($dependencies))
		{
			$this->_dependencies = array_merge($this->_dependencies, $dependencies);
			$manager->set($handle, new FileAsset($this->getSourcePath($src)));
			foreach($pages as $page)
			{
				$this->_scripts[$page][] = '@'.$handle;
			}
		}

		return '';
	}

	private function prepareScriptHandle($handle)
	{
		return 'script_'.str_replace(array('-', '.'), '_', $handle);
	}

	private function checkScriptDependencies(array &$dependencies)
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

		$current_page = $this->getCurrentPage();

		// Check if non-WordPress dependencies are present.
		foreach($diff as $key => $dependency)
		{
			unset($dependencies[$key]);
			$dependency = '@'.$this->prepareScriptHandle($dependency);
			if(!in_array($dependency, $this->_scripts[JIGOSHOP_ALL]) && !in_array($dependency, $this->_scripts[$current_page]))
			{
				return false;
			}
		}

		return true;
	}

	private function getCurrentPage()
	{
		$available = Pages::getAvailable();
		foreach($available as $page)
		{
			if(Pages::is($page))
			{
				return $page;
			}
		}

		return JIGOSHOP_ALL;
	}

	private function getSourcePath($src)
	{
		return str_replace(str_replace(array('http://', 'https://'), '', WP_CONTENT_URL), WP_CONTENT_DIR, $src);
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
	public function removeScript($handle, array $options = array())
	{
		$pages = isset($options['page']) ? (array)$options['page'] : array('all');
		$script_handle = $this->prepareScriptHandle($handle);
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
	public function localizeScript($handle, $object, array $values)
	{
		$handle = '@'.$this->prepareScriptHandle($handle);
		$current_page = $this->getCurrentPage();
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
	public function addStyle($handle, $src, array $dependencies = array(), array $options = array())
	{
		$pages = isset($options['page']) ? (array)$options['page'] : array('all');
		$handle = $this->prepareStyleHandle($handle);
		$manager = $this->_factory->getAssetManager();

		// Check if this is external source
		$src = str_replace(array('http://', 'https://'), '', $src);
		if (strpos($src, str_replace(array('http://', 'https://'), '', WP_CONTENT_URL)) === false) {
			return $src;
		}

		if(!$manager->has($handle) && $src && $this->checkStyleDependencies($dependencies))
		{
			$manager->set($handle, new FileAsset($this->getSourcePath($src)));

			foreach($pages as $page)
			{
				$this->_styles[$page][] = '@'.$handle;
			}
		}

		return '';
	}

	private function prepareStyleHandle($handle)
	{
		return 'style_'.str_replace(array('-', '.'), '_', $handle);
	}

	private function checkStyleDependencies(array &$dependencies)
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

		$current_page = $this->getCurrentPage();

		// Check if non-WordPress dependencies are present.
		foreach($diff as $key => $dependency)
		{
			unset($dependencies[$key]);
			$dependency = '@'.$this->prepareStyleHandle($dependency);
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
	public function removeStyle($handle, array $options = array())
	{
		$pages = isset($options['page']) ? (array)$options['page'] : array('all');
		$style_handle = $this->prepareStyleHandle($handle);
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
	public function adminPrintStyles()
	{
		// TODO: Think how to improve this call
		$styles = call_user_func_array('array_merge', $this->_styles);
		$asset = new Optimization\Asset\Minified\Stylesheet($this->_factory, $styles, array('page' => 'all', 'location' => 'admin'));
		$css = $asset->getAsset();

		wp_enqueue_style('jigoshop_web_optimization_admin_styles', JIGOSHOP_URL.'/cache/assets/'.$this->getAssetName($css));
	}

	private function getAssetName(AssetInterface $asset)
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
	public function adminPrintScripts()
	{
		// TODO: Think how to improve this call
		$scripts = call_user_func_array('array_merge', $this->_scripts);
		$asset = new Optimization\Asset\Javascript($this->_factory, $scripts, array('page' => 'all', 'location' => 'admin'));
		$js = $asset->getAsset();

		wp_enqueue_script('jigoshop_web_optimization_admin_scripts', JIGOSHOP_URL.'/cache/assets/'.$this->getAssetName($js), array_unique($this->_dependencies));

		foreach($this->_localizations as $object => $values)
		{
			wp_localize_script('jigoshop_web_optimization_admin_scripts', $object, $values);
		}
	}

	/**
	 * Prints frontend styles.
	 */
	public function frontPrintStyles()
	{
		$current_page = $this->getCurrentPage();
		$styles = array_merge(
			$this->_styles[JIGOSHOP_ALL],
			$this->_styles[$current_page]
		);
		$asset = new Optimization\Asset\Minified\Stylesheet($this->_factory, $styles, array('page' => $current_page, 'location' => 'frontend'));
		$css = $asset->getAsset();

		wp_enqueue_style('jigoshop_web_optimization', JIGOSHOP_URL.'/cache/assets/'.$this->getAssetName($css));
	}

	/**
	 * Prints frontend scripts.
	 */
	public function frontPrintScripts()
	{
		$current_page = $this->getCurrentPage();
		$scripts = array_merge(
			$this->_scripts[JIGOSHOP_ALL],
			$this->_scripts[$current_page]
		);
		$asset = new Optimization\Asset\Minified\Javascript($this->_factory, $scripts, array('page' => $current_page, 'location' => 'frontend'));
		$js = $asset->getAsset();

		wp_enqueue_script('jigoshop_web_optimization', JIGOSHOP_URL.'/cache/assets/'.$this->getAssetName($js), array_unique($this->_dependencies));

		foreach($this->_localizations as $object => $values)
		{
			wp_localize_script('jigoshop_web_optimization', $object, $values);
		}
	}
}
