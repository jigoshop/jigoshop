<?php

require_once 'class.php';

/**
 * Abstract singleton class
 * 
 * @author Gecka
 *
 */
class jigoshop_singleton extends jigoshop_class {

    protected static $instance = array();
    
    protected function __construct () { }

    public static function instance () {
		
    	$class = get_called_class();
		
    	if( isset(self::$instance[$class]) ) return self::$instance[$class];
    		
    	$args = func_get_args();
    		
    	$class = get_called_class();
    	self::$instance[$class] = new $class($args);
    	    	
    	return self::$instance[$class];
    
    }
    
}