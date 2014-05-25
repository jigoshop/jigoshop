<?php
define('JIGOSHOP_DIR', dirname(__FILE__).'/..');

require_once(JIGOSHOP_DIR.'/vendor/autoload.php');
$loader = new \Symfony\Component\ClassLoader\ClassLoader();
$loader->addPrefix('WPAL', JIGOSHOP_DIR.'/vendor/megawebmaster/wpal');
$loader->addPrefix('Jigoshop', JIGOSHOP_DIR.'/src');
$loader->register();