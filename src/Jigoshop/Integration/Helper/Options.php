<?php

namespace Jigoshop\Integration\Helper;

class Options
{
	/**
	 * Parses Jigoshop 1.x options array into Jigoshop 2.x format.
	 *
	 * @param array $options Source options array.
	 * @return array Formatted options array.
	 */
	public static function parse(array $options)
	{
		// Divide options array into sections
		$sections = array();
		$mainSection = array();
		for ($i = 0, $endI = count($options); $i < $endI;) {
			if ($options[$i]['type'] == 'title') {
				// Found section
				$section = array(
					'id' => isset($options[$i]['id']) ? $options[$i]['id'] : sanitize_title($options[$i]['name']),
					'title' => $options[$i]['name'],
					'fields' => array(),
				);
				$i++;

				while ($i < $endI && $options[$i]['type'] != 'title') {
					$section['fields'][] = self::parseOption($options[$i]);
					$i++;
				}

				$sections[] = $section;
			} else {
				$mainSection[] = self::parseOption($options[$i]);
				$i++;
			}
		}

		if (!empty($mainSection)) {
			$sections = array_merge(array(
				'id' => 'main',
				'title' => __('Main', 'jigoshop'),
				'fields' => $mainSection,
			), $sections);
		}

		return $sections;
	}

	/**
	 * Parses single Jigoshop 1.x option into Jigoshop 2.x format.
	 *
	 * @param $option array Option to convert.
	 * @return array Formatted option.
	 */
	public static function parseOption($option)
	{
		$result = array(
			'title' => isset($option['name']) ? $option['name'] : false,
			'name' => isset($option['id']) ? $option['id'] : '',
			'description' => isset($option['desc']) ? $option['desc'] : false,
			'tip' => isset($option['tip']) ? $option['tip'] : false,
			'type' => self::getType(isset($option['type']) ? $option['type'] : 'text'),
			'options' => isset($option['choices']) ? $option['choices'] : array(),
			// TODO: classes based on 'extra' field
			// TODO: Some additional options from 'extra' field
		);

		switch ($result['type']) {
			case 'checkbox':
				$result['value'] = 'on';
				$result['checked'] = isset($option['std']) ? $option['std'] === 'yes' : false;
				break;
			default:
				$result['value'] = isset($option['std']) ? $option['std'] : false;
		}

		return $result;
	}

	private static function getType($type)
	{
		switch ($type) {
			case 'text':
			case 'longtext':
				return 'text';
			default:
				return $type;
		}
	}
}
