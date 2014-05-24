<?php

namespace Jigoshop\Core;

/**
 * Options tests.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
	/** @var \WPAL\Wordpress */
	private $wordpress;

	public function setUp()
	{
		$this->wordpress = $this->getMock('\\WPAL\\Wordpress');
		$this->wordpress->expects($this->any())
			->method('getOption')
			->with($this->equalTo('jigoshop'))
			->will($this->returnValue(array(
				'test' => 'value',
				'load_frontend_css' => 'no',
				'array' => array('the' => 'value'),
				'catalog_sort' => array(
					'order_by' => 'post_title',
					'order' => 'ASC',
					'test' => 'value',
				),
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
	}

	public function testGetOptionValue()
	{
		// Given
		$options = new Options($this->wordpress);

		// When
		$option = $options->get('test');

		// Then
		$this->assertEquals('value', $option);
	}

	public function testUpdateOptionValue()
	{
		// Given
		$options = new Options($this->wordpress);

		// When
		$options->update('test', 'test_value');

		// Then
		$this->assertEquals('test_value', $options->get('test'));
	}

	public function testGetImageSizes()
	{
		// Given
		$options = new Options($this->wordpress);

		// When
		$sizes = $options->getImageSizes();

		// Then
		$this->assertCount(1, $sizes);
		$this->assertEquals($sizes[0]['crop'], false);
		$this->assertEquals($sizes[0]['width'], 100);
		$this->assertEquals($sizes[0]['height'], 100);
	}

	public function testUpdateAndSaveOptionsValue()
	{
		// Given
		$wordpress = $this->wordpress;
		/** @var $wordpress \PHPUnit_Framework_MockObject_MockObject */
		$wordpress->expects($this->once())
			->method('updateOption')
			->withAnyParameters();
		$options = new Options($this->wordpress);

		// When
		$options->update('test', 'test_value');
		$options->saveOptions();

		// Then
		$this->assertEquals('test_value', $options->get('test'));
	}

	public function testDefaultOptionsMerging()
	{
		// Given
		$options = new Options($this->wordpress);

		// When / Then
		$this->assertEquals('simple', $options->get('cache_mechanism'));
		$this->assertEquals('value', $options->get('test'));
		$this->assertEquals('no', $options->get('load_frontend_css'));
		$this->assertEquals('non-existent', $options->get('non-existent', 'non-existent'));
		$this->assertEquals(array('the' => 'value'), $options->get('array'));
		$catalogSort = $options->get('catalog_sort');
		$this->assertCount(3, $catalogSort);
		$this->assertEquals('post_title', $catalogSort['order_by']);
		$this->assertEquals('ASC', $catalogSort['order']);
		$this->assertEquals('value', $catalogSort['test']);
	}

	public function testGettingNestedValues()
	{
		// Given
		$options = new Options($this->wordpress);

		// When / Then
		$this->assertEquals('value', $options->get('array.the'));
		$this->assertEquals('post_title', $options->get('catalog_sort.order_by'));
	}
}
