<?php

namespace Jigoshop\Core;

use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

/**
 * Class for adding required assets.
 * TODO: Rethink if all assets are needed.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class Assets
{
	/** @var \Jigoshop\Core\Pages */
	private $pages;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Helper\Styles */
	private $styles;
	/** @var \Jigoshop\Helper\Scripts */
	private $scripts;
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp, Pages $pages, Options $options, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->pages = $pages;
		$this->options = $options;
		$this->styles = $styles;
		$this->scripts = $scripts;
		$wp->addAction('admin_enqueue_scripts', array($this, 'loadAdminAssets'));
		$wp->addAction('wp_enqueue_scripts', array($this, 'loadFrontendAssets'));
	}

	public function loadAdminAssets()
	{
		/* Our setting icons */
//		$this->styles->add('jigoshop_admin_icons_style', JIGOSHOP_URL.'/assets/css/admin-icons.css');

		$adminPage = $this->pages->isAdminPage();
		if (!$adminPage) {
			return;
		}

		$this->styles->add('jigoshop-admin', JIGOSHOP_URL.'/assets/css/admin.css');
		$this->styles->add('jigoshop-vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
		$this->scripts->add('jigoshop-admin-product', JIGOSHOP_URL.'/assets/js/admin/product.js');
		$this->scripts->add('jigoshop-vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
//		$this->scripts->add('bootstrap', JIGOSHOP_URL.'/assets/js/bootstrap.min.js', array('jquery'));

		// Insert Select2
//		$this->styles->add('select2', JIGOSHOP_URL.'/assets/css/select2.css');
//		$this->styles->add('select2', JIGOSHOP_URL.'/assets/css/select2-bootstrap.css', array('bootstrap'));
//		$this->scripts->add('select2', JIGOSHOP_URL.'/assets/js/select2.min.js', array('jquery'));

//		$this->styles->add('jigoshop_admin_styles', JIGOSHOP_URL.'/assets/css/admin.css');
//		$this->styles->add('jquery-ui-jigoshop-styles', JIGOSHOP_URL.'/assets/css/jquery-ui-1.8.16.jigoshop.css');
//		$this->styles->add('jigoshop-required', JIGOSHOP_URL.'/assets/css/required.css');

//		$this->scripts->add('jigoshop-select2', JIGOSHOP_URL.'/assets/js/select2.min.js', array('jquery'));
//		$this->scripts->add('jigoshop_blockui', JIGOSHOP_URL.'/assets/js/blockui.js', array('jquery'), array('version' => '2.4.6'));
//		$this->scripts->add('jigoshop_backend', JIGOSHOP_URL.'/assets/js/jigoshop_backend.js', array('jquery'), array('version' => '1.0'));

//		$this->scripts->add('jquery_flot', JIGOSHOP_URL.'/assets/js/jquery.flot.min.js', array('jquery'), array(
//			'version' => '1.0',
//			'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop') // TODO: Properly fetch page names
//		));
//		$this->scripts->add('jquery_flot_pie', JIGOSHOP_URL.'/assets/js/jquery.flot.pie.min.js', array('jquery'), array(
//			'version' => '1.0',
//			'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop') // TODO: Properly fetch page names
//		));

		/*
		 * Disable autosaves on the order and coupon pages. Prevents the javascript alert when modifying.
		 * `wp_deregister_script( 'autosave' )` would produce errors, so we use a filter instead.
		 */
//		if (in_array($adminPage, array(Types::ORDER, Types::COUPON), true)) {
//			$this->wp->addFilter('script_loader_src', array($this, 'disableAutoSave'), 10, 2);
//		}
	}

	/**
	 * @param $src string Script URI to load
	 * @param $handle string Handle name
	 * @return string Script URI to load
	 * @internal
	 */
	public function disableAutoSave($src, $handle)
	{
		if ('autosave' != $handle) {
			return $src;
		}

		return '';
	}

	public function loadFrontendAssets()
	{
		$frontend_css = JIGOSHOP_URL.'/assets/css/frontend.css';
		$theme_css = file_exists($this->wp->getStylesheetDirectory().'/jigoshop/style.css') ? $this->wp->getStylesheetDirectoryUri().'/jigoshop/style.css' : $frontend_css;

		if ($this->options->get('disable_css') == 'no') {
			if ($this->options->get('load_frontend_css') == 'yes') {
				$this->styles->add('jigoshop_theme_styles', $frontend_css);
			}
			$this->styles->add('jigoshop_styles', $theme_css);
		}

		$this->scripts->add('jigoshop_global', JIGOSHOP_URL.'/assets/js/global.js', array('jquery'), array('in_footer' => true));

		if ($this->options->get('disable_prettyphoto') == 'no') {
			$this->scripts->add('prettyphoto', JIGOSHOP_URL.'/assets/js/jquery.prettyPhoto.js', array('jquery'), array('in_footer' => true));
		}

		$this->scripts->add('jigoshop_blockui', JIGOSHOP_URL.'/assets/js/blockui.js', array('jquery'), array('in_footer' => true));
		$this->scripts->add('jigoshop-cart', JIGOSHOP_URL.'/assets/js/cart.js', array('jquery'), array('in_footer' => true, 'page' => Pages::CART));
		$this->scripts->add('jigoshop-checkout', JIGOSHOP_URL.'/assets/js/checkout.js', array('jquery'), array('in_footer' => true, 'page' => Pages::CHECKOUT));
		$this->scripts->add('jigoshop-single-product', JIGOSHOP_URL.'/assets/js/single-product.js', array('jquery'), array('in_footer' => true, 'page' => Pages::PRODUCT));
		$this->scripts->add('jigoshop-countries', JIGOSHOP_URL.'/assets/js/countries.js', array(), array('in_footer' => true, 'page' => array(Pages::CHECKOUT, Pages::CART)));
	}
}
