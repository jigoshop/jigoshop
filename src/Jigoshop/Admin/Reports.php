<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;

/**
 * Jigoshop reports admin page.
 *
 * @package Jigoshop\Admin
 */
class Reports implements PageInterface
{
	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Reports', 'jigoshop');
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
		return 'view_jigoshop_reports';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return 'jigoshop_reports';
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		// TODO: Implement display() method.
	}
}
