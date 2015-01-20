<?php

/**
 * Validation Class
 * DISCLAIMER
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
class jigoshop_validation
{
	public static function is_integer($value)
	{
		return \Jigoshop\Helper\Validation::isInteger($value);
	}

	public static function is_natural($value)
	{
		return \Jigoshop\Helper\Validation::isNatural($value);
	}

	public static function is_decimal($value)
	{
		return \Jigoshop\Helper\Validation::isDecimal($value);
	}

	public static function is_email($email)
	{
		return \Jigoshop\Helper\Validation::isEmail($email);
	}

	public static function is_phone($phone)
	{
		return \Jigoshop\Helper\Validation::isPhone($phone);
	}

	public static function is_postcode($postcode, $country)
	{
		return \Jigoshop\Helper\Validation::isPostcode($postcode, $country);
	}

	/**
	 * Format the postcode according to the country and length of the postcode
	 *
	 * @param   string  postcode
	 * @param  string  country
	 * @return  string  formatted postcode
	 */
	public static function format_postcode($postcode, $country)
	{
		$postcode = strtoupper(trim($postcode));
		$postcode = trim(preg_replace('/[\s]/', '', $postcode));

		if ($country == 'GB') {
			if (strlen($postcode) == 7) {
				$postcode = substr_replace($postcode, ' ', 4, 0);
			} else {
				$postcode = substr_replace($postcode, ' ', 3, 0);
			}
		}

		return $postcode;
	}
}
