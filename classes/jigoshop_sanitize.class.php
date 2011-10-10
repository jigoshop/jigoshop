<?php
/**
 * Contains methods for sanitizing arrays of data
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
class Sanitize {

	private $_fields = array();

	public function __construct( $uncleanArray )
	{
		foreach ( $uncleanArray as $key => $value ) {
			$this->_fields[$this->_sanitize( $key )] = $this->_sanitize( $value );
		}
	}

	private function _sanitize( $input )
	{
		if ( is_array( $input )) {
			foreach ( $input as $key => $value ) {
				$input[$key] = $this->_sanitize( $value );
			}
			return $input;
		}
		return stripslashes( htmlentities( strip_tags( trim( $input ))));
	}

	public function __isset( $key )
	{
		return isset( $this->_fields[$key] );
	}
	
	public function __unset( $key )
	{
		if ( array_key_exists( $key, $this->_fields )) {
			unset( $this->_fields[$key] );
		}
	}
	
	public function __get( $key )
	{
		if ( array_key_exists( $key, $this->_fields ) && ! empty( $this->_fields[$key] )) {
			return $this->_fields[$key];
		} else {
			return null;
		}
	}
	
	public function __set( $key, $value )
	{
		$this->_fields[$this->_sanitize( $key )] = $this->_sanitize( $value );
	}
	
}

?>