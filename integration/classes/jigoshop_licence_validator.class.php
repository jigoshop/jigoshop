<?php

/**
 * Jigoshop Licence Validation Class used by downloadable digital products
 * Used for validation actions of product licencing from a selling shop
 * Used for WordPress auto update notices of updates available from a selling shop
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Extensions
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 * @version 1.3 - 2014-06-22
 */
class jigoshop_licence_validator extends \Jigoshop\Licence
{
	/**
	 * Constructor for Licence Validator in each plugin
	 *
	 * @param string $file - full server path to the main plugin file
	 * @param string $identifier - selling Shop Product ID
	 * @param string $home_shop_url - selling Shop URL of this plugin (product)
	 */
	public function __construct($file, $identifier, $home_shop_url)
	{
		parent::__construct($file, $identifier, $home_shop_url);
	}

	/**
	 * Is Licence Active for this plugin
	 * All plugins should call this early in their existance to check if their licence is valid
	 * Allow plugin to function normally if it is and limit or disable functionality otherwise
	 *
	 * @return boolean
	 */
	public function is_licence_active()
	{
		return parent::isActive();
	}

	/**
	 * Displaying the error message in admin panel when plugin is activated without a valid licence key
	 */
	public function display_inactive_plugin_warning()
	{
		parent::displayWarnings();
	}

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param object $transient
	 * @return object $transient
	 */
	public function check_for_update($transient)
	{
		return parent::checkUpdates($transient);
	}

	/**
	 * Get our self-hosted update description from the 'plugins_api' filter
	 *
	 * @param boolean $false
	 * @param array $action
	 * @param object $arg
	 * @return bool|object
	 */
	public function get_update_info($false, $action, $arg)
	{
		return parent::getUpdateData($false, $action, $arg);
	}

	function in_plugin_update_message($plugin_data, $r)
	{
		parent::updateMessage($plugin_data, $r);
	}
}
