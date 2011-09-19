<?php
/**
 * Jigoshop Payment Gateway class
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Checkout
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
class jigoshop_payment_gateway {
	
	var $id;
	var $title;
	var $chosen;
	var $has_fields;
	var $countries;
	var $availability;
	var $enabled;
	var $icon;
	var $description;
	
	function is_available() {
		
		if ($this->enabled=="yes") :
			
			return true;
			
		endif;	
		
		return false;
	}
	
	function set_current() {
		$this->chosen = true;
	}
	
	function icon() {
		if ($this->icon) :
			return '<img src="'. jigoshop::force_ssl($this->icon).'" alt="'.$this->title.'" />';
		endif;
	}
	
	function admin_options() {}
	
	function process_payment() {}
	
	function validate_fields() { return true; }
}