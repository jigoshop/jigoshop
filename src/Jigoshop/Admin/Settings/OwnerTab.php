<?php

namespace Jigoshop\Admin\Settings;

/**
 * Owner tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class OwnerTab implements TabInterface
{
	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Owner', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return 'owner';
	}

	/**
	 * @return array List of items to display.
	 */
	public function getFields()
	{
		return array(
			array(
				'id' => 'name',
				'name' => 'owner[name]',
				'title' => 'Owner name',
				'type' => 'text',
				'description' => 'Owner name',
			)
		);
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate(array $settings)
	{
		// TODO: Implement validate() method.
	}
}
