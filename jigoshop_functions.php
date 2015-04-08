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

/**
 * Format decimal numbers according to current settings.
 *
 * @param  float|string $number Expects either a float or a string with a decimal separator only (no thousands)
 * @param  mixed $dp number of decimal points to use, blank to use configured decimals or false to avoid all rounding.
 * @param  boolean $trim_zeros from end of string
 * @return string
 */
function jigoshop_format_decimal($number, $dp = false, $trim_zeros = false)
{
	$locale = localeconv();
	$options = Jigoshop_Base::get_options();
	$decimals = array(
		$options->get('jigoshop_price_decimal_sep'),
		$locale['decimal_point'],
		$locale['mon_decimal_point']
	);

	// Remove locale from string
	if (!is_float($number)) {
		$number = jigowatt_clean(str_replace($decimals, '.', $number));
	}

	if ($dp !== false) {
		$dp = intval($dp == "" ? $options->get('jigoshop_price_num_decimals') : $dp);
		$number = number_format(floatval($number), $dp, '.', '');

		// DP is false - don't use number format, just return a string in our format
	} elseif (is_float($number)) {
		$number = jigowatt_clean(str_replace($decimals, '.', strval($number)));
	}

	if ($trim_zeros && strstr($number, '.')) {
		$number = rtrim(rtrim($number, '0'), '.');
	}

	return $number;
}
