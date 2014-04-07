<?php

namespace Jigoshop\Admin;

/**
 * Jigoshop settings.
 *
 * @package Jigoshop\Admin
 * @author Jigoshop
 */
class Settings implements PageInterface
{
	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Settings', 'jigoshop');
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
		return 'settings';
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		// TODO: Implement display() method.
	}
}