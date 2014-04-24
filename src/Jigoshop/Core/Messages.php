<?php

namespace Jigoshop\Core;

/**
 * Class containing Jigoshop messages.
 *
 * @package Jigoshop\Core
 * @author Jigoshop
 */
class Messages
{
	private $_notices = array();
	private $_warnings = array();
	private $_errors = array();

	public function __construct()
	{
		if(isset($_SESSION['jigoshop_notices']))
		{
			$this->_notices = $_SESSION['jigoshop_notices'];
		}
		if(isset($_SESSION['jigoshop_warnings']))
		{
			$this->_warnings = $_SESSION['jigoshop_warnings'];
		}
		if(isset($_SESSION['jigoshop_errors']))
		{
			$this->_errors = $_SESSION['jigoshop_errors'];
		}

		add_action('shutdown', array($this, '_preserveMessages'));
	}

	/**
	 * @param $message string Notice message.
	 * @param $persistent bool Is this message persistent (available after redirect)?
	 */
	public function addNotice($message, $persistent = true)
	{
		$this->_notices[] = array(
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
		}, $this->_notices);
	}

	/**
	 * @param $message string Warning message.
	 * @param $persistent bool Is this message persistent (available after redirect)?
	 */
	public function addWarning($message, $persistent = true)
	{
		$this->_warnings[] = array(
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
		}, $this->_warnings);
	}

	/**
	 * @param $message string Error message.
	 * @param $persistent bool Is this message persistent (available after redirect)?
	 */
	public function addError($message, $persistent = true)
	{
		$this->_errors[] = array(
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
		}, $this->_errors);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _preserveMessages()
	{
		$_SESSION['jigoshop_notices'] = array_filter($this->_notices, function($item){
			return $item['persistent'];
		});
		$_SESSION['jigoshop_warnings'] = array_filter($this->_warnings, function($item){
			return $item['persistent'];
		});
		$_SESSION['jigoshop_errors'] = array_filter($this->_errors, function($item){
			return $item['persistent'];
		});
	}
}