<?php

namespace Jigoshop\Helper;

use Jigoshop\Exception;

class Forms
{
	protected static $checkboxTemplate = 'forms/checkbox';
	protected static $selectTemplate = 'forms/select';
	protected static $textTemplate = 'forms/text';
	protected static $constantTemplate = 'forms/constant';

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
	 * @throws \Jigoshop\Exception
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
			'classes' => array(),
			'description' => false,
			'tip' => false,
			'options' => array(),
		);
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			throw new Exception('Field "%s" must have a name!', serialize($field));
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$checkboxTemplate, $field);
	}

	/**
	 * Outputs select field.
	 *
	 * @param $field array Field parameters.
	 * @throws \Jigoshop\Exception
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
			'classes' => array(),
			'description' => false,
			'tip' => false,
			'options' => array(),
		);
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			throw new Exception('Field "%s" must have a name!', serialize($field));
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		if($field['multiple']){
			$field['name'] .= '[]';
		}

		$field['description'] = esc_html($field['description']);

		Render::output(static::$selectTemplate, $field);
	}

	/**
	 * Outputs simple text field.
	 *
	 * @param $field array Field parameters.
	 * @throws \Jigoshop\Exception
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
			'classes' => array(),
			'description' => false,
			'tip' => false,
			'options' => array(),
		);
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$textTemplate, $field);
	}


	/**
	 * Outputs simple static (constant) field.
	 *
	 * @param $field array Field parameters.
	 * @throws \Jigoshop\Exception
	 *
	 * // TODO: Describe field parameters.
	 */
	public static function constant($field)
	{
		// TODO: Refactor text() and constant() (and maybe others) to use some generic protected function.
		$defaults = array(
			'id' => null,
			'name' => null,
			'label' => null,
			'value' => false,
			'placeholder' => '',
			'classes' => array(),
			'description' => false,
			'tip' => false,
			'options' => array(),
		);
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$constantTemplate, $field);
	}

	/**
	 * Prepares field name to be used as field ID.
	 *
	 * @param $name string Name to prepare.
	 * @return string Prepared ID.
	 */
	public static function prepareIdFromName($name)
	{
		return str_replace(array('[', ']'), array('_', ''), $name);
	}
}
