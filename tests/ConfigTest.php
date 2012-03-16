<?php
/**
 * ConfigTest Tests
 */
class ConfigTest extends WP_UnitTestCase {

    // public function setUp() {
    //     parent::setUp();
    //     $this->my_plugin = $GLOBALS['my_plugin'];
    // }

    // public function testAppendContent() {
    //     $array = array();
    //     $this->assertEquals( "<p>Hello WordPress Unit Tests</p>", $this->my_plugin->append_content(''), '->append_content() appends text' );
    // }

    function test_is_email_only_letters_with_dot_com_domain() {
        $this->assertEquals( 'nb@nikolay.com', is_email( 'nb@nikolay.com' ) );
    }
    
    function test_is_email_should_not_allow_missing_tld() {
        $this->assertFalse( is_email( 'nb@nikolay' ) );
    }
    
    function test_is_email_should_allow_bg_domain() {
        $this->assertEquals( 'nb@nikolay.bg', is_email( 'nb@nikolay.bg' ) );
    }

    // function test_is_email_should_not_allow_blah_domain() {
    //     $this->assertFalse( is_email( 'nb@nikolay.blah' ) );
    // }

    // /**
    //  * A contrived example using some WordPress functionality
    //  */
    // public function testPostTitle() {
    //     // This will simulate running WordPress' main query.
    //     // See wordpress-tests/lib/testcase.php
    //     $this->go_to('http://example.org/?p=1');

    //     // Now that the main query has run, we can do tests that are more functional in nature
    //     global $wp_query;
    //     $post = $wp_query->get_queried_object();
    //     $this->assertEquals('Hello world!', $post->post_title );
    // }
}