<?php

namespace Jigoshop\Helper;

use Monolog\Registry;

class Validation
{
	/**
	 * Postcode regex for validation
	 * TODO: need to verify these country codes match ours, it doesn't appear all do
	 */
	public static $postcodes = array(
		'AD' => 'AD\d{3}',
		'AM' => '(37)?\d{4}',
		'AR' => '^([A-HJ-TP-Z]{1}\d{4}[A-Z]{3}|[a-z]{1}\d{4}[a-hj-tp-z]{3})$',
		'AS' => '96799',
		'AT' => '\d{4}',
		'AU' => '^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$',
		'AX' => '22\d{3}',
		'AZ' => '\d{4}',
		'BA' => '\d{5}',
		'BB' => '(BB\d{5})?',
		'BD' => '\d{4}',
		'BE' => '^[1-9]{1}[0-9]{3}$',
		'BG' => '\d{4}',
		'BH' => '((1[0-2]|[2-9])\d{2})?',
		'BM' => '[A-Z]{2}[ ]?[A-Z0-9]{2}',
		'BN' => '[A-Z]{2}[ ]?\d{4}',
		'BR' => '\d{5}[\-]?\d{3}',
		'BY' => '\d{6}',
		'CA' => '^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])\ {0,1}(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$',
		'CC' => '6799',
		'CH' => '^[1-9][0-9][0-9][0-9]$',
		'CK' => '\d{4}',
		'CL' => '\d{7}',
		'CN' => '\d{6}',
		'CR' => '\d{4,5}|\d{3}-\d{4}',
		'CS' => '\d{5}',
		'CV' => '\d{4}',
		'CX' => '6798',
		'CY' => '\d{4}',
		'CZ' => '\d{3}[ ]?\d{2}',
		'DE' => '\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b',
		'DK' => '^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$',
		'DO' => '\d{5}',
		'DZ' => '\d{5}',
		'EC' => '([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?',
		'EE' => '\d{5}',
		'EG' => '\d{5}',
		'ES' => '^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$',
		'ET' => '\d{4}',
		'FI' => '\d{5}',
		'FK' => 'FIQQ 1ZZ',
		'FM' => '(9694[1-4])([ \-]\d{4})?',
		'FO' => '\d{3}',
		'FR' => '^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$',
		'GE' => '\d{4}',
		'GF' => '9[78]3\d{2}',
		'GL' => '39\d{2}',
		'GN' => '\d{3}',
		'GP' => '9[78][01]\d{2}',
		'GR' => '\d{3}[ ]?\d{2}',
		'GS' => 'SIQQ 1ZZ',
		'GT' => '\d{5}',
		'GU' => '969[123]\d([ \-]\d{4})?',
		'GW' => '\d{4}',
		'HM' => '\d{4}',
		'HN' => '(?:\d{5})?',
		'HR' => '\d{5}',
		'HT' => '\d{4}',
		'HU' => '\d{4}',
		'ID' => '\d{5}',
		'IE' => '((D|DUBLIN)?([1-9]|6[wW]|1[0-8]|2[024]))?',
		'IL' => '\d{5}',
		'IN' => '^[1-9][0-9][0-9][0-9][0-9][0-9]$',
		'IO' => 'BBND 1ZZ',
		'IQ' => '\d{5}',
		'IS' => '\d{3}',
		'IT' => '^(V-|I-)?[0-9]{5}$',
		'JO' => '\d{5}',
		'JP' => '\d{3}-\d{4}',
		'KE' => '\d{5}',
		'KG' => '\d{6}',
		'KH' => '\d{5}',
		'KR' => '\d{3}[\-]\d{3}',
		'KW' => '\d{5}',
		'KZ' => '\d{6}',
		'LA' => '\d{5}',
		'LB' => '(\d{4}([ ]?\d{4})?)?',
		'LI' => '(948[5-9])|(949[0-7])',
		'LK' => '\d{5}',
		'LR' => '\d{4}',
		'LS' => '\d{3}',
		'LT' => '\d{5}',
		'LU' => '\d{4}',
		'LV' => '\d{4}',
		'MA' => '\d{5}',
		'MC' => '980\d{2}',
		'MD' => '\d{4}',
		'ME' => '8\d{4}',
		'MG' => '\d{3}',
		'MH' => '969[67]\d([ \-]\d{4})?',
		'MK' => '\d{4}',
		'MN' => '\d{6}',
		'MP' => '9695[012]([ \-]\d{4})?',
		'MQ' => '9[78]2\d{2}',
		'MT' => '[A-Z]{3}[ ]?\d{2,4}',
		'MU' => '(\d{3}[A-Z]{2}\d{3})?',
		'MV' => '\d{5}',
		'MX' => '\d{5}',
		'MY' => '\d{5}',
		'NC' => '988\d{2}',
		'NE' => '\d{4}',
		'NF' => '2899',
		'NG' => '(\d{6})?',
		'NI' => '((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?',
		'NL' => '^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$',
		'NO' => '\d{4}',
		'NP' => '\d{5}',
		'NZ' => '\d{4}',
		'OM' => '(PC )?\d{3}',
		'PF' => '987\d{2}',
		'PG' => '\d{3}',
		'PH' => '\d{4}',
		'PK' => '\d{5}',
		'PL' => '\d{2}-\d{3}',
		'PM' => '9[78]5\d{2}',
		'PN' => 'PCRN 1ZZ',
		'PR' => '00[679]\d{2}([ \-]\d{4})?',
		'PT' => '\d{4}([\-]\d{3})?',
		'PW' => '96940',
		'PY' => '\d{4}',
		'RE' => '9[78]4\d{2}',
		'RO' => '\d{6}',
		'RS' => '\d{6}',
		'RU' => '\d{6}',
		'SA' => '\d{5}',
		'SE' => '^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$',
		'SG' => '\d{6}',
		'SH' => '(ASCN|STHL) 1ZZ',
		'SI' => '\d{4}',
		'SJ' => '\d{4}',
		'SK' => '\d{3}[ ]?\d{2}',
		'SM' => '4789\d',
		'SN' => '\d{5}',
		'SO' => '\d{5}',
		'SZ' => '[HLMS]\d{3}',
		'TC' => 'TKCA 1ZZ',
		'TH' => '\d{5}',
		'TJ' => '\d{6}',
		'TM' => '\d{6}',
		'TN' => '\d{4}',
		'TR' => '\d{5}',
		'TW' => '\d{3}(\d{2})?',
		'UA' => '\d{5}',
		'UK' => '^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$',
		'US' => '^\d{5}([\-]?\d{4})?$',
		'UY' => '\d{5}',
		'UZ' => '\d{6}',
		'VA' => '00120',
		'VE' => '\d{4}',
		'VI' => '008(([0-4]\d)|(5[01]))([ \-]\d{4})?',
		'WF' => '986\d{2}',
		'YT' => '976\d{2}',
		'YU' => '\d{5}',
		'ZA' => '\d{4}',
		'ZM' => '\d{5}',
	);

	/**
	 * Validates a numeric integer value, positive or negative
	 * Must contain valid numeric characters [0-9]
	 *
	 * @param string $value value to determine if numeric integer value
	 * @return boolean
	 */
	public static function isInteger($value)
	{
		if ($value[0] == '-' || $value[0] == '+') {
			return ctype_digit(substr($value, 1));
		}

		return ctype_digit($value);
	}

	/**
	 * Validates a numeric natural number, positive only
	 * Must contain valid numeric characters [0-9]
	 *
	 * @param string $value value to determine if natural number
	 * @return boolean
	 */
	public static function isNatural($value)
	{
		return ctype_digit($value);
	}

	/**
	 * Validates a decimal value, positive or negative
	 * Must contain valid numeric characters [0-9]
	 * May contain optional decimal point followed by numeric characters [0-9]
	 *
	 * @param string $value value to determine if decimal value
	 * @return boolean
	 */
	public static function isDecimal($value)
	{
		return is_numeric($value);
	}

	/**
	 * Validates an email using wordpress native isEmail function
	 *
	 * @param string $email email address
	 * @return boolean
	 */
	public static function isEmail($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	/**
	 * Validates a phone number using a regular expression
	 *
	 * @param string $phone phone number
	 * @return boolean
	 */
	public static function isPhone($phone)
	{
		if (strlen(trim(preg_replace('/[\s\#0-9_\-\+\(\)]/', '', $phone))) > 0) {
			return false;
		}

		return true;
	}

	/**
	 * Checks for a valid postcode for a country
	 *
	 * @param string $postcode postcode
	 * @param string $country country
	 * @return boolean
	 */
	public static function isPostcode($postcode, $country)
	{
		if (strlen(trim(preg_replace('/[\s\-A-Za-z0-9]/', '', $postcode))) > 0) {
			return false;
		}

		$country = strtoupper(trim($country));
		$postcode = strtoupper(trim($postcode));

		// Assume that unknown countries has proper postcodes
		if (!isset(self::$postcodes[$country]) && $country !== 'GB') {
			return true;
		}

		Registry::getInstance('jigoshop')->addDebug(sprintf('Validating postcode "%s" for country "%s"', $postcode, $country));

		switch ($country) {
			case 'GB':
				return self::isGreatBritainPostcode($postcode);
			default:
				$regex = '/^'.self::$postcodes[$country].'$/';
				Registry::getInstance('jigoshop')->addDebug(sprintf('Validation regex: %s', $regex));
				$match = preg_match($regex, $postcode);
				if ($match !== 1) {
					return false;
				}
		}

		return true;
	}

	private static function isGreatBritainPostcode($toCheck)
	{
		// Permitted letters depend upon their position in the postcode.
		$alpha1 = "[abcdefghijklmnoprstuwyz]"; // Character 1
		$alpha2 = "[abcdefghklmnopqrstuvwxy]"; // Character 2
		$alpha3 = "[abcdefghjkstuw]"; // Character 3
		$alpha4 = "[abehmnprvwxy]"; // Character 4
		$alpha5 = "[abdefghjlnpqrstuwxyz]"; // Character 5

		// Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA
		$pcexp[0] = '^('.$alpha1.'{1}'.$alpha2.'{0,1}[0-9]{1,2})([0-9]{1}'.$alpha5.'{2})$';

		// Expression for postcodes: ANA NAA
		$pcexp[1] = '^('.$alpha1.'{1}[0-9]{1}'.$alpha3.'{1})([0-9]{1}'.$alpha5.'{2})$';

		// Expression for postcodes: AANA NAA
		$pcexp[2] = '^('.$alpha1.'{1}'.$alpha2.'[0-9]{1}'.$alpha4.')([0-9]{1}'.$alpha5.'{2})$';

		// Exception for the special postcode GIR 0AA
		$pcexp[3] = '^(gir)(0aa)$';

		// Standard BFPO numbers
		$pcexp[4] = '^(bfpo)([0-9]{1,4})$';

		// c/o BFPO numbers
		$pcexp[5] = '^(bfpo)(c\/o[0-9]{1,3})$';

		// Load up the string to check, converting into lowercase and removing spaces
		$postcode = strtolower($toCheck);
		$postcode = str_replace(' ', '', $postcode);

		// Check the string against the six types of postcodes
		foreach ($pcexp as $regexp) {
			if (preg_match('@'.$regexp.'@', $postcode, $matches)) {
				return true;
			}
		}

		return false;
	}
}
