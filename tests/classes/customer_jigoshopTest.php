<?php

// Load the plugin
require_once('classes/jigoshop_options_interface.php');
require_once('classes/jigoshop_options.class.php');
require_once('classes/abstract/jigoshop_base.class.php');
require_once('classes/abstract/jigoshop_singleton.class.php');
require_once('classes/jigoshop_session.class.php');
require_once('classes/jigoshop_customer.class.php');
require_once('classes/jigoshop_countries.class.php');

class WP_Test_Jigoshop_Customer extends WP_UnitTestCase
{

	public function setUp() {
		parent::setUp();
		$this->customer = jigoshop_customer::instance();
	}

	public function tearDown() {
		Jigoshop_Singleton::reset();
		parent::tearDown();
	}

	public function test_customer_isnt_outside_base()
	{
		$this->assertFalse($this->customer->is_customer_outside_base(false));
	}

	public function test_customer_is_outside_base()
	{
		// Change the customer country
		$this->customer->set_shipping_country( 'AT' );
		$this->assertTrue($this->customer->is_customer_outside_base(true));
	}

	public function test_set_state() {

		// Set the postcode & test
		$this->customer->set_postcode('W1 6LD');
		$this->assertEquals('w16ld', $this->customer->get_postcode());
	}
}