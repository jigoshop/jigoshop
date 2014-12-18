<?php

namespace Jigoshop\Core;

use Jigoshop\Frontend\Pages;
use Mockery as m;

/**
 * Pages test.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class PagesTest extends \PHPUnit_Framework_TestCase
{
	/** @var m\MockInterface */
	private $wp;
	/** @var m\MockInterface */
	private $options;

	/** @before */
	public function prepare()
	{
		$this->wp = m::mock('WPAL\Wordpress');
		$this->options = m::mock('Jigoshop\Core\Options');
	}

	/** @test */
	public function getAvailable()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$expected = array(
			Pages::CART,
			Pages::CHECKOUT,
			Pages::PRODUCT,
			Pages::PRODUCT_CATEGORY,
			Pages::PRODUCT_LIST,
			Pages::PRODUCT_TAG,
			Pages::ALL,
		);

		// When
		$available = $pages->getAvailable();

		// Then
		$this->assertEquals($expected, $available);
	}

	/** @test */
	public function isAccount()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::ACCOUNT))->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->andReturn(true);

		// When
		$positive = $pages->isAccount();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isAccountNegative()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::ACCOUNT))->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->andReturn(false);

		// When
		$negative = $pages->isAccount();

		// Then
		$this->assertEquals(false, $negative);
	}

	/** @test */
	public function getAdminPageProduct()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$currentScreen = new \stdClass();
		$currentScreen->post_type = Types::PRODUCT;
		$this->wp->shouldReceive('getCurrentScreen')->andReturn($currentScreen);

		// When
		$result = $pages->getAdminPage();

		// Then
		$this->assertEquals(Types::PRODUCT, $result);
	}

	/** @test */
	public function getAdminPageJigoshop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$currentScreen = new \stdClass();
		$currentScreen->post_type = 'test';
		$currentScreen->id = 'jigoshop';
		$this->wp->shouldReceive('getCurrentScreen')->andReturn($currentScreen);

		// When
		$result = $pages->getAdminPage();

		// Then
		$this->assertEquals('jigoshop', $result);
	}

	/** @test */
	public function getNotAdminPage()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('getCurrentScreen')->andReturn(null);

		// When
		$result = $pages->getAdminPage();

		// Then
		$this->assertEquals(false, $result);
	}

	/** @test */
	public function getAdminPageNotJigoshop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$currentScreen = new \stdClass();
		$currentScreen->post_type = 'test';
		$currentScreen->id = 'test';
		$this->wp->shouldReceive('getCurrentScreen')->once()->andReturn($currentScreen);

		// When
		$result = $pages->getAdminPage();

		// Then
		$this->assertEquals(false, $result);
	}

	// This is important to have IsNotAjax test BEFORE IsAjax as latter one defines constants
	/** @test */
	public function isNotAjax()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);

		// When
		$result = $pages->isAjax();

		// Then
		$this->assertEquals(false, $result);
	}

	/** @test */
	public function isAjaxXmlHttpRequest()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

		// When
		$result = $pages->isAjax();

		// Then
		$this->assertEquals(true, $result);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
	}

	/** @test */
	public function isAjax()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		define('DOING_AJAX', true);

		// When
		$result = $pages->isAjax();

		// Then
		$this->assertEquals(true, $result);
	}

	/** @test */
	public function isCart()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::CART))->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->andReturn(true);

		// When
		$positive = $pages->isCart();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isCartNegative()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::CART))->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->andReturn(false);

		// When
		$negative = $pages->isCart();

		// Then
		$this->assertEquals(false, $negative);
	}

	/** @test */
	public function isCheckout()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::CHECKOUT))->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->andReturn(true);

		// When
		$positive = $pages->isCheckout();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isCheckoutNegative()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::CHECKOUT))->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->andReturn(false);

		// When
		$negative = $pages->isCheckout();

		// Then
		$this->assertEquals(false, $negative);
	}

	/** @test */
	public function isJigoshopShop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(true);

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isJigoshopAccount()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->wp->shouldReceive('isTax')->andReturn(false);
		$this->wp->shouldReceive('isSingular')->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::ACCOUNT))->once()->andReturn('1');
		$this->options->shouldReceive('getPageId')->withArgs(array(m::any()))->andReturn('2');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->once()->andReturn(true);
		$this->wp->shouldReceive('isPage')->withArgs(array(m::any()))->andReturn(false);

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isJigoshopCart()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->wp->shouldReceive('isTax')->andReturn(false);
		$this->wp->shouldReceive('isSingular')->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::CART))->once()->andReturn('1');
		$this->options->shouldReceive('getPageId')->withArgs(array(m::any()))->andReturn('2');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->once()->andReturn(true);
		$this->wp->shouldReceive('isPage')->withArgs(array(m::any()))->andReturn(false);

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isJigoshopCheckout()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->wp->shouldReceive('isTax')->andReturn(false);
		$this->wp->shouldReceive('isSingular')->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::CHECKOUT))->once()->andReturn('1');
		$this->options->shouldReceive('getPageId')->withArgs(array(m::any()))->andReturn('2');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->once()->andReturn(true);
		$this->wp->shouldReceive('isPage')->withArgs(array(m::any()))->andReturn(false);

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isJigoshopOrderTracking()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->wp->shouldReceive('isTax')->andReturn(false);
		$this->wp->shouldReceive('isSingular')->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::ORDER_TRACKING))->once()->andReturn('1');
		$this->options->shouldReceive('getPageId')->withArgs(array(m::any()))->andReturn('2');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->once()->andReturn(true);
		$this->wp->shouldReceive('isPage')->withArgs(array(m::any()))->andReturn(false);

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isNotJigoshop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->wp->shouldReceive('isTax')->andReturn(false);
		$this->wp->shouldReceive('isSingular')->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(m::any()))->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array(m::any()))->andReturn(false);

		// When
		$result = $pages->isJigoshop();

		// Then
		$this->assertEquals(false, $result);
	}

	/** @test */
	public function isProduct()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->wp->shouldReceive('isSingular')->withArgs(array(array(Types::PRODUCT)))->andReturn(true);

		// When
		$positive = $pages->isProduct();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isProductNegative()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->wp->shouldReceive('isSingular')->withArgs(array(array(Types::PRODUCT)))->andReturn(false);

		// When
		$negative = $pages->isProduct();

		// Then
		$this->assertEquals(false, $negative);
	}

	/** @test */
	public function isProductCategory()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isTax')->withArgs(array(Types::PRODUCT_CATEGORY))->andReturn(true);

		// When
		$positive = $pages->isProductCategory();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isProductCategoryNegative()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isTax')->withArgs(array(Types::PRODUCT_CATEGORY))->andReturn(false);

		// When
		$negative = $pages->isProductCategory();

		// Then
		$this->assertEquals(false, $negative);
	}

	/** @test */
	public function isProductListPostType()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(true);

		// When
		$positive = $pages->isProductList();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isProductListShopPage()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::SHOP))->once()->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->once()->andReturn(true);

		// When
		$positive = $pages->isProductList();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isProductListProductCategory()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::SHOP))->once()->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->once()->andReturn(false);
		$this->wp->shouldReceive('isTax')->withArgs(array(Types::PRODUCT_CATEGORY))->andReturn(true);

		// When
		$positive = $pages->isProductList();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isProductListProductTag()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::SHOP))->once()->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->once()->andReturn(false);
		$this->wp->shouldReceive('isTax')->withArgs(array(Types::PRODUCT_TAG))->andReturn(true);
		$this->wp->shouldReceive('isTax')->withArgs(array(Types::PRODUCT_CATEGORY))->andReturn(false);

		// When
		$positive = $pages->isProductList();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isProductTag()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isTax')->withArgs(array(Types::PRODUCT_TAG))->andReturn(true);

		// When
		$positive = $pages->isProductTag();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isProductTagNegative()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isTax')->withArgs(array(Types::PRODUCT_TAG))->andReturn(false);

		// When
		$negative = $pages->isProductTag();

		// Then
		$this->assertEquals(false, $negative);
	}

	/** @test */
	public function isShopProductList()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(true);

		// When
		$positive = $pages->isShop();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isShopProduct()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::SHOP))->once()->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->once()->andReturn(false);
		$this->wp->shouldReceive('isTax')->andReturn(false);
		$this->wp->shouldReceive('isSingular')->withArgs(array(array(Types::PRODUCT)))->andReturn(true);

		// When
		$positive = $pages->isShop();

		// Then
		$this->assertEquals(true, $positive);
	}

	/** @test */
	public function isNotShop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->shouldReceive('isPostTypeArchive')->withArgs(array(Types::PRODUCT))->andReturn(false);
		$this->options->shouldReceive('getPageId')->withArgs(array(Pages::SHOP))->once()->andReturn('1');
		$this->wp->shouldReceive('isPage')->withArgs(array('1'))->once()->andReturn(false);
		$this->wp->shouldReceive('isTax')->andReturn(false);
		$this->wp->shouldReceive('isSingular')->withArgs(array(array(Types::PRODUCT)))->andReturn(false);

		// When
		$result = $pages->isShop();

		// Then
		$this->assertEquals(false, $result);
	}

	/** @test */
	public function isAll()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);

		// When
		$result = $pages->is(Pages::ALL);

		// Then
		$this->assertEquals(true, $result);
	}

	/** @test */
	public function isOneOf()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);

		// When
		$result = $pages->isOneOf(array(Pages::ALL));

		// Then
		$this->assertEquals(true, $result);
	}
}
