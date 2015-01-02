<?php

namespace Jigoshop\Integration\Admin\Settings;

use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Admin\Settings\ValidationException;
use Jigoshop\Integration\Helper\Options;

class Tab implements TabInterface
{
	/** @var string */
	private $title;
	/** @var array */
	private $sections;

	public function __construct($tab, $options)
	{
		$this->title = $tab;
		$this->sections = Options::parse($options);
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return sanitize_title($this->title);
	}

	/**
	 * @return array List of items to display.
	 */
	public function getSections()
	{
		return $this->sections;
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate($settings)
	{
		if (!is_array($settings)) {
			$settings = array();
		}

		foreach ($this->sections as $section) {
			foreach ($section['fields'] as $field) {
				if (isset($field['update'])) {
					$settings[$field['name']] = call_user_func_array($field['update'], $field);
				}
			}
		}

		return $settings;
	}
}
