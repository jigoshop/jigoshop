<?php

namespace Jigoshop\Core;

/**
 * Class OptionsTest
 *
 * @package Jigoshop\Core
 * @author Jigoshop
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $wordpress;
	/** @var \Jigoshop\Core\Options */
	private $options;

	public function setUp()
	{
		$this->wordpress = $this->getMock('Jigoshop\\Core\\Wordpress');
		$this->wordpress->expects($this->any())
			->method('getOption')
			->with($this->equalTo('jigoshop'))
			->will($this->returnValue(array(
				'test' => 'value'
			)));
		$this->wordpress->expects($this->any())
			->method('applyFilters')
			->with($this->logicalOr($this->equalTo('jigoshop\\image\\sizes'), $this->equalTo('jigoshop\\image\\size\\crop')), $this->anything())
			->will($this->returnCallback(function($filter){
				switch($filter)
				{
					case 'jigoshop\\image\\sizes':
						return array(
								'test_size' => array(
									'crop' => false,
									'width' => 100,
									'height' => 100,
								)
							);
					case 'jigoshop\\image\\size\\crop':
						return false;
				}
				return null;
			}));
		$this->options = new Options($this->wordpress);
	}

	public function testGetOptionValue()
	{
		// When
		$option = $this->options->get('test');

		// Then
		$this->assertEquals('value', $option);
	}

	public function testUpdateOptionValue()
	{
		// When
		$this->options->update('test', 'test_value');

		// Then
		$this->assertEquals('test_value', $this->options->get('test'));
	}

	public function testGetImageSizes()
	{
		// When
		$sizes = $this->options->getImageSizes();

		// Then
		$this->assertCount(1, $sizes);
		$this->assertEquals($sizes[0]['crop'], false);
		$this->assertEquals($sizes[0]['width'], 100);
		$this->assertEquals($sizes[0]['height'], 100);
	}

	public function testUpdateAndSaveOptionsValue()
	{
		// Given
		$this->wordpress->expects($this->any())
			->method('updateOption')
			->withAnyParameters();

		// When
		$this->options->update('test', 'test_value');
		$this->options->saveOptions();

		// Then
		$this->assertEquals('test_value', $this->options->get('test'));
	}
}
