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
    
	public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
    }
    
}