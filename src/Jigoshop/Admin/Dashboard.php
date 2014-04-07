<?php

namespace Jigoshop\Admin;

/**
 * Jigoshop dashboard.
 *
 * @package Jigoshop\Admin
 * @author Jigoshop
 */
class Dashboard implements PageInterface
{
	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Dashboard', 'jigoshop');
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
		return 'dashboard';
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		// TODO: Implement display() method.
	}
}