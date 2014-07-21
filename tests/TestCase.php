<?php

class TestCase extends PHPUnit_Framework_TestCase
{
	/** @after */
	public function closeMockery()
	{
		\Mockery::close();
	}
}
