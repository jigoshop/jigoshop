<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Exception;

/**
 * Validation exception - thrown on settings validation.
 *
 * @package Jigoshop\Admin\Settings
 */
class ValidationException extends Exception
{
	/** @var array */
	private $fields;

	/**
	 * @param string $message Message to show to the user.
	 * @param array $fields List of invalid fields with their messages.
	 * @param int $code Code to return.
	 */
	public function __construct($message = '', array $fields = array(), $code = 0)
	{
		parent::__construct($message, $code);
		$this->fields = $fields;
	}

	/**
	 * Adds new error for selected field.
	 *
	 * @param string $field Field name.
	 * @param string $error Error message.
	 */
	public function addFieldError($field, $error)
	{
		if (!isset($this->fields[$field])) {
			$this->fields[$field] = array();
		}

		$this->fields[$field][] = $error;
	}

	/**
	 * @return array List of fields with their errors.
	 */
	public function getErrors()
	{
		return $this->fields;
	}
}
