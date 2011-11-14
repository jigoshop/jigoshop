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
 * @package    Jigoshop
 * @category   Core
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

abstract class jigoshop_base_class {
	
	/**
	 * Wrapper to WordPress add_action() function
	 * adds the necessary class address on the function passed for WordPress to use
	 *
	 * @param string $tag - the action hook name
	 * @params callback $function_to_add - the function name to add to the action hook
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
	 * @params callback $function_to_add - the function name to add to the filter hook
	 * @param int $priority - the priority of the function to add to the filter hook
	 * @param int $accepted_args - the number of arguments to pass to the filter to add
	 *
	 * @since 0.9.9.2
	 */
	protected function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return add_filter( $tag, array( $this, $function_to_add ), $priority, $accepted_args );
	}
	
}