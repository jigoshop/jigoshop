<?php

namespace Jigoshop\Core;

use Mockery as m;

/**
 * Assets tests.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class AssetsTest extends \TestCase
{
	/** @var m\MockInterface */
	private $wp;
	/** @var m\MockInterface */
	private $pages;
	/** @var m\MockInterface */
	private $styles;
	/** @var m\MockInterface */
	private $scripts;
	/** @var m\MockInterface */
	private $options;

	/** @before */
	public function prepare()
	{
		$this->wp = m::mock('\WPAL\Wordpress');
		$this->options = m::mock('Jigoshop\Core\Options');
		$this->pages = m::mock('Jigoshop\Core\Pages');
		$this->styles = m::mock('Jigoshop\Helper\Styles');
		$this->scripts = m::mock('Jigoshop\Helper\Scripts');

		$this->wp->shouldReceive('addAction')->matchArgs(array('admin_enqueue_scripts'));
		$this->wp->shouldReceive('addAction')->matchArgs(array('wp_enqueue_scripts'));
	}

	/** @test */
	public function loadAdminAssetsOutsideOfAdmin()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);

		$this->pages->shouldReceive('isAdminPage')->andReturn(false);
		$this->wp->shouldReceive('addFilter')->never();
		$this->styles->shouldReceive('add')->once();
		$this->scripts->shouldReceive('add')->never();

		// When
		$assets->loadAdminAssets();

		// Then no errors should arise
	}

	/** @test */
	public function loadAdminAssets()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);

		$this->pages->shouldReceive('isAdminPage')->andReturn(true);
		$this->wp->shouldReceive('addFilter')->never();

		$this->styles->shouldReceive('add')->withArgs(array('jigoshop_admin_icons_style', m::any()))->once();
		$this->styles->shouldReceive('add')->withArgs(array('jigoshop_admin_styles', m::any()))->once();
		$this->styles->shouldReceive('add')->withArgs(array('jquery-ui-jigoshop-styles', m::any()))->once();
		$this->styles->shouldReceive('add')->withArgs(array('jigoshop-required', m::any()))->once();

		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-select2', m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_blockui', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_backend', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jquery_flot', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jquery_flot_pie', m::any(), m::any(), m::any()))->once();

		// When
		$assets->loadAdminAssets();

		// Then no errors should arise
	}

	/** @test */
	public function loadAdminAssetsAutosave()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);

		$this->pages->shouldReceive('isAdminPage')->andReturn('order'); // TODO: Insert proper constant containing order post type
//		$this->wp->shouldReceive('addFilter')->once()->withArgs(array('script_loader_src')); // TODO: Re-enable this line when orders autosave is up and running

		$this->styles->shouldReceive('add')->withArgs(array('jigoshop_admin_icons_style', m::any()))->once();
		$this->styles->shouldReceive('add')->withArgs(array('jigoshop_admin_styles', m::any()))->once();
		$this->styles->shouldReceive('add')->withArgs(array('jquery-ui-jigoshop-styles', m::any()))->once();
		$this->styles->shouldReceive('add')->withArgs(array('jigoshop-required', m::any()))->once();

		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-select2', m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_blockui', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_backend', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jquery_flot', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jquery_flot_pie', m::any(), m::any(), m::any()))->once();

		// When
		$assets->loadAdminAssets();

		// Then no errors should arise
	}

	/** @test */
	public function disableAutosave()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);

		// When
		$autosave = $assets->disableAutoSave('test', 'autosave');
		$normal = $assets->disableAutoSave('test', 'normal');

		// Then
		$this->assertEquals('', $autosave);
		$this->assertEquals('test', $normal);
	}

	/** @test */
	public function loadFrontendAssetsNoCSS()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);

		$this->wp->shouldReceive('getStylesheetDirectory')->once()->andReturn(JIGOSHOP_DIR);
		$this->options->shouldReceive('get')->withArgs(array('disable_css'))->andReturn('yes');
		$this->options->shouldReceive('get')->withArgs(array('disable_prettyphoto'))->andReturn('yes');

		$this->styles->shouldReceive('add')->never();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_global', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_blockui', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-cart', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-checkout', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-single-product', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-countries', m::any(), m::any(), m::any()))->once();

		// When
		$assets->loadFrontendAssets();

		// Then no errors should arise
	}

	/** @test */
	public function loadFrontendAssets()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);
		$this->wp->shouldReceive('getStylesheetDirectory')->once()->andReturn(JIGOSHOP_DIR);
		$this->options->shouldReceive('get')->withArgs(array('disable_css'))->andReturn('no');
		$this->options->shouldReceive('get')->withArgs(array('load_frontend_css'))->andReturn('yes');
		$this->options->shouldReceive('get')->withArgs(array('disable_prettyphoto'))->andReturn('no');

		$this->styles->shouldReceive('add')->withArgs(array('jigoshop_theme_styles', m::any()))->once();
		$this->styles->shouldReceive('add')->withArgs(array('jigoshop_styles', m::any()))->once();

		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_global', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('prettyphoto', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_blockui', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-cart', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-checkout', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-single-product', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-countries', m::any(), m::any(), m::any()))->once();

		// When
		$assets->loadFrontendAssets();

		// Then no errors should arise
	}

	/** @test */
	public function loadFrontendAssetsNoFrontend()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);
		$this->wp->shouldReceive('getStylesheetDirectory')->once()->andReturn(JIGOSHOP_DIR);
		$this->options->shouldReceive('get')->withArgs(array('disable_css'))->andReturn('no');
		$this->options->shouldReceive('get')->withArgs(array('load_frontend_css'))->andReturn('no');
		$this->options->shouldReceive('get')->withArgs(array('disable_prettyphoto'))->andReturn('no');

		$this->styles->shouldReceive('add')->withArgs(array('jigoshop_theme_styles', m::any(), m::any(), m::any()))->never();
		$this->styles->shouldReceive('add')->withArgs(array('jigoshop_styles', m::any()))->once();

		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_global', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('prettyphoto', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop_blockui', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-cart', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-checkout', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-single-product', m::any(), m::any(), m::any()))->once();
		$this->scripts->shouldReceive('add')->withArgs(array('jigoshop-countries', m::any(), m::any(), m::any()))->once();

		// When
		$assets->loadFrontendAssets();

		// Then no errors should arise
	}
}
