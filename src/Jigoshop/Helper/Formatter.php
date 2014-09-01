<?php

namespace Jigoshop\Helper;

/**
 * Helper for formatting different values.
 *
 * @package Jigoshop\Helper
 * @author Amadeusz Starzykiewicz
 */
class Formatter
{
	public static function letterToNumber($value)
	{
		$letter = strtoupper(substr($value, -1));
		$result = substr($value, 0, -1);

		switch ($letter) {
			case 'P':
				$result *= 1024;
			case 'T':
				$result *= 1024;
			case 'G':
				$result *= 1024;
			case 'M':
				$result *= 1024;
			case 'K':
				$result *= 1024;
		}

		return $result;
	}
}