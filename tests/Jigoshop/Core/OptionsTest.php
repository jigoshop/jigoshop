<?php

namespace Jigoshop\Core;

use Mockery as m;

/**
 * Options tests.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class OptionsTest extends \TestCase
{
	/** @var m\MockInterface */
	private $wp;

	/** @before */
	public function prepare()
	{
		$this->wp = m::mock('WPAL\Wordpress');
		$this->wp->shouldReceive('getOption')->withArgs(array('jigoshop'))->andReturn(array(
			'test' => 'value',
			'load_frontend_css' => 'no',
			'array' => array('the' => 'value'),
			'catalog_sort' => array(
				'order_by' => 'post_title',
				'order' => 'ASC',
				'test' => 'value',
			),
		));
		$this->wp->shouldReceive('applyFilters')->withArgs(array('jigoshop\image\sizes', m::any()))->andReturn(array(
			'test_size' => array(
				'crop' => false,
				'width' => 100,
				'height' => 100,
			),
		));
		$this->wp->shouldReceive('applyFilters')->withArgs(array('jigoshop\image\size\crop', m::any(), m::any()))->andReturn(false);
		$this->wp->shouldReceive('addImageSize');
		$this->wp->shouldReceive('addAction')->withArgs(array('shutdown', m::any()))->once();
	}

	/** @test */
	public function getOptionValue()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$options = new Options($this->wp);

		// When
		$option = $options->get('test');

		// Then
		$this->assertEquals('value', $option);
	}

	/** @test */
	public function updateOptionValue()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$options = new Options($this->wp);

		// When
		$options->update('test', 'test_value');

		// Then
		$this->assertEquals('test_value', $options->get('test'));
	}

	/** @test */
	public function getImageSizes()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$options = new Options($this->wp);

		// When
		$sizes = $options->getImageSizes();

		// Then
		$this->assertCount(1, $sizes);
		$this->assertEquals($sizes[0]['crop'], false);
		$this->assertEquals($sizes[0]['width'], 100);
		$this->assertEquals($sizes[0]['height'], 100);
	}

	/** @test */
	public function updateAndSaveOptionsValue()
	{
		// Given
		$this->wp->shouldReceive('updateOption')->once();
		/** @noinspection PhpParamsInspection */
		$options = new Options($this->wp);

		// When
		$options->update('test', 'test_value');
		$options->saveOptions();

		// Then
		$this->assertEquals('test_value', $options->get('test'));
	}

	/** @test */
	public function defaultOptionsMerging()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$options = new Options($this->wp);

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

	/** @test */
	public function gettingNestedValues()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$options = new Options($this->wp);

		// When / Then
		$this->assertEquals('value', $options->get('array.the'));
		$this->assertEquals('post_title', $options->get('catalog_sort.order_by'));
	}
}
