<?php

namespace Jigoshop\Entity\Order;

use Jigoshop\Helper\Country;

/**
 * Address of company customer.
 *
 * @package Jigoshop\Entity\Order
 * @author Amadeusz Starzykiewicz
 */
class CompanyAddress extends Address
{
	/** @var string */
	private $company;
	/** @var string */
	private $vatNumber;

	/**
	 * @param string $company New company name.
	 */
	public function setCompany($company)
	{
		$this->company = $company;
	}

	/**
	 * @return string Company name.
	 */
	public function getCompany()
	{
		return $this->company;
	}

	/**
	 * @param string $vatNumber New VAT number.
	 */
	public function setVatNumber($vatNumber)
	{
		$this->vatNumber = $vatNumber;
	}

	/**
	 * @return string VAT number.
	 */
	public function getVatNumber()
	{
		return $this->vatNumber;
	}

	public function get($field)
	{
		switch ($field) {
			case 'company':
				return $this->getCompany();
			case 'euvatno':
				return $this->getVatNumber();
		}

		return parent::get($field);
	}

	/**
	 * @return string String representation of the whole address.
	 */
	public function __toString()
	{
		$result = trim(str_replace(
			array(', ,', ', <'),
			array('', '<'),
			sprintf(
				_x('<strong>%1$s</strong>, %2$s<br/>%3$s, %4$s, %5$s<br/>%6$s, %7$s', 'order-address', 'jigoshop'),
				$this->getName(), $this->company, $this->getAddress(), $this->getCity(), $this->getPostcode(),  Country::getName($this->getCountry()),
				Country::getStateName($this->getCountry(), $this->getState())
			)
		), ', ');

		$phone = $this->getPhone();
		if (!empty($phone)) {
			$result .= sprintf(_x('<br/>Phone: %s', 'order-address', 'jigoshop'), $phone);
		}
		$email = $this->getEmail();
		if (!empty($email)) {
			$result .= sprintf(_x('<br/>Email: <a href="mailto: %1$s">%1$s</a>', 'order-address', 'jigoshop'), $email);
		}

		return $result;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return serialize(array(
			'company' => $this->company,
			'euvatno' => $this->vatNumber,
			'parent' => parent::serialize(),
		));
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 * @return void
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->company = $data['company'];
		$this->vatNumber = $data['euvatno'];
		parent::unserialize($data['parent']);
	}
}
