<?php
/**
 * Various functions Jigoshop gives to the plugin authors.
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
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * Checks if current Jigoshop version is at least a specified one.
 *
 * @param $version string Version string (i.e. 1.10.1)
 * @return bool
 */
function jigoshop_is_minumum_version($version)
{
	if(version_compare(JIGOSHOP_VERSION, $version, '<'))
	{
		return false;
	}

	return true;
}

/**
 * Adds notice for specified source (i.e. plugin name) that current Jigoshop version is not matched.
 *
 * Notice is added only if version is not at it's minimum.
 *
 * @param $source string Source name (used in message).
 * @param $version string Version string (i.e. 1.10.1).
 * @return bool Whether notice was added.
 */
function jigoshop_add_required_version_notice($source, $version)
{
	if(!jigoshop_is_minumum_version($version))
	{
		add_action('admin_notices', function() use ($source, $version) {
			$message = sprintf(__('<strong>%s</strong>: required Jigoshop version: %s. Current version: %s. Please upgrade.', 'jigoshop'), $source, $version, JIGOSHOP_VERSION);
			echo "<div class=\"error\"><p>${message}</p></div>";
		});

		return true;
	}

	return false;
}