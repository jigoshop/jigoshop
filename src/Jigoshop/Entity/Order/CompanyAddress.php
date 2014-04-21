<?php

namespace Jigoshop\Entity\Order;

/**
 * Address of company customer.
 *
 * @package Jigoshop\Entity\Order
 * @author Amadeusz Starzykiewicz
 */
class CompanyAddress extends Address
{
	private $company;
	private $vatNumber;

	/**
	 * @param mixed $company
	 */
	public function setCompany($company)
	{
		$this->company = $company;
	}

	/**
	 * @return mixed
	 */
	public function getCompany()
	{
		return $this->company;
	}

	/**
	 * @param mixed $vatNumber
	 */
	public function setVatNumber($vatNumber)
	{
		$this->vatNumber = $vatNumber;
	}

	/**
	 * @return mixed
	 */
	public function getVatNumber()
	{
		return $this->vatNumber;
	}
}