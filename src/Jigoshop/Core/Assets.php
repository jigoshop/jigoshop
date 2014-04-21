<?php

namespace Jigoshop\Core;

use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Pages;

/**
 * Class for adding required assets.
 *
 * TODO: Rethink if all assets are needed.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class Assets
{
	private $options;

	public function __construct(Options $options)
	{
		$this->options = $options;
		add_action('admin_enqueue_scripts', array($this, 'loadAdminAssets'));
		add_action('wp_enqueue_scripts', array($this, 'loadFrontendAssets'));
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	public function loadAdminAssets()
	{
		/* Our setting icons */
		Styles::add('jigoshop_admin_icons_style', JIGOSHOP_URL.'/assets/css/admin-icons.css');

		$adminPage = \Jigoshop\Helper\Pages::isAdminPage();
		if(!$adminPage)
		{
			return;
		}

		Styles::add('jigoshop_admin_styles', JIGOSHOP_URL.'/assets/css/admin.css');
		Styles::add('jquery-ui-jigoshop-styles', JIGOSHOP_URL.'/assets/css/jquery-ui-1.8.16.jigoshop.css');
		Styles::add('thickbox', false);
		Styles::add('jigoshop-required', JIGOSHOP_URL.'/assets/css/required.css');

		Scripts::add('jigoshop-select2', JIGOSHOP_URL.'/assets/js/select2.min.js', array('jquery'));
		Scripts::add('jquery-ui-datepicker', JIGOSHOP_URL.'/assets/js/jquery-ui-datepicker-1.8.16.min.js', array('jquery'), array('version' => '1.8.16'));
		Scripts::add('jigoshop_blockui', JIGOSHOP_URL.'/assets/js/blockui.js', array('jquery'), array('version' => '2.4.6'));
		Scripts::add('jigoshop_backend', JIGOSHOP_URL.'/assets/js/jigoshop_backend.js', array('jquery'), array('version' => '1.0'));
		Scripts::add('thickbox', false);

		Scripts::add('jquery_flot', JIGOSHOP_URL.'/assets/js/jquery.flot.min.js', array('jquery'), array(
			'version' => '1.0',
			'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop')
		));
		Scripts::add('jquery_flot_pie', JIGOSHOP_URL.'/assets/js/jquery.flot.pie.min.js', array('jquery'), array(
			'version' => '1.0',
			'page' => array('jigoshop_page_jigoshop_reports', 'toplevel_page_jigoshop')
		));

		/*
		 * Disable autosaves on the order and coupon pages. Prevents the javascript alert when modifying.
		 * `wp_deregister_script( 'autosave' )` would produce errors, so we use a filter instead.
		 */
		if($adminPage == 'shop_order' || $adminPage == 'shop_coupon')
		{
			add_filter('script_loader_src', array($this, '_disableAutoSave'), 10, 2);
		}
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _disableAutoSave($src, $handle)
	{
		if('autosave' != $handle)
		{
			return $src;
		}

		return '';
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	public function loadFrontendAssets()
	{
		$frontend_css = JIGOSHOP_URL.'/assets/css/frontend.css';
		$theme_css = file_exists(get_stylesheet_directory().'/jigoshop/style.css') ? get_stylesheet_directory_uri().'/jigoshop/style.css' : $frontend_css;

		if($this->options->get('disable_css') == 'no')
		{
			if($this->options->get('load_frontend_css') == 'yes')
			{
				Styles::add('jigoshop_theme_styles', $frontend_css);
			}
			Styles::add('jigoshop_styles', $theme_css);
		}

		Scripts::add('jigoshop_global', JIGOSHOP_URL.'/assets/js/global.js', array('jquery'), array('in_footer' => true));

		if($this->options->get('disable_prettyphoto') == 'no')
		{
			Scripts::add('prettyphoto', JIGOSHOP_URL.'/assets/js/jquery.prettyPhoto.js', array('jquery'), array('in_footer' => true));
		}

		Scripts::add('jigoshop_blockui', JIGOSHOP_URL.'/assets/js/blockui.js', array('jquery'), array('in_footer' => true));
		Scripts::add('jigoshop-cart', JIGOSHOP_URL.'/assets/js/cart.js', array('jquery'), array('in_footer' => true, 'page' => Pages::CART));
		Scripts::add('jigoshop-checkout', JIGOSHOP_URL.'/assets/js/checkout.js', array('jquery'), array('in_footer' => true, 'page' => Pages::CHECKOUT));
		Scripts::add('jigoshop-single-product', JIGOSHOP_URL.'/assets/js/single-product.js', array('jquery'), array('in_footer' => true, 'page' => Pages::PRODUCT));
		Scripts::add('jigoshop-countries', JIGOSHOP_URL.'/assets/js/countries.js', array(), array('in_footer' => true, 'page' => array(Pages::CHECKOUT, Pages::CART)));
	}
}