<?php
/**
 * Abstract Class that should be extended by most jigoshop classes providing useful methods
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

include_once (dirname(dirname(__FILE__)) . '/jigoshop_options_interface.php');

abstract class Jigoshop_Base {

    private static $jigoshop_options;
    
	/**
	 * Wrapper to WordPress add_action() function
	 * adds the necessary class address on the function passed for WordPress to use
	 *
	 * @param string $tag - the action hook name
	 * @param callback $function_to_add - the function name to add to the action hook
	 * @param int $priority - the priority of the function to add to the action hook
	 * @param int $accepted_args - the number of arguments to pass to the function to add
	 *
	 * @since 0.9.9.2
	 */
	protected function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return add_action( $tag, array( $this, $function_to_add ), $priority, $accepted_args );
	}


	/**
	 * Wrapper to WordPress add_filter() function
	 * adds the necessary class address on the function passed for WordPress to use
	 *
	 * @param string $tag - the filter hook name
	 * @param callback $function_to_add - the function name to add to the filter hook
	 * @param int $priority - the priority of the function to add to the filter hook
	 * @param int $accepted_args - the number of arguments to pass to the filter to add
	 *
	 * @since 0.9.9.2
	 */
	protected function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return add_filter( $tag, array( $this, $function_to_add ), $priority, $accepted_args );
	}
    
    /**
     * Allow jigoshop options to be injected into the class. Any implementation of
     * Jigoshop_Options_Interface can be injected
     * 
     * @param Jigoshop_Options_Interface $jigoshop_options the options to use on the classes
     */
    protected static function set_options(Jigoshop_Options_Interface $jigoshop_options) {
        self::$jigoshop_options = $jigoshop_options;
    }
    
    /**
     * helper function for any files that do not inherit jigoshop_base, they can access jigoshop_options
     * @return Jigoshop_Options_Interface the options that have been set, or null if they haven't been set yet 
     */
    public static function get_options() {
        
        // default options to Jigoshop_Options if they haven't been set
        if (self::$jigoshop_options == null) :
            self::$jigoshop_options = new Jigoshop_Options();
        endif;
        
        return self::$jigoshop_options;
        
    }

}