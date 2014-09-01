<?php

namespace Jigoshop\Core;

use Mockery as m;

/**
 * Messages test.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class MessagesTest extends \TestCase
{
	/** @var m\MockInterface */
	private $wp;

	/** @before */
	public function prepare()
	{
		$this->wp = m::mock('WPAL\Wordpress');
		$this->wp->shouldReceive('addAction');
	}

	/** @test */
	public function addNotice()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$messages = new Messages($this->wp);

		// When
		$messages->addNotice('test', false);

		// Then
		$notices = $messages->getNotices();
		$this->assertCount(1, $notices);
		$this->assertEquals('test', $notices[0]);
	}

	/** @test */
	public function addWarning()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$messages = new Messages($this->wp);

		// When
		$messages->addWarning('test', false);

		// Then
		$warnings = $messages->getWarnings();
		$this->assertCount(1, $warnings);
		$this->assertEquals('test', $warnings[0]);
	}

	/** @test */
	public function addError()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$messages = new Messages($this->wp);

		// When
		$messages->addError('test', false);

		// Then
		$errors = $messages->getErrors();
		$this->assertCount(1, $errors);
		$this->assertEquals('test', $errors[0]);
	}

	/** @test */
	public function preservingMessages()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$messages = new Messages($this->wp);
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
