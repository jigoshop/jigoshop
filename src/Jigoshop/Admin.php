<?php

namespace Jigoshop;

use Jigoshop\Admin\Dashboard;
use Jigoshop\Admin\PageInterface;
use Jigoshop\Admin\Product\Attributes;
use Jigoshop\Admin\Reports;
use Jigoshop\Admin\Settings;
use Jigoshop\Admin\SystemInfo;
use Jigoshop\Core\Options;
use WPAL\Wordpress;

/**
 * Class for handling administration panel.
 *
 * @package Jigoshop
 * @author Amadeusz Starzykiewicz
 */
class Admin
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var array */
	private $pages = array(
		'jigoshop' => array(),
		'products' => array(),
		'orders' => array(),
	);
	private $dashboard;
	private $settings;
	private $systemInfo;

	public function __construct(Wordpress $wp, Options $options, Dashboard $dashboard, Settings $settings, SystemInfo $systemInfo, Reports $reports, Attributes $attributes)
	{
		$this->wp = $wp;
		$this->dashboard = $dashboard;
		$this->settings = $settings;
		$this->systemInfo = $systemInfo;
		$this->addPage('jigoshop', $this->dashboard);
		$this->addPage('jigoshop', $reports);
		$this->addPage('products', $attributes);

		$wp->addAction('admin_menu', array($this, 'beforeMenu'), 9);
		$wp->addAction('admin_menu', array($this, 'afterMenu'), 50);
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
		if (!isset($this->pages[$parent])) {
			throw new Exception('Trying to add page to invalid parent. Available ones are: '.join(', ', array_keys($this->pages)));
		}

		$this->pages[$parent][] = $page;
	}

	/**
	 * Adds Jigoshop menus.
	 */
	public function beforeMenu()
	{
		$menu = $this->wp->getMenu();

		if ($this->wp->currentUserCan('manage_jigoshop')) {
			$menu[54] = array('', 'read', 'separator-jigoshop', '', 'wp-menu-separator jigoshop');
		}

		$this->wp->addMenuPage(__('Jigoshop'), __('Jigoshop'), 'manage_jigoshop', 'jigoshop', array($this->dashboard, 'display'), null, 55);
		foreach ($this->pages['jigoshop'] as $page) {
			/** @var $page PageInterface */
			$this->wp->addSubmenuPage('jigoshop', $page->getTitle(), $page->getTitle(), $page->getCapability(), $page->getMenuSlug(), array($page, 'display'));
		}

		foreach ($this->pages['products'] as $page) {
			/** @var $page PageInterface */
			$this->wp->addSubmenuPage('edit.php?post_type=product', $page->getTitle(), $page->getTitle(), $page->getCapability(), $page->getMenuSlug(), array($page, 'display'));
		}

		foreach ($this->pages['orders'] as $page) {
			/** @var $page PageInterface */
			$this->wp->addSubmenuPage('edit.php?post_type=shop_order', $page->getTitle(), $page->getTitle(), $page->getCapability(), $page->getMenuSlug(), array($page, 'display'));
		}

		$this->wp->doAction('jigoshop\\admin\\before_menu');
	}

	/**
	 * Adds Jigoshop settings and system information menus (at the end of Jigoshop sub-menu).
	 */
	public function afterMenu()
	{
		$this->wp->addSubmenuPage('jigoshop', $this->settings->getTitle(), $this->settings->getTitle(), $this->settings->getCapability(),
			$this->settings->getMenuSlug(), array($this->settings, 'display'));
		$this->wp->addSubmenuPage('jigoshop', $this->systemInfo->getTitle(), $this->systemInfo->getTitle(), $this->systemInfo->getCapability(),
			$this->systemInfo->getMenuSlug(), array($this->systemInfo, 'display'));

		$this->wp->doAction('jigoshop\\admin\\after_menu');
	}
}