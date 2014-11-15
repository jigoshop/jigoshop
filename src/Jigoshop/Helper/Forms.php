<?php

namespace Jigoshop\Helper;

use Jigoshop\Exception;

class Forms
{
	protected static $checkboxTemplate = 'forms/checkbox';
	protected static $selectTemplate = 'forms/select';
	protected static $textTemplate = 'forms/text';
	protected static $constantTemplate = 'forms/constant';
	protected static $hiddenTemplate = 'forms/hidden';
	protected static $textareaTemplate = 'forms/textarea';

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
	 * Returns disabled string for inputs.
	 *
	 * @param $status bool Disable field.
	 * @return string
	 */
	public static function disabled($status)
	{
		if ($status) {
			return ' disabled="disabled"';
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
			'value' => 'on',
			'checked' => false,
			'disabled' => false,
			'classes' => array(),
			'description' => false,
			'tip' => false,
			'options' => array(),
			'hidden' => false,
			'size' => 10,
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
			'disabled' => false,
			'classes' => array(),
			'description' => false,
			'tip' => false,
			'options' => array(),
			'hidden' => false,
			'size' => 10,
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
			'disabled' => false,
			'classes' => array(),
			'description' => false,
			'tip' => false,
			'options' => array(),
			'hidden' => false,
			'size' => 10,
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
	 * Outputs textarea field.
	 *
	 * @param $field array Field parameters.
	 * @throws \Jigoshop\Exception
	 *
	 * // TODO: Describe field parameters.
	 */
	public static function textarea($field)
	{
		$defaults = array(
			'id' => null,
			'name' => null,
			'label' => null,
			'value' => false,
			'rows' => 3,
			'disabled' => false,
			'classes' => array(),
			'description' => false,
			'tip' => false,
			'options' => array(),
			'hidden' => false,
			'size' => 10,
		);
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$textareaTemplate, $field);
	}

	/**
	 * Outputs hidden field.
	 *
	 * @param $field array Field parameters.
	 * @throws \Jigoshop\Exception
	 *
	 * // TODO: Describe field parameters.
	 */
	public static function hidden($field)
	{
		$defaults = array(
			'id' => null,
			'name' => null,
			'value' => false,
			'placeholder' => '',
			'classes' => array(),
			'options' => array(),
		);
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$hiddenTemplate, $field);
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
			'hidden' => false,
			'size' => 10,
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
	 * Outputs field based on specified type.
	 *
	 * @param $type string Field type.
	 * @param $field array Field definition.
	 */
	public static function field($type, $field)
	{
		switch ($type) {
			case 'text':
				self::text($field);
				break;
			case 'select':
				self::select($field);
				break;
			case 'checkbox':
				self::checkbox($field);
				break;
			case 'textarea':
				self::textarea($field);
				break;
			case 'hidden':
				self::hidden($field);
				break;
			case 'constant':
				self::constant($field);
				break;
		}
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
