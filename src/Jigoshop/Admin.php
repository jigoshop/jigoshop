<?php

namespace Jigoshop;

use Jigoshop\Admin\Dashboard;
use Jigoshop\Admin\PageInterface;
use Jigoshop\Admin\Product\Attributes;
use Jigoshop\Admin\Reports;
use Jigoshop\Admin\Settings;
use Jigoshop\Admin\SystemInfo;

/**
 * Class for handling administration panel.
 *
 * @package Jigoshop
 * @author Jigoshop
 */
class Admin
{
	/** @var \Jigoshop\Core */
	private $core;
	/** @var array */
	private $pages = array(
		'jigoshop' => array(),
		'products' => array(),
		'orders' => array(),
	);
	private $dashboard;
	private $settings;
	private $systemInfo;

	public function __construct(Core $core)
	{
		$this->core = $core;
		$this->dashboard = new Dashboard($core->getDatabase(), $core->getOptions(), $core->getOrderService(), $core->getProductService());
		$this->settings = new Settings();
		$this->systemInfo = new SystemInfo();
		$this->addPage('jigoshop', $this->dashboard);
		$this->addPage('jigoshop', new Reports());
		$this->addPage('products', new Attributes());

		add_action('admin_menu', array($this, 'beforeMenu'), 9);
		add_action('admin_menu', array($this, 'afterMenu'), 50);
	}

	/**
	 * Adds new page to Jigoshop admin panel.
	 * Available parents:
	 *   * jigoshop - main Jigoshop menu,
	 *   * products - Jigoshop products menu
	 *   * orders - Jigoshop orders menu
	 *
	 * @param $parent string Slug of parent page.
	 * @param $page PageInterface Page to add.
	 * @throws Exception When trying to add page not in Jigoshop menus.
	 */
	public function addPage($parent, PageInterface $page)
	{
		if(!isset($this->pages[$parent]))
		{
			throw new Exception('Trying to add page to invalid parent. Available ones are: '.join(', ', array_keys($this->pages)));
		}

		$this->pages[$parent][] = $page;
	}

	/**
	 * @return Core\Options Options handler.
	 */
	public function getOptions()
	{
		return $this->core->getOptions();
	}

	/**
	 * Adds Jigoshop menus.
	 */
	public function beforeMenu()
	{
		global $menu;

		if(current_user_can('manage_jigoshop'))
		{
			$menu[54] = array('', 'read', 'separator-jigoshop', '', 'wp-menu-separator jigoshop');
		}

		// TODO: Add Jigoshop icon!
		add_menu_page(__('Jigoshop'), __('Jigoshop'), 'manage_jigoshop', 'jigoshop', array($this->dashboard, 'display'), null, 55);
		foreach($this->pages['jigoshop'] as $page)
		{
			/** @var $page PageInterface */
			add_submenu_page('jigoshop', $page->getTitle(), $page->getTitle(), $page->getCapability(), $page->getMenuSlug(), array($page, 'display'));
		}

		foreach($this->pages['products'] as $page)
		{
			/** @var $page PageInterface */
			add_submenu_page('edit.php?post_type=product', $page->getTitle(), $page->getTitle(), $page->getCapability(), $page->getMenuSlug(), array($page, 'display'));
		}

		foreach($this->pages['orders'] as $page)
		{
			/** @var $page PageInterface */
			add_submenu_page('edit.php?post_type=shop_order', $page->getTitle(), $page->getTitle(), $page->getCapability(), $page->getMenuSlug(), array($page, 'display'));
		}

		do_action('jigoshop\\admin\\before_menu');
	}

	/**
	 * Adds Jigoshop settings and system information menus (to the end of Jigoshop sub-menu).
	 */
	public function afterMenu()
	{
		$admin_page = add_submenu_page('jigoshop', $this->settings->getTitle(), $this->settings->getTitle(), $this->settings->getCapability(),
			$this->settings->getMenuSlug(), array($this->settings, 'display'));
		add_action('admin_print_scripts-'.$admin_page, array($this, 'settings_scripts'));
		add_action('admin_print_styles-'.$admin_page, array($this, 'settings_styles'));

		add_submenu_page('jigoshop', $this->systemInfo->getTitle(), $this->systemInfo->getTitle(), $this->systemInfo->getCapability(),
			$this->systemInfo->getMenuSlug(), array($this->systemInfo, 'display'));

		do_action('jigoshop\\admin\\after_menu');
	}
}