<?php

namespace Jigoshop\Helper;

use Jigoshop\Exception;

class Forms
{
	/**
	 * Returns string for checkboxes if value is checked (value and current are the same).
	 *
	 * @param $value string Value to check.
	 * @param $current string Value to compare.
	 * @return string
	 */
	public static function checked($value, $current)
	{
		if ($value == $current) {
			return ' checked="checked"';
		}

		return '';
	}
	/**
	 * Returns string for selects if value is within selected values.
	 *
	 * @param $value string Value to check.
	 * @param $current string|array Currently selected values.
	 * @return string
	 */
	public static function selected($value, $current)
	{
		if ((is_array($current) && in_array($value, $current)) || $value == $current) {
			return ' selected="selected"';
		}

		return '';
	}

	/**
	 * Outputs simple text field.
	 *
	 * @param $field array Field parameters.
	 *
	 * // TODO: Describe field parameters.
	 */
	public static function checkbox($field)
	{
		$defaults = array(
			'id' => null,
			'name' => null,
			'label' => null,
			'value' => false,
			'classes' => array('checkbox'),
			'description' => false,
			'tip' => false,
			'options' => array(),
		);
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			throw new Exception('Field "%s" must have a name!', serialize($field));
		}

		if (empty($field['id'])) {
			$field['id'] = $field['name'];
		}

		$field['name'] = 'product['.$field['name'].']';

		Render::output('forms/checkbox', $field);
	}

	/**
	 * Outputs select field.
	 *
	 * @param $field array Field parameters.
	 *
	 * // TODO: Describe field parameters.
	 */
	public static function select($field)
	{
		$defaults = array(
			'id' => null,
			'name' => null,
			'label' => null,
			'value' => false,
			'multiple' => false,
			'placeholder' => '',
			'classes' => array('form-control'),
			'description' => false,
			'tip' => false,
			'options' => array(),
		);
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			throw new Exception('Field "%s" must have a name!', serialize($field));
		}

		if (empty($field['id'])) {
			$field['id'] = $field['name'];
		}

		if($field['multiple']){
			$field['name'] .= '[]';
		}

		$field['description'] = esc_html($field['description']);
		$field['name'] = 'product['.$field['name'].']';

		Render::output('forms/select', $field);
	}

	/**
	 * Outputs simple text field.
	 *
	 * @param $field array Field parameters.
	 *
	 * // TODO: Describe field parameters.
	 */
	public static function text($field)
	{
		$defaults = array(
			'id' => null,
			'name' => null,
			'label' => null,
			'value' => false,
			'placeholder' => '',
			'classes' => array('text', 'short'),
			'description' => false,
			'tip' => false,
			'options' => array(),
		);
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			throw new Exception('Field "%s" must have a name!', serialize($field));
		}

		if (empty($field['id'])) {
			$field['id'] = $field['name'];
		}

		$field['name'] = 'product['.$field['name'].']';

		Render::output('forms/text', $field);
	}
}
