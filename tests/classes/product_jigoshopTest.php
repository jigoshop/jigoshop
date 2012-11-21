<?php
	// @rob: we are getting alot of errors for "Trying to get property of non-object".
	// Only happens on the 3.5.0 beta2 build WITH MU, https://travis-ci.org/#!/chriscct7/jigoshop/jobs/2860365
	// Maybe a WP beta bug?
	
require_once('classes/jigoshop_options_interface.php');
require_once('classes/jigoshop_options.class.php');
require_once('classes/abstract/jigoshop_base.class.php');
require_once('classes/jigoshop.class.php');
require_once('classes/jigoshop_product.class.php');

class WP_Test_Jigoshop_Product extends WP_UnitTestCase
{
	private function _create_product()
	{
		$post = array(
			'post_content' => 'Test Product Content',
			'post_title'	 => 'Test Product',
			'post_type'		 => 'product'
		);
		$this->id = wp_insert_post($post);

		$meta = array(
			'regular_price' => '10',
			'sale_price'		=> '5',
			'tax_status'		=> 'taxable',
			'tax_class'			=> 'standard',
			'sku'						=> 'TESTPROD1',
			'featured'			=> TRUE,
			'manage_stock'	=> TRUE,
			'stock'					=> '12'
		);

		foreach( $meta as $key => $value ) {
			update_post_meta($this->id, $key, $value);
		}

		return new jigoshop_product($this->id);
	}

	public function test_loaded_product()
	{
		$_product = $this->_create_product();

		$this->assertSame('TESTPROD1', $_product->get_sku());
	}

	public function test_reduce_stock()
	{
		$_product = $this->_create_product();
		$_product->reduce_stock(1);
		$this->assertEquals(11, $_product->stock);
	}

	public function test_increase_stock()
	{
		$_product = $this->_create_product();
		$_product->increase_stock(1);
		$this->assertEquals(13, $_product->stock);
	}

	public function test_requires_shipping()
	{
		$_product = $this->_create_product();

		$this->assertTrue($_product->requires_shipping());
	}

	public function test_is_type_simple()
	{
		$_product = $this->_create_product();
		$this->assertTrue($_product->is_type('simple'));
	}

	public function test_is_taxable()
	{
		$_product = $this->_create_product();

		$this->assertTrue($_product->is_taxable());
	}

	public function test_get_title()
	{
		$_product = $this->_create_product();

		$this->assertEquals('Test Product', $_product->get_title());
	}

	public function test_add_to_cart_url()
	{
		$_product = $this->_create_product();
		$this->assertContains('add-to-cart='.$this->id, $_product->add_to_cart_url());
	}

	public function test_managing_stock()
	{
		$_product = $this->_create_product();
		$this->assertTrue($_product->managing_stock());
	}

	public function test_is_in_stock()
	{
		$_product = $this->_create_product();
		$this->assertTrue($_product->is_in_stock());
	}

	public function test_has_enough_stock()
	{
		$_product = $this->_create_product();
		$this->assertTrue($_product->has_enough_stock(10));
		$this->assertFalse($_product->has_enough_stock(9999));
	}

	public function test_get_stock()
	{
		$_product = $this->_create_product();

		$this->assertEquals(12, $_product->get_stock());
	}

	public function test_get_availablity()
	{
		$_product = $this->_create_product();
		$return = $_product->get_availability();
        // @rob two spaces in between 12 and availability...fixed
		$this->assertContains('12  available', $return['availability']);
	}

	public function test_is_featured()
	{
		$_product = $this->_create_product();

		$this->assertTrue($_product->is_featured());
	}

	public function test_is_visible()
	{
		$_product = $this->_create_product();
		$this->assertTrue($_product->is_visible());
	}

	public function test_is_on_sale()
	{
		$_product = $this->_create_product();
		$this->assertTrue($_product->is_on_sale());
	}

	public function test_get_percentage_sale()
	{
		$_product = $this->_create_product();
		$this->assertEquals('50%', $_product->get_percentage_sale());
	}

	public function test_get_price()
	{
		$_product = $this->_create_product();
		$this->assertEquals(5, $_product->get_price());
	}
}