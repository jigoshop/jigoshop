<?php

/**
 * Abstract Class extended by all jigoshop classes providing usefull methods
 * 
 * @author Gecka
 */
abstract class jigoshop_class {
	
	/**
	 * Wrapper to wordpress add_action() function
	 * @see add_action()
	 */
	protected function add_action ($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
		return add_action($tag, array($this, $function_to_add), $priority = 10, $accepted_args = 1);
	}
	
	/**
	 * Wrapper to wordpress add_filter() function
	 * @see add_filter()
	 */
	protected function add_filter ($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
		return add_filter($tag, array($this, $function_to_add), $priority = 10, $accepted_args = 1);
	}
	
}