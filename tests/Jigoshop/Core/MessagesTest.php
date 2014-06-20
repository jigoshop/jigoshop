<?php

namespace Jigoshop\Core;

/**
 * Messages test.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class MessagesTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $wordpress;

	public function setUp()
	{
		$this->wordpress = $this->getMock('\\WPAL\\Wordpress');
		$this->wordpress->expects($this->any())
			->method('addAction')
			->with($this->anything());
	}

	public function testAddNotice()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$messages = new Messages($this->wordpress);

		// When
		$messages->addNotice('test', false);

		// Then
		$notices = $messages->getNotices();
		$this->assertCount(1, $notices);
		$this->assertEquals('test', $notices[0]);
	}

	public function testAddWarning()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$messages = new Messages($this->wordpress);

		// When
		$messages->addWarning('test', false);

		// Then
		$warnings = $messages->getWarnings();
		$this->assertCount(1, $warnings);
		$this->assertEquals('test', $warnings[0]);
	}

	public function testAddError()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$messages = new Messages($this->wordpress);

		// When
		$messages->addError('test', false);

		// Then
		$errors = $messages->getErrors();
		$this->assertCount(1, $errors);
		$this->assertEquals('test', $errors[0]);
	}

	public function testPreservingMessages()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$messages = new Messages($this->wordpress);
		$messages->addError('test', false);
		$messages->addError('preserved', true);

		// When
		$messages->preserveMessages();

		// Then
		$errors = $_SESSION[Messages::ERRORS];
		$this->assertCount(2, $messages->getErrors());
		$this->assertCount(1, $errors);
		$this->assertEquals('preserved', $errors[0]['message']);
	}
}