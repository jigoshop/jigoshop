<?php
// Load WordPress test environment
// https://github.com/nb/wordpress-tests
// The path to wordpress-tests
$testFile = "test.txt";
$fh = fopen($testFile, 'w') or die("can't open file");
$stringData = "1";
fwrite($fh, $stringData);
fclose($fh);
$path = './vendor/wordpress-tests/bootstrap.php';
if( file_exists( $path ) ) {
    require_once $path;
} else {
    exit( "Couldn't find wordpress-tests please run\n git submodule init && git submodule update\n" );
}
//load all jigoshop classes
require_once './jigoshop.php';