<?php
/**
 * jigoshopTest Tests
 */
class jigoshopTest extends WP_UnitTestCase {

    var $plugin_slug = 'jigoshop';

    public function setUp() {
        parent::setUp();
    }

    public function testPlugin_url() {
        $this->assertEquals('http://example.org/wp-content/plugins/jigoshop', jigoshop::plugin_url());
    }

    public function testPlugin_path() {
        $plugin_root = dirname(dirname(dirname(__FILE__)));
        $this->assertEquals( $plugin_root, jigoshop::plugin_path() );
    }

    public function testGet_var() {
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

    /**
     * Ssl on
     *
     * @return void
     */
    public function testForce_ssl()
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

    public function testAdd_error() {

        // perform the change
        jigoshop::add_error('Hello World');

        $this->assertEquals( TRUE, in_array('Hello World', jigoshop::$errors));

    }

    public function testClear_messages() {
        jigoshop::add_error('Hello World');

        // then clear it
        jigoshop::clear_messages();

        // did it work?
        $this->assertEmpty(jigoshop::$errors, 'jigoshop::$errors still has something in it');
        $this->assertEmpty(jigoshop::$messages, 'jigoshop::$messages still has something in it');
    }
}