<?php

namespace Jigoshop\Entity\Order;

use Jigoshop\Helper\Country;

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
	 * @param string $address Street, house etc. value.
	 */
	public function setAddress($address)
	{
		$this->address = $address;
	}

	/**
	 * @return string Address line.
	 */
	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * @param string $city New city name.
	 */
	public function setCity($city)
	{
		$this->city = $city;
	}

	/**
	 * @return string City name.
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * @param string $country New country code.
	 */
	public function setCountry($country)
	{
		$this->country = $country;
	}

	/**
	 * @return string Country code.
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param string $email New email.
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return Email.
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $firstName New first name.
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
	}

	/**
	 * @return string First name.
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @param string $lastName New last name.
	 */
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
	}

	/**
	 * @return string Last name.
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * @param string $phone New phone number.
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
	}

	/**
	 * @return string Phone number.
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param string $postcode New postcode.
	 */
	public function setPostcode($postcode)
	{
		$this->postcode = $postcode;
	}

	/**
	 * @return string Postcode.
	 */
	public function getPostcode()
	{
		return $this->postcode;
	}

	/**
	 * @param string $state New state name or code.
	 */
	public function setState($state)
	{
		$this->state = $state;
	}

	/**
	 * @return string State name or code.
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

	/**
	 * Returns field value based on it's string name.
	 *
	 * @param $field string Name of the field.
	 * @return bool|string Value or false if not found.
	 */
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
			case 'address':
				$value = $this->getAddress();
				break;
			case 'city':
				$value = $this->getCity();
				break;
			case 'postcode':
				$value = $this->getPostcode();
				break;
			case 'country':
				$value = $this->getCountry();
				break;
			case 'state':
				$value = $this->getState();
				break;
			case 'email':
				$value = $this->getEmail();
				break;
			case 'phone':
				$value = $this->getPhone();
				break;
		}

		return $value;
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
				_x('<strong>%1$s</strong><br/>%2$s, %3$s, %4$s<br/>%5$s, %6$s', 'order-address', 'jigoshop'),
				$this->getName(), $this->address, $this->city, $this->postcode, Country::getName($this->country), Country::getStateName($this->country, $this->state)
			)
		), ', ');

		if (!empty($this->phone)) {
			$result .= sprintf(_x('<br/>Phone: %s', 'order-address', 'jigoshop'), $this->phone);
		}
		if (!empty($this->email)) {
			$result .= sprintf(_x('<br/>Email: <a href="mailto: %1$s">%1$s</a>', 'order-address', 'jigoshop'), $this->email);
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
