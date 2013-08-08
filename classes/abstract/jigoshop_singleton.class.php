<?php
/**
 * Abstract Singleton Class: Ensure that there can be only one instance of Class. Provide a global access point to that instance.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

abstract class Jigoshop_Singleton extends Jigoshop_Base {

    private static $instance = array();
    public static $testing = false;

    protected function __construct() { }


    public static function instance() {

    	$class = get_called_class();

    	if ( isset( self::$instance[$class] ) ) return self::$instance[$class];

    	$args = func_get_args();

    	self::$instance[$class] = new $class( $args );

    	return self::$instance[$class];

    }
	public static function reset() {
		self::$instance = null;	
	}

	public function __clone() {
        trigger_error( "Cloning Singleton's is not allowed.", E_USER_ERROR );
    }


    public function __wakeup() {
        trigger_error( "Unserializing Singleton's is not allowed.", E_USER_ERROR );
    }

}


/**
 * this is required for servers that do not have PHP 5.3 or better
 * solution from: http://www.php.net/manual/de/function.get-called-class.php#93799
 */
if ( ! function_exists( 'get_called_class' )) {
	class jigoshop_class_tools {
		static function get_called_class( $bt = false, $l = 2 ) {
			if ( !$bt ) $bt = debug_backtrace();
			if ( !isset( $bt[$l] )) throw new Exception( "Cannot find called class -> stack level too deep." );
			if ( !isset( $bt[$l]['type'] )) {
				throw new Exception ( 'Cannot find called class -> backtrace type not set' );
			}
			else switch ( $bt[$l]['type'] ) {
				case '::':
					$lines = file( $bt[$l]['file'] );
					$i = 0;
					$callerLine = '';
					do {
						$i++;
						$callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
					} while ( stripos( $callerLine, $bt[$l]['function'] ) === false );
					preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
								$callerLine,
								$matches);
					if ( !isset($matches[1]  )) {
						// must be an edge case.
						throw new Exception ( "Could not find caller class -> originating method call is obscured." );
					}
					switch ( $matches[1] ) {
						case 'self':
						case 'parent':
							return self::get_called_class( $bt, $l+1 );
						default:
							return $matches[1];
					}
					// won't get here.
				case '->': switch ( $bt[$l]['function'] ) {
						case '__get':
							// edge case -> get class of calling object
							if ( !is_object( $bt[$l]['object'] )) throw new Exception ( "Could not find called class -> edge case fail. __get called on non object." );
							return get_class( $bt[$l]['object'] );
						default: return $bt[$l]['class'];
					}

				default: throw new Exception ( "Could not find called class -> unknown backtrace method type" );
			}
		}
	}

	function get_called_class() {
		return jigoshop_class_tools::get_called_class();
	}
}