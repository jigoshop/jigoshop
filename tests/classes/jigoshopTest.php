<?php
/**
 * jigoshopTest Tests
 */
class jigoshopTest extends WP_UnitTestCase {

    var $plugin_slug = 'jigoshop';

    public function setUp() {
        parent::setUp();
    }

    public function test_plugin_url() {
        $this->assertEquals('http://example.org/wp-content/plugins/jigoshop', jigoshop::plugin_url());
    }

    public function test_plugin_path() {
        $plugin_root = dirname(dirname(dirname(__FILE__)));
        $this->assertEquals( $plugin_root, jigoshop::plugin_path() );
    }

    public function test_get_var() {
        $vars = array(
            'shop_small_w'     => '150',
            'shop_small_h'     => '150', 
            'shop_tiny_w'      => '36',
            'shop_tiny_h'      => '36',
            'shop_thumbnail_w' => '90',
            'shop_thumbnail_h' => '90',
            'shop_large_w'     => '300',
            'shop_large_h'     => '300',
        );

        foreach ( $vars as $key => $value ) {
            $this->assertEquals( $value, jigoshop::get_var( $key ) );
        }
    }

    public function test_force_ssl()
    {
        // Set up the test
        $unsecure_url = 'http://google.com';
        $_SERVER['HTTPS'] = TRUE;

        // perform the change
        $url = jigoshop::force_ssl($unsecure_url);

        // test the results
        $this->assertEquals( 'https://google.com', $url );

        $_SERVER['HTTPS'] = FALSE;

        // perform the change
        $url = jigoshop::force_ssl($unsecure_url);

        // test the results
        $this->assertEquals( 'http://google.com', $url );
    }

    public function test_add_error() {

        // perform the change
        jigoshop::add_error('Hello World');

        $this->assertEquals( TRUE, in_array('Hello World', jigoshop::$errors));

    }

    public function test_add_message() {

        // perform the change
        jigoshop::add_message('Hello World');

        $this->assertEquals( TRUE, in_array('Hello World', jigoshop::$messages));

    }

    public function test_clear_messages() {
        jigoshop::add_error('Hello World');

        // then clear it
        jigoshop::clear_messages();

        // did it work?
        $this->assertEmpty(jigoshop::$errors, 'jigoshop::$errors still has something in it');
        $this->assertEmpty(jigoshop::$messages, 'jigoshop::$messages still has something in it');
    }

    public function test_show_messages() {
        // set up
        jigoshop::add_error( 'Hello World' );

        $this->expectOutputString('<div class="jigoshop_error">Hello World</div>');
        jigoshop::show_messages();

        error_log( print_r(jigoshop::show_messages(), true) );
        exit;

        jigoshop::add_message( 'Foo Bar' );

        $this->expectOutputString('<div class="jigoshop_error">Foo Bar</div>');
        jigoshop::show_messages();
    }
}