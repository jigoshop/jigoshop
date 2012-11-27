<?php
// Load the plugin
require_once('classes/abstract/jigoshop_base.class.php');
require_once('classes/jigoshop_countries.class.php');

class WP_Test_Jigoshop_Countries extends WP_UnitTestCase
{
	private $object;

	public function setUp() {
		$this->object = new jigoshop_countries();
	}

	public function tearDown() {
		// unset($_SESSION);
		parent::tearDown();
	}

	public function test_country_has_states() {
		$this->assertTrue( jigoshop_countries::country_has_states('US') );
	}

	public function test_country_has_no_states() {
		$this->assertFalse( jigoshop_countries::country_has_states('GB') );
	}

	public function test_is_eu_country() {
		$this->assertTrue( jigoshop_countries::is_eu_country('GB') );
  }

  public function test_is_not_eu_country() {
		$this->assertFalse( jigoshop_countries::is_eu_country('US') );
  }

  public function test_get_base_country() {
  	$this->assertEquals('GB',jigoshop_countries::get_base_country());
  }

  public function test_get_base_state() {
  	$this->assertEquals('*', jigoshop_countries::get_base_state());
  }

  public function test_get_allowed_countries() {
  	// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
  }

}