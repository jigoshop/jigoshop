<?php

namespace Jigoshop\Entity\Order;

/**
 * Address of the customer.
 *
 * @package Jigoshop\Entity\Order
 * @author Amadeusz Starzykiewicz
 */
class Address implements \Serializable
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
	 * @return array
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
			case 'last_name':
				$value = $this->getLastName();
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
			'first_name' => $this->firstName,
			'last_name' => $this->lastName,
			'address' => $this->address,
			'city' => $this->city,
			'postcode' => $this->postcode,
			'country' => $this->country,
			'state' => $this->state,
			'email' => $this->email,
			'phone' => $this->phone,
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
		$this->firstName = $data['first_name'];
		$this->lastName = $data['last_name'];
		$this->address = $data['address'];
		$this->city = $data['city'];
		$this->postcode = $data['postcode'];
		$this->country = $data['country'];
		$this->state = $data['state'];
		$this->email = $data['email'];
		$this->phone = $data['phone'];
	}
}
