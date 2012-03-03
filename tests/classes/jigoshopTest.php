<?php
class jigoshopTest extends WP_UnitTestCase {

    var $plugin_slug = 'jigoshop';

    public function setUp() {
        parent::setUp();
    }

    public function test_plugin_url() {
        $this->assertEquals('http://example.org/wp-content/plugins/jigoshop', jigoshop::plugin_url());

        jigoshop::$plugin_url = NULL;
        $_SERVER['HTTPS'] = TRUE;
        $this->assertEquals('https://example.org/wp-content/plugins/jigoshop', jigoshop::plugin_url());
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
        jigoshop::add_message('Foo Bar');

        // then clear it
        jigoshop::clear_messages();

        // did it work?
        $this->assertEmpty(jigoshop::$errors, 'jigoshop::$errors still has something in it');
        $this->assertEmpty(jigoshop::$messages, 'jigoshop::$messages still has something in it');
    }

    /**
     * Test Show Messages
     *
     * Pre-conditions:
     * Case A: Set error 'Hello World' and ensure it is output
     * Case B: Set message 'Foo Bar' and ensure it is output
     *
     * Post-conditions:
     * Case A: Ouput contains div with class of jigoshop_error
     * Case B: Ouput contains div with class of jigoshop_message
     */
    public function test_show_messages() {
        // set up
        jigoshop::add_error( 'Hello World' );

        ob_start();
        jigoshop::show_messages();
        $this->assertEquals('<div class="jigoshop_error">Hello World</div>', ob_get_clean());

        jigoshop::add_message( 'Foo Bar' );

        ob_start();
        jigoshop::show_messages();
        $this->assertEquals('<div class="jigoshop_message">Foo Bar</div>', ob_get_clean());
    }
}