<?php

namespace Jigoshop\Admin;

/**
 * Interface for admin pages.
 *
 * @package Jigoshop\Admin
 * @author Amadeusz Starzykiewicz
 */
interface PageInterface
{
	/** @return string Title of page. */
	public function getTitle();

	/** @return string Parent of the page string. */
	public function getParent();

	/** @return string Required capability to view the page. */
	public function getCapability();

	/** @return string Menu slug. */
	public function getMenuSlug();

	/** Displays the page. */
	public function display();
}