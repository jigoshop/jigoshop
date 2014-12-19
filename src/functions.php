<?php

namespace Jigoshop;

/**
 * Checks if current Jigoshop version is at least a specified one.
 *
 * @param $version string Version string (i.e. 1.10.1)
 * @return bool
 */
function isMinimumVersion($version)
{
	if(version_compare(Core::VERSION, $version, '<'))
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
function addRequiredVersionNotice($source, $version)
{
	if(!isMinimumVersion($version))
	{
		add_action('admin_notices', function() use ($source, $version) {
			$message = sprintf(__('<strong>%s</strong>: required Jigoshop version: %s. Current version: %s. Please upgrade.', 'jigoshop'), $source, $version, Core::VERSION);
			echo '<div class="error"><p>'.$message.'</p></div>';
		});

		return true;
	}

	return false;
}
