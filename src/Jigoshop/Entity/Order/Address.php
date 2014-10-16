<?php

namespace Jigoshop\Entity\Order;

/**
 * Address of the customer.
 *
 * @package Jigoshop\Entity\Order
 * @author Amadeusz Starzykiewicz
 */
class Address
{
	private $firstName;
	private $lastName;
	private $address;
	private $city;
	private $postcode;
	private $country;
	private $state;
	private $email;
	private $phone;

	/**
	 * @param mixed $address
	 */
	public function setAddress($address)
	{
		$this->address = $address;
	}

	/**
	 * @return mixed
	 */
	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * @param mixed $city
	 */
	public function setCity($city)
	{
		$this->city = $city;
	}

	/**
	 * @return mixed
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * @param mixed $country
	 */
	public function setCountry($country)
	{
		$this->country = $country;
	}

	/**
	 * @return mixed
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param mixed $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param mixed $firstName
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
	}

	/**
	 * @return mixed
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @param mixed $lastName
	 */
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
	}

	/**
	 * @return mixed
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * @param mixed $phone
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
	}

	/**
	 * @return mixed
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param mixed $postcode
	 */
	public function setPostcode($postcode)
	{
		$this->postcode = $postcode;
	}

	/**
	 * @return mixed
	 */
	public function getPostcode()
	{
		return $this->postcode;
	}

	/**
	 * @param mixed $state
	 */
	public function setState($state)
	{
		$this->state = $state;
	}

	/**
	 * @return mixed
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return string Full name for the address.
	 */
	public function getName()
	{
		return $this->firstName.' '.$this->lastName;
	}

	/**
	 * @return string Formatted address for Google Maps.
	 */
	public function getGoogleAddress()
	{
		return trim($this->address.', '.$this->city.', '.$this->postcode.', '.$this->country.', '.$this->state, ', ');
	}

	public function get($field)
	{
		$value = false;

		switch ($field) {
			case 'first_name':
				$value = $this->getFirstName();
				break;
		}

		return $value;
	}

	public function __toString()
	{
		return trim(str_replace(
			array(', ,', ', <'),
			array('', '<'),
			sprintf(
				_x('%1$s, %2$s, %3$s<br/>%4$s, %5$s', 'order-address', 'jigoshop'),
				$this->address, $this->city, $this->postcode, $this->country, $this->state
			)
		), ', ');
	}
}
