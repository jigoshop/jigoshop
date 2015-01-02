<?php

namespace Jigoshop\Admin\Settings;

/**
 * Interface for admin setting tabs.
 *
 * @package Jigoshop\Admin\Settings
 */
interface TabInterface
{
	/**
	 * @return string Title of the tab.
	 */
	public function getTitle();

	/**
	 * @return string Tab slug.
	 */
	public function getSlug();

	/**
	 * @return array List of items to display.
	 */
	public function getSections();

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate($settings);
}
