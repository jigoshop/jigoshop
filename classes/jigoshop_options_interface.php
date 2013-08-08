<?php
/**
 * Jigoshop_Options_Interface contains all WordPress options used within Jigoshop
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package     Jigoshop
 * @category    Core
 * @author      Jigoshop
 * @copyright   Copyright © 2011-2013 Jigoshop.
 * @license     http://jigoshop.com/license/commercial-edition
 */

interface Jigoshop_Options_Interface {

	/**
	 * Updates the database with the current options
	 *
	 * At various times during a page load, options can be set, or added.
	 * @since	1.3
	 */
    public function update_options();

	/**
	 * Adds a named option
	 *
	 * Will do nothing if option already exists to match WordPress behaviour
	 * Use 'set_option' to actually set an existing option
	 *
	 * @param   string	the name of the option to add
	 * @param   mixed	the value to set if the option doesn't exist
	 *
	 * @since	1.3
	 */
    public function add_option( $name, $value );

	/**
	 * Returns a named Jigoshop option
	 *
	 * @param   string	the name of the option to retrieve
	 * @param   mixed	the value to return if the option doesn't exist
	 * @return  mixed	the value of the option, null if no $default and option doesn't exist
	 *
	 * @since	1.3
	 */
    public function get_option( $name, $default = null );

	/**
	 * Sets a named Jigoshop option
	 *
	 * @param   string	the name of the option to set
	 * @param	mixed	the value to set
	 *
	 * @since	1.3
	 */
    public function set_option( $name, $value );

	/**
	 * Deletes a named Jigoshop option
	 *
	 * @param   string	the name of the option to delete
	 * @return	bool	true for successful completion if option found, false otherwise
	 *
	 * @since	1.3
	 */
    public function delete_option( $name );

	/**
	 * Determines whether an Option exists
	 *
	 * @return	bool	true for successful completion if option found, false otherwise
	 *
	 * @since	1.3
	 */
    public function exists_option( $name );

	/**
	 * Install additional Tab's to Jigoshop Options
	 * Extensions would use this to add a new Tab for their own options
	 *
	 * @param	string	The name of the Tab ('tab'), eg. 'My Extension'
	 * @param	array	The array of options to install onto this tab
	 *
	 * @since	1.3
	 */	
	public function install_external_options_tab( $tab, $options );
	
	/**
	 * Install additional default options for parsing onto a specific Tab
	 * Shipping methods, Payment gateways and Extensions would use this
	 *
	 * @param	string	The name of the Tab ('tab') to install onto
	 * @param	array	The array of options to install
	 *
	 * @since	1.3
	 */	
    public function install_external_options_onto_tab( $tab, $options );

	/**
	 * Install additional default options for parsing after a specific option ID
	 * Extensions would use this
	 *
	 * @param	string	The name of the ID  to install -after-
	 * @param	array	The array of options to install
	 *
	 * @since	1.3
	 */	
    public function install_external_options_after_id( $insert_after_id, $options );
    
	/**
	 * Return the Jigoshop current options
	 *
	 * @param   none
	 * @return  array	the entire current options array is returned
	 *
	 * @since	1.3
	 */
    public function get_current_options();

	/**
	 * Return the Jigoshop default options
	 *
	 * @param   none
	 * @return  array	the entire default options array is returned
	 *
	 * @since	1.3
	 */
    public function get_default_options();

}

?>
