<?php

namespace Jigoshop\Frontend\Page;

/**
 * Interface for templates and their actions.
 *
 * @package Jigoshop\Frontend
 */
interface PageInterface
{
	/**
	 * Executes actions associated with selected page.
	 */
	public function action();

	/**
	 * Renders page template.
	 *
	 * @return string Page template.
	 */
	public function render();
}
