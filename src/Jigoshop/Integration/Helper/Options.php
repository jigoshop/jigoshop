<?php

namespace Jigoshop\Integration\Helper;

use Jigoshop\Helper\Country;
use Jigoshop\Integration;

class Options
{
	/**
	 * Parses Jigoshop 1.x options array into Jigoshop 2.x format.
	 *
	 * @param string $id ID of the tab options are for.
	 * @param array $source Source options array.
	 * @return array Formatted options array.
	 */
	public static function parse($id, array $source)
	{
		// Divide options array into sections
		$sections = array();
		$mainSection = array();
		for ($i = 0, $endI = count($source); $i < $endI;) {
			if ($source[$i]['type'] == 'title') {
				// Found section
				$section = array(
					'id' => isset($source[$i]['id']) ? $source[$i]['id'] : sanitize_title($source[$i]['name']),
					'title' => $source[$i]['name'],
					'fields' => array(),
				);
				$i++;

				while ($i < $endI && $source[$i]['type'] != 'title') {
					$section['fields'][] = self::parseOption($id, $source[$i]);
					$i++;
				}

				$sections[] = $section;
			} else {
				$mainSection[] = self::parseOption($id, $source[$i]);
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
	 * @param string $id ID of the tab options are for.
	 * @param $option array Option to convert.
	 * @return array Formatted option.
	 */
	public static function parseOption($id, $option)
	{
		$result = array(
			'title' => isset($option['name']) ? $option['name'] : false,
			'__name' => isset($option['id']) ? $option['id'] : '',
			'description' => isset($option['desc']) ? $option['desc'] : false,
			'tip' => isset($option['tip']) ? $option['tip'] : false,
			'type' => self::getType(isset($option['type']) ? $option['type'] : 'text'),
			'options' => isset($option['choices']) ? $option['choices'] : array(),
			// TODO: classes based on 'extra' field
			// TODO: Some additional options from 'extra' field
		);

		$result['name'] = '['.$result['__name'].']';

		if (in_array($option['type'], array('multi_select_countries', 'single_select_country'))) {
			$result['options'] = Country::getAll();
		}
		if ($option['type'] == 'single_select_page') {
			$result['options'] = self::_getPages();
		}
		if (in_array($option['type'], array('multi_select_countries', 'multicheck'))) {
			$result['multiple'] = true;
		}

		switch ($result['type']) {
			case 'checkbox':
				$result['value'] = 'on';
				$result['checked'] = Integration::getOptions()->get($id.'.'.$result['__name'], isset($option['std']) ? $option['std'] == 'yes' : false);
				break;
			case 'user_defined':
				$result['display'] = $option['display'];
				$result['update'] = $option['update'];
			default:
				$result['value'] = Integration::getOptions()->get($id.'.'.$result['__name'], isset($option['std']) ? $option['std'] : false);
		}

		return $result;
	}

	private static function _getPages()
	{
		$pages = array();
		foreach (get_pages() as $page) {
			$pages[$page->ID] = $page->post_title;
		}

		$pages[0] = __('None', 'jigoshop');

		return $pages;
	}

	private static function getType($type)
	{
		switch ($type) {
			case 'text':
			case 'midtext':
			case 'longtext':
				return 'text';
			case 'integer':
			case 'natural':
			case 'decimal':
			case 'range':
				return 'number';
			case 'radio':
			case 'multicheck':
			case 'multi_select_countries':
			case 'single_select_country':
			case 'single_select_page':
				return 'select';
			case 'codeblock':
				return 'textarea';
			default:
				return $type;
		}
	}
}
