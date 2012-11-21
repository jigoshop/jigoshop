<?php
require_once('classes/jigoshop_validation.class.php');

class WP_Test_Jigoshop_Validation extends WP_UnitTestCase
{
	public function test_is_natural()
	{
		$this->assertTrue(jigoshop_validation::is_natural('1'));
	}

	public function test_isnt_natural()
	{
		$this->assertFalse(jigoshop_validation::is_natural('-1'));
	}

	/**
   * @dataProvider provider_phone
   */
	public function test_is_phone($number)
	{
		$this->assertTrue(jigoshop_validation::is_phone($number));
	}

	/**
   * @dataProvider provider_postcode
   */
	public function test_is_postcode($postcode, $country)
	{
		$this->assertTrue(jigoshop_validation::is_postcode($postcode, $country));
	}

	/**
   * @dataProvider provider_postcode
   */
	public function test_format_postcode($postcode, $country, $expected)
	{
		$this->assertEquals($expected, jigoshop_validation::format_postcode($postcode, $country));
	}

	// ==================================================================
	//
	// Data Providers
	//
	// ------------------------------------------------------------------

	public function provider_phone() {
		return array(
			array('01233 456 789'),
			array('(912) 555-1234'),
		);
	}

	public function provider_postcode()
	{
		return array(
			array('SW1A0AA', 'GB', 'SW1A 0AA'),
			array('SW10AA', 'GB', 'SW1 0AA'),
			array('10003', 'US', '10003'),
		);
	}
}