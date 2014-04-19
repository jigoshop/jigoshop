<?php

namespace Jigoshop\Core;

/**
 * Messages test.
 *
 * @package Jigoshop\Core
 */
class MessagesTest extends \PHPUnit_Framework_TestCase
{
	/** @var Messages */
	private $messages;

	/** @var \Jigoshop\Core\Wordpress */
	private $wordpress;

	public function setUp()
	{
		$this->wordpress = $this->getMock('\\Jigoshop\\Core\\Wordpress');
		$this->messages = new Messages($this->wordpress);
	}

	public function testAddNotice()
	{
		// When
		$this->messages->addNotice('test', false);

		// Then
		$notices = $this->messages->getNotices();
		$this->assertCount(1, $notices);
		$this->assertEquals('test', $notices[0]);
	}

	public function testAddWarning()
	{
		// When
		$this->messages->addWarning('test', false);

		// Then
		$warnings = $this->messages->getWarnings();
		$this->assertCount(1, $warnings);
		$this->assertEquals('test', $warnings[0]);
	}

	public function testAddError()
	{
		// When
		$this->messages->addError('test', false);

		// Then
		$errors = $this->messages->getErrors();
		$this->assertCount(1, $errors);
		$this->assertEquals('test', $errors[0]);
	}

	public function testPreserveMessages()
	{
		// Given
		$this->messages->addError('test', false);
		$this->messages->addError('preserved', true);

		// When
		$this->messages->preserveMessages();

		// Then
		$errors = $_SESSION[Messages::ERRORS];
		$this->assertCount(2, $this->messages->getErrors());
		$this->assertCount(1, $errors);
		$this->assertEquals('preserved', $errors[0]['message']);
	}
}