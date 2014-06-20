<?php

namespace Jigoshop\Admin;

/**
 * Jigoshop system info page.
 *
 * @package Jigoshop\Admin
 * @author Amadeusz Starzykiewicz
 */
class SystemInfo implements PageInterface
{
	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return \__('System Information', 'jigoshop');
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
		// TODO: Implement display() method.
	}
}