<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;

/**
 * General tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class GeneralTab implements TabInterface
{
	const SLUG = 'general';

	/** @var array */
	private $options;

	public function __construct(Options $options)
	{
		$this->options = $options->get(self::SLUG);
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('General', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return self::SLUG;
	}

	/**
	 * @return array List of items to display.
	 */
	public function getSections()
	{
		return array(
			array(
				'title' => __('Main', 'jigoshop'),
				'fields' => array(
					array(
						'name' => '[name]',
						'title' => __('Shop name', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['name'],
					),
					array(
						'name' => '[email]',
						'title' => __('Administrator e-mail', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['email'],
					),
					array(
						'name' => '[show_message]',
						'id' => 'show_message',
						'title' => __('Display custom message?', 'jigoshop'),
						'type' => 'checkbox',
						'value' => $this->options['show_message'],
						'tip' => __('Add custom message on top of each page of your website.', 'jigoshop'),
					),
					array(
						'name' => '[message]',
						'id' => 'custom_message',
						'title' => __('Message text', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['message'],
						'classes' => array('hidden'),
					),
				),
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
		return $settings;
	}
}
