<?php
/**
 * Contains methods for sanitizing arrays and objects of data
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

class jigoshop_sanitize {

	private $_fields = array();


	public function __construct( $uncleanArray )
	{
		foreach ( $uncleanArray as $key => $value ) {
			$this->_fields[$this->_sanitize( $key )] = $this->_sanitize( $value );
		}
	}


	/**
	 * Sanitize or clean an input field.
	 * Removes leading and trailing white space, all html and php tags, encodes any html entities
	 *     using the default (ENT_COMPAT | ENT_HTML401), and unquotes any quoted strings.
	 *
	 * @param object $input - The object to sanitize.
	 * @return object - The sanitized object.
	 *
	 * @since 0.9.9.2
	 */
	private function _sanitize( $input )
	{
		$input = $this->_fixIncompleteObject( $input );

		if ( is_array( $input ) || is_object($input) ) {
			$output = array();
			foreach ( $input as $key => $value ) {
				$output[$key] = $this->_sanitize( $value );
			}
			return $output;
		}
//		return stripslashes( trim( $input ));
		return stripslashes( strip_tags( trim( $input )));
	}


	/**
	 * _fixIncompleteObject repairs an object if it is incomplete.
	 *
	 * Removes the __PHP_Incomplete_Class crap from the object,
	 * so that is_object() will correctly identify $input as an object
	 *
	 * @param object $object - The "broken" object
	 * @return object - The "fixed" object
	 */
	private function _fixIncompleteObject( $input )
	{
		if ( ! is_object( $input ) && gettype( $input ) == 'object' ) {
			return unserialize( serialize( $input ));
		}
		return $input;
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