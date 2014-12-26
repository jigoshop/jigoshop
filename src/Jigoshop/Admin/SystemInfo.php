<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

/**
 * Jigoshop system info page.
 *
 * @package Jigoshop\Admin
 * @author Amadeusz Starzykiewicz
 */
class SystemInfo implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
	}

	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('System Information', 'jigoshop');
	}

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return Admin::MENU;
	}

	/**
	 * @return string Required capability to view the page.
	 */
	public function getCapability()
	{
		return 'manage_jigoshop';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return 'jigoshop_system_information';
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		Render::output('admin/system_info', array(
			'wpdb' => $this->wp->getWPDB(),
			'show_on_front' => $this->wp->getOption('show_on_front'),
			'page_on_front' => $this->wp->getOption('page_on_front'),
			'page_for_posts' => $this->wp->getOption('page_for_posts'),
		));
	}
}
