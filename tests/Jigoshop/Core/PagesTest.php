<?php

namespace Jigoshop\Core;

/**
 * Pages test.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class PagesTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $wp;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $options;

	public function setUp()
	{
		$this->wp = $this->getMock('\\WPAL\\Wordpress');
		$this->options = $this->getMockBuilder('\\Jigoshop\\Core\\Options')->disableOriginalConstructor()->getMock();
	}

	public function testGetAvailable()
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

	public function testIsAccount()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->equalTo(Pages::ACCOUNT))
			->will($this->returnValue('1'));
		$this->wp->expects($this->at(0))
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(true));
		$this->wp->expects($this->at(1))
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(false));

		// When
		$positive = $pages->isAccount();
		$negative = $pages->isAccount();

		// Then
		$this->assertEquals(true, $positive);
		$this->assertEquals(false, $negative);
	}

	public function testIsAdminPageProduct()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$currentScreen = new \stdClass();
		$currentScreen->post_type = PostTypes::PRODUCT;
		$this->wp->expects($this->once())
			->method('getCurrentScreen')
			->will($this->returnValue($currentScreen));

		// When
		$result = $pages->isAdminPage();

		// Then
		$this->assertEquals(PostTypes::PRODUCT, $result);
	}

	public function testIsAdminPageJigoshop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$currentScreen = new \stdClass();
		$currentScreen->post_type = 'test';
		$currentScreen->id = 'jigoshop';
		$this->wp->expects($this->once())
			->method('getCurrentScreen')
			->will($this->returnValue($currentScreen));

		// When
		$result = $pages->isAdminPage();

		// Then
		$this->assertEquals('jigoshop', $result);
	}

	public function testIsNotAdminPage()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('getCurrentScreen')
			->will($this->returnValue(null));

		// When
		$result = $pages->isAdminPage();

		// Then
		$this->assertEquals(false, $result);
	}

	public function testIsAdminPageNotJigoshop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$currentScreen = new \stdClass();
		$currentScreen->post_type = 'test';
		$currentScreen->id = 'test';
		$this->wp->expects($this->once())
			->method('getCurrentScreen')
			->will($this->returnValue($currentScreen));
		$this->wp->expects($this->once())
			->method('getCurrentScreen')
			->will($this->returnValue($currentScreen));

		// When
		$result = $pages->isAdminPage();

		// Then
		$this->assertEquals(false, $result);
	}

	// This is important to have IsNotAjax test BEFORE IsAjax as latter one defines constants
	public function testIsNotAjax()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);

		// When
		$result = $pages->isAjax();

		// Then
		$this->assertEquals(false, $result);
	}

	public function testIsAjaxXmlHttpRequest()
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

	public function testIsAjax()
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

	public function testIsCart()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->equalTo(Pages::CART))
			->will($this->returnValue('1'));
		$this->wp->expects($this->at(0))
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(true));
		$this->wp->expects($this->at(1))
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(false));

		// When
		$positive = $pages->isCart();
		$negative = $pages->isCart();

		// Then
		$this->assertEquals(true, $positive);
		$this->assertEquals(false, $negative);
	}

	public function testIsCheckout()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->equalTo(Pages::CHECKOUT))
			->will($this->returnValue('1'));
		$this->wp->expects($this->at(0))
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(true));
		$this->wp->expects($this->at(1))
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(false));

		// When
		$positive = $pages->isCheckout();
		$negative = $pages->isCheckout();

		// Then
		$this->assertEquals(true, $positive);
		$this->assertEquals(false, $negative);
	}

	public function testIsJigoshopShop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('isPostTypeArchive')
			->with($this->equalTo(PostTypes::PRODUCT))
			->will($this->returnValue(true));

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsJigoshopAccount()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->anything())
			->will($this->returnCallback(function($page){
				if($page === Pages::ACCOUNT){
					return '1';
				}
				return '2';
			}));

		$this->wp->expects($this->any())
			->method('isPage')
			->with($this->anything())
			->will($this->returnCallback(function($page){
				if($page == '1'){
					return true;
				}
				return false;
			}));

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsJigoshopCart()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->anything())
			->will($this->returnCallback(function($page){
				if($page === Pages::CART){
					return '1';
				}
				return '2';
			}));

		$this->wp->expects($this->any())
			->method('isPage')
			->with($this->anything())
			->will($this->returnCallback(function($page){
				if($page == '1'){
					return true;
				}
				return false;
			}));

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsJigoshopCheckout()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->anything())
			->will($this->returnCallback(function($page){
				if($page === Pages::CHECKOUT){
					return '1';
				}
				return '2';
			}));

		$this->wp->expects($this->any())
			->method('isPage')
			->with($this->anything())
			->will($this->returnCallback(function($page){
				if($page == '1'){
					return true;
				}
				return false;
			}));

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsJigoshopOrderTracking()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->anything())
			->will($this->returnCallback(function($page){
				if($page === Pages::ORDER_TRACKING){
					return '1';
				}
				return '2';
			}));

		$this->wp->expects($this->any())
			->method('isPage')
			->with($this->anything())
			->will($this->returnCallback(function($page){
				if($page == '1'){
					return true;
				}
				return false;
			}));

		// When
		$positive = $pages->isJigoshop();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsNotJigoshop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('isPostTypeArchive')
			->with($this->equalTo(PostTypes::PRODUCT))
			->will($this->returnValue(false));
		$this->wp->expects($this->once())
			->method('isSingular')
			->with($this->anything())
			->will($this->returnValue(false));
		$this->wp->expects($this->any())
			->method('isTax')
			->with($this->anything())
			->will($this->returnValue(false));
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->anything())
			->will($this->returnValue('1'));
		$this->wp->expects($this->any())
			->method('isPage')
			->with($this->anything())
			->will($this->returnValue(false));

		// When
		$result = $pages->isJigoshop();

		// Then
		$this->assertEquals(false, $result);
	}

	public function testIsProduct()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->at(0))
			->method('isSingular')
			->with($this->equalTo(array(PostTypes::PRODUCT)))
			->will($this->returnValue(true));
		$this->wp->expects($this->at(1))
			->method('isSingular')
			->with($this->equalTo(array(PostTypes::PRODUCT)))
			->will($this->returnValue(false));

		// When
		$positive = $pages->isProduct();
		$negative = $pages->isProduct();

		// Then
		$this->assertEquals(true, $positive);
		$this->assertEquals(false, $negative);
	}

	public function testIsProductCategory()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->at(0))
			->method('isTax')
			->with($this->equalTo(PostTypes::PRODUCT_CATEGORY))
			->will($this->returnValue(true));
		$this->wp->expects($this->at(1))
			->method('isTax')
			->with($this->equalTo(PostTypes::PRODUCT_CATEGORY))
			->will($this->returnValue(false));

		// When
		$positive = $pages->isProductCategory();
		$negative = $pages->isProductCategory();

		// Then
		$this->assertEquals(true, $positive);
		$this->assertEquals(false, $negative);
	}

	public function testIsProductListPostType()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('isPostTypeArchive')
			->with($this->equalTo(PostTypes::PRODUCT))
			->will($this->returnValue(true));

		// When
		$positive = $pages->isProductList();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsProductListShopPage()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('isPostTypeArchive')
			->with($this->equalTo(PostTypes::PRODUCT))
			->will($this->returnValue(false));
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->equalTo(Pages::SHOP))
			->will($this->returnValue('1'));
		$this->wp->expects($this->once())
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(true));

		// When
		$positive = $pages->isProductList();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsProductListProductCategory()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('isPostTypeArchive')
			->with($this->equalTo(PostTypes::PRODUCT))
			->will($this->returnValue(false));
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->equalTo(Pages::SHOP))
			->will($this->returnValue('1'));
		$this->wp->expects($this->once())
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(false));
		$this->wp->expects($this->once())
			->method('isTax')
			->with($this->equalTo(PostTypes::PRODUCT_CATEGORY), $this->anything())
			->will($this->returnValue(true));

		// When
		$positive = $pages->isProductList();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsProductListProductTag()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('isPostTypeArchive')
			->with($this->equalTo(PostTypes::PRODUCT))
			->will($this->returnValue(false));
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->equalTo(Pages::SHOP))
			->will($this->returnValue('1'));
		$this->wp->expects($this->once())
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(false));
		$this->wp->expects($this->any())
			->method('isTax')
			->with($this->anything(), $this->anything())
			->will($this->returnCallback(function($tax){
				if($tax == PostTypes::PRODUCT_TAG){
					return true;
				}
				return false;
			}));

		// When
		$positive = $pages->isProductList();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsProductTag()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->at(0))
			->method('isTax')
			->with($this->equalTo(PostTypes::PRODUCT_TAG))
			->will($this->returnValue(true));
		$this->wp->expects($this->at(1))
			->method('isTax')
			->with($this->equalTo(PostTypes::PRODUCT_TAG))
			->will($this->returnValue(false));

		// When
		$positive = $pages->isProductTag();
		$negative = $pages->isProductTag();

		// Then
		$this->assertEquals(true, $positive);
		$this->assertEquals(false, $negative);
	}

	public function testIsShopProductList()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('isPostTypeArchive')
			->with($this->equalTo(PostTypes::PRODUCT))
			->will($this->returnValue(true));

		// When
		$positive = $pages->isShop();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsShopProduct()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('isPostTypeArchive')
			->with($this->equalTo(PostTypes::PRODUCT))
			->will($this->returnValue(false));
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->equalTo(Pages::SHOP))
			->will($this->returnValue('1'));
		$this->wp->expects($this->once())
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(false));
		$this->wp->expects($this->any())
			->method('isTax')
			->with($this->anything(), $this->anything())
			->will($this->returnValue(false));
		$this->wp->expects($this->once())
			->method('isSingular')
			->with($this->equalTo(array(PostTypes::PRODUCT)))
			->will($this->returnValue(true));

		// When
		$positive = $pages->isShop();

		// Then
		$this->assertEquals(true, $positive);
	}

	public function testIsNotShop()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);
		$this->wp->expects($this->once())
			->method('isPostTypeArchive')
			->with($this->equalTo(PostTypes::PRODUCT))
			->will($this->returnValue(false));
		$this->options->expects($this->any())
			->method('getPageId')
			->with($this->equalTo(Pages::SHOP))
			->will($this->returnValue('1'));
		$this->wp->expects($this->once())
			->method('isPage')
			->with($this->equalTo('1'))
			->will($this->returnValue(false));
		$this->wp->expects($this->any())
			->method('isTax')
			->with($this->anything(), $this->anything())
			->will($this->returnValue(false));
		$this->wp->expects($this->once())
			->method('isSingular')
			->with($this->equalTo(array(PostTypes::PRODUCT)))
			->will($this->returnValue(false));

		// When
		$result = $pages->isShop();

		// Then
		$this->assertEquals(false, $result);
	}

	public function testIsAll()
	{
		// Given
		/** @noinspection PhpParamsInspection */
		$pages = new Pages($this->wp, $this->options);

		// When
		$result = $pages->is(Pages::ALL);

		// Then
		$this->assertEquals(true, $result);
	}

	public function testIsOneOf()
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