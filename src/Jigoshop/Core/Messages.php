<?php

namespace Jigoshop\Core;

use WPAL\Wordpress;

/**
 * Class containing Jigoshop messages.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class Messages
{
	const NOTICES = 'jigoshop_notices';
	const WARNINGS = 'jigoshop_warnings';
	const ERRORS = 'jigoshop_errors';

	private $notices = array();
	private $warnings = array();
	private $errors = array();

	public function __construct(Wordpress $wp)
	{
		session_start();
		if (isset($_SESSION[self::NOTICES])) {
			$this->notices = $_SESSION[self::NOTICES];
		}
		if (isset($_SESSION[self::WARNINGS])) {
			$this->warnings = $_SESSION[self::WARNINGS];
		}
		if (isset($_SESSION[self::ERRORS])) {
			$this->errors = $_SESSION[self::ERRORS];
		}

		$wp->addAction('shutdown', array($this, 'preserveMessages'));
	}

	/**
	 * @param $message string Notice message.
	 * @param $persistent bool Is this message persistent (available after redirect)?
	 */
	public function addNotice($message, $persistent = true)
	{
		$this->notices[] = array(
			'message' => $message,
			'persistent' => $persistent,
		);
	}

	/**
	 * @return bool Whether there are notices to show.
	 */
	public function hasNotices()
	{
		return !empty($this->notices);
	}

	/**
	 * @return array Stored notices.
	 */
	public function getNotices()
	{
		$notices = array_map(function ($item){
			return $item['message'];
		}, $this->notices);
		$this->notices = array();
		return $notices;
	}

	/**
	 * @param $message string Warning message.
	 * @param $persistent bool Is this message persistent (available after redirect)?
	 */
	public function addWarning($message, $persistent = true)
	{
		$this->warnings[] = array(
			'message' => $message,
			'persistent' => $persistent,
		);
	}

	/**
	 * @return bool Whether there are warnings to show.
	 */
	public function hasWarnings()
	{
		return !empty($this->warnings);
	}

	/**
	 * @return array Stored warnings.
	 */
	public function getWarnings()
	{
		$warnings = array_map(function ($item){
			return $item['message'];
		}, $this->warnings);
		$this->warnings = array();
		return $warnings;
	}

	/**
	 * @param $message string Error message.
	 * @param $persistent bool Is this message persistent (available after redirect)?
	 */
	public function addError($message, $persistent = true)
	{
		$this->errors[] = array(
			'message' => $message,
			'persistent' => $persistent,
		);
	}

	/**
	 * @return bool Whether there are errors to show.
	 */
	public function hasErrors()
	{
		return !empty($this->errors);
	}

	/**
	 * @return array Stored errors.
	 */
	public function getErrors()
	{
		$errors = array_map(function ($item){
			return $item['message'];
		}, $this->errors);
		$this->errors = array();
		return $errors;
	}

	/**
	 * Preserves messages storing them to PHP session.
	 */
	public function preserveMessages()
	{
		$_SESSION[self::NOTICES] = array_values(array_filter($this->notices, function ($item){
			return $item['persistent'];
		}));
		$_SESSION[self::WARNINGS] = array_values(array_filter($this->warnings, function ($item){
			return $item['persistent'];
		}));
		$_SESSION[self::ERRORS] = array_values(array_filter($this->errors, function ($item){
			return $item['persistent'];
		}));
		session_write_close();
	}

	/**
	 * Removes all stored messages.
	 */
	public function clear()
	{
		$this->notices = array();
		$this->warnings = array();
		$this->errors = array();
		$this->preserveMessages();
	}
}
