<?php

namespace Jigoshop\Helper;

use Jigoshop\Entity\Customer;
use Jigoshop\Service\TaxServiceInterface;

class Tax
{
	/** @var TaxServiceInterface */
	private static $service;

	public static function setService(TaxServiceInterface $service)
	{
		self::$service = $service;
	}

	/**
	 * Returns proper tax label if tax service is running.
	 *
	 * @param $taxClass string Tax class.
	 * @param $customer Customer Customer to fetch taxes for.
	 * @return string Tax label.
	 */
	public static function getLabel($taxClass, $customer = null)
	{
		if (self::$service !== null) {
			return self::$service->getLabel($taxClass, $customer);
		}

		return $taxClass;
	}

	/**
	 * Returns proper tax rate if tax service is running.
	 *
	 * @param $taxClass string Tax class.
	 * @param $customer Customer Customer to fetch taxes for.
	 * @return float Tax rate.
	 */
	public static function getRate($taxClass, $customer = null)
	{
		if (self::$service !== null) {
			return self::$service->getRate($taxClass, $customer);
		}

		return 0;
	}
}
