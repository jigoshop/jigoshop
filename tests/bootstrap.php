<?php
// Load WordPress test environment
// https://github.com/nb/wordpress-tests
// The path to wordpress-tests

$path = './vendor/wordpress-tests/bootstrap.php';
if( file_exists( $path ) ) {
    require_once $path;
} else {
    exit( "Couldn't find wordpress-tests please run git submodule init \n" );
}
//load jiigoshop all
require_once './jigoshop.php';;