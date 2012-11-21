<?php
// Load the plugin
require_once('classes/abstract/jigoshop_base.class.php');
require_once('classes/abstract/jigoshop_singleton.class.php');
require_once('classes/jigoshop_session.class.php');


class WP_Test_Jigoshop_Session extends WP_UnitTestCase
{

	public function tearDown() {
		// Reset the session
		unset($_SESSION);
		parent::tearDown();
	}

	public function test_session() {

		// Check that session is null
		$this->assertNull(jigoshop_session::instance()->var);

		jigoshop_session::instance()->var = 'hello world';

		// Check that hthe session is set & is correct
		$this->assertEquals('hello world', jigoshop_session::instance()->var);
	}

	public function test_isset_session() {

		// Set the session
		jigoshop_session::instance()->var = 'hello world';

		// Test that the item is set
		$this->assertTrue(isset(jigoshop_session::instance()->var));
	}

	public function test_unset_session() {

		// Set the session
		jigoshop_session::instance()->var = 'hello world';

		// Unset it
		unset(jigoshop_session::instance()->var);

		$this->assertNull(jigoshop_session::instance()->var);
	}
}