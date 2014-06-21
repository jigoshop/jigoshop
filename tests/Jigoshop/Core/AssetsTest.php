<?php

namespace Jigoshop\Core;

/**
 * Assets tests.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class AssetsTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $wp;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $pages;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $styles;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $scripts;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $options;

	public function setUp()
	{
		$this->wp = $this->getMock('\\WPAL\\Wordpress');
		$this->options = $this->getMockBuilder('\\Jigoshop\\Core\\Options')->disableOriginalConstructor()->getMock();
		$this->pages = $this->getMockBuilder('\\Jigoshop\\Core\\Pages')->disableOriginalConstructor()->getMock();
		$this->styles = $this->getMockBuilder('\\Jigoshop\\Helper\\Styles')->disableOriginalConstructor()->getMock();
		$this->scripts = $this->getMockBuilder('\\Jigoshop\\Helper\\Scripts')->disableOriginalConstructor()->getMock();

		$this->wp->expects($this->any())
			->method('addAction')
			->with($this->logicalOr($this->equalTo('admin_enqueue_scripts'), $this->equalTo('wp_enqueue_scripts')), $this->anything());
	}

	public function testLoadAdminAssetsOutsideOfAdmin()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);

		$this->pages->expects($this->any())
			->method('isAdminPage')
			->will($this->returnValue(false));
		$this->wp->expects($this->never())
			->method('addFilter');
		$this->styles->expects($this->once())
			->method('add')
			->with($this->anything(), $this->anything());
		$this->scripts->expects($this->never())
			->method('add');

		// When
		$assets->loadAdminAssets();

		// Then no errors should arise
	}

	public function testLoadAdminAssets()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);

		$this->pages->expects($this->any())
			->method('isAdminPage')
			->will($this->returnValue(true));
		$this->wp->expects($this->never())
			->method('addFilter');

		$this->styles->expects($this->at(0))
			->method('add')
			->with('jigoshop_admin_icons_style', $this->anything());
		$this->styles->expects($this->at(1))
			->method('add')
			->with('jigoshop_admin_styles', $this->anything());
		$this->styles->expects($this->at(2))
			->method('add')
			->with('jquery-ui-jigoshop-styles', $this->anything());
		$this->styles->expects($this->at(3))
			->method('add')
			->with('jigoshop-required', $this->anything());

		$this->scripts->expects($this->at(0))
			->method('add')
			->with('jigoshop-select2', $this->anything());
		$this->scripts->expects($this->at(1))
			->method('add')
			->with('jigoshop_blockui', $this->anything());
		$this->scripts->expects($this->at(2))
			->method('add')
			->with('jigoshop_backend', $this->anything());
		$this->scripts->expects($this->at(3))
			->method('add')
			->with('jquery_flot', $this->anything());
		$this->scripts->expects($this->at(4))
			->method('add')
			->with('jquery_flot_pie', $this->anything());

		// When
		$assets->loadAdminAssets();

		// Then no errors should arise
	}

	public function testLoadAdminAssetsAutosave()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);

		$this->pages->expects($this->once())
			->method('isAdminPage')
			->will($this->returnValue(Types::ORDER));
		$this->wp->expects($this->once())
			->method('addFilter')
			->with($this->equalTo('script_loader_src'), $this->anything(), $this->anything(), $this->anything());

		$this->styles->expects($this->at(0))
			->method('add')
			->with('jigoshop_admin_icons_style', $this->anything());
		$this->styles->expects($this->at(1))
			->method('add')
			->with('jigoshop_admin_styles', $this->anything());
		$this->styles->expects($this->at(2))
			->method('add')
			->with('jquery-ui-jigoshop-styles', $this->anything());
		$this->styles->expects($this->at(3))
			->method('add')
			->with('jigoshop-required', $this->anything());

		$this->scripts->expects($this->at(0))
			->method('add')
			->with('jigoshop-select2', $this->anything());
		$this->scripts->expects($this->at(1))
			->method('add')
			->with('jigoshop_blockui', $this->anything());
		$this->scripts->expects($this->at(2))
			->method('add')
			->with('jigoshop_backend', $this->anything());
		$this->scripts->expects($this->at(3))
			->method('add')
			->with('jquery_flot', $this->anything());
		$this->scripts->expects($this->at(4))
			->method('add')
			->with('jquery_flot_pie', $this->anything());

		// When
		$assets->loadAdminAssets();

		// Then no errors should arise
	}

	public function testDisableAutosave()
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

	public function testLoadFrontendAssetsNoCSS()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);

		$this->wp->expects($this->once())
			->method('getStylesheetDirectory')
			->will($this->returnValue(JIGOSHOP_DIR));
		$this->options->expects($this->any())
			->method('get')
			->with($this->logicalOr($this->equalTo('disable_css'), $this->equalTo('disable_prettyphoto')))
			->will($this->returnCallback(function($option){
				switch($option){
					case 'disable_css':
						return 'yes';
					case 'disable_prettyphoto':
						return 'yes';
					default:
						return 'no';
				}
			}));

		$this->styles->expects($this->never())
			->method('add');
		$this->scripts->expects($this->at(0))
			->method('add')
			->with('jigoshop_global', $this->anything());
		$this->scripts->expects($this->at(1))
			->method('add')
			->with('jigoshop_blockui', $this->anything());
		$this->scripts->expects($this->at(2))
			->method('add')
			->with('jigoshop-cart', $this->anything());
		$this->scripts->expects($this->at(3))
			->method('add')
			->with('jigoshop-checkout', $this->anything());
		$this->scripts->expects($this->at(4))
			->method('add')
			->with('jigoshop-single-product', $this->anything());
		$this->scripts->expects($this->at(5))
			->method('add')
			->with('jigoshop-countries', $this->anything());

		// When
		$assets->loadFrontendAssets();

		// Then no errors should arise
	}

	public function testLoadFrontendAssets()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);
		$this->wp->expects($this->once())
			->method('getStylesheetDirectory')
			->will($this->returnValue(JIGOSHOP_DIR));
		$this->options->expects($this->any())
			->method('get')
			->with($this->logicalOr($this->equalTo('disable_css'), $this->equalTo('load_frontend_css'), $this->equalTo('disable_prettyphoto')))
			->will($this->returnCallback(function($option){
				switch($option){
					case 'disable_css':
						return 'no';
					case 'load_frontend_css':
						return 'yes';
					case 'disable_prettyphoto':
						return 'no';
					default:
						return 'no';
				}
			}));

		$this->styles->expects($this->at(0))
			->method('add')
			->with($this->equalTo('jigoshop_theme_styles'), $this->anything());
		$this->styles->expects($this->at(1))
			->method('add')
			->with($this->equalTo('jigoshop_styles'), $this->anything());

		$this->scripts->expects($this->at(0))
			->method('add')
			->with('jigoshop_global', $this->anything());
		$this->scripts->expects($this->at(1))
			->method('add')
			->with('prettyphoto', $this->anything());
		$this->scripts->expects($this->at(2))
			->method('add')
			->with('jigoshop_blockui', $this->anything());
		$this->scripts->expects($this->at(3))
			->method('add')
			->with('jigoshop-cart', $this->anything());
		$this->scripts->expects($this->at(4))
			->method('add')
			->with('jigoshop-checkout', $this->anything());
		$this->scripts->expects($this->at(5))
			->method('add')
			->with('jigoshop-single-product', $this->anything());
		$this->scripts->expects($this->at(6))
			->method('add')
			->with('jigoshop-countries', $this->anything());

		// When
		$assets->loadFrontendAssets();

		// Then no errors should arise
	}

	public function testLoadFrontendAssetsNoFrontend()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$assets = new Assets($this->wp, $this->pages, $this->options, $this->styles, $this->scripts);
		$this->wp->expects($this->once())
			->method('getStylesheetDirectory')
			->will($this->returnValue(JIGOSHOP_DIR));
		$this->options->expects($this->any())
			->method('get')
			->with($this->logicalOr($this->equalTo('disable_css'), $this->equalTo('load_frontend_css'), $this->equalTo('disable_prettyphoto')))
			->will($this->returnCallback(function($option){
				switch($option){
					case 'disable_css':
						return 'no';
					case 'load_frontend_css':
						return 'no';
					case 'disable_prettyphoto':
						return 'no';
					default:
						return 'no';
				}
			}));

		$this->styles->expects($this->at(0))
			->method('add')
			->with($this->equalTo('jigoshop_styles'), $this->anything());

		$this->scripts->expects($this->at(0))
			->method('add')
			->with('jigoshop_global', $this->anything());
		$this->scripts->expects($this->at(1))
			->method('add')
			->with('prettyphoto', $this->anything());
		$this->scripts->expects($this->at(2))
			->method('add')
			->with('jigoshop_blockui', $this->anything());
		$this->scripts->expects($this->at(3))
			->method('add')
			->with('jigoshop-cart', $this->anything());
		$this->scripts->expects($this->at(4))
			->method('add')
			->with('jigoshop-checkout', $this->anything());
		$this->scripts->expects($this->at(5))
			->method('add')
			->with('jigoshop-single-product', $this->anything());
		$this->scripts->expects($this->at(6))
			->method('add')
			->with('jigoshop-countries', $this->anything());

		// When
		$assets->loadFrontendAssets();

		// Then no errors should arise
	}
}
