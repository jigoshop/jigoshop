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

	public function __construct(Wordpress $wordpress)
	{
		if(isset($_SESSION[self::NOTICES]))
		{
			$this->notices = $_SESSION[self::NOTICES];
		}
		if(isset($_SESSION[self::WARNINGS]))
		{
			$this->warnings = $_SESSION[self::WARNINGS];
		}
		if(isset($_SESSION[self::ERRORS]))
		{
			$this->errors = $_SESSION[self::ERRORS];
		}

		$wordpress->addAction('shutdown', array($this, 'preserveMessages'));
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
	 * @return array Stored notices.
	 */
	public function getNotices()
	{
		return array_map(function($item){
			return $item['message'];
		}, $this->notices);
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
	 * @return array Stored warnings.
	 */
	public function getWarnings()
	{
		return array_map(function($item){
			return $item['message'];
		}, $this->warnings);
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
	 * @return array Stored errors.
	 */
	public function getErrors()
	{
		return array_map(function($item){
			return $item['message'];
		}, $this->errors);
	}

	/**
	 * Preserves messages storing them to PHP session.
	 */
	public function preserveMessages()
	{
		$_SESSION[self::NOTICES] = array_values(array_filter($this->notices, function($item){
			return $item['persistent'];
		}));
		$_SESSION[self::WARNINGS] = array_values(array_filter($this->warnings, function($item){
			return $item['persistent'];
		}));
		$_SESSION[self::ERRORS] = array_values(array_filter($this->errors, function($item){
			return $item['persistent'];
		}));
	}
}