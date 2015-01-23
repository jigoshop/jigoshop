<?php

namespace Jigoshop\Entity\Customer;

use Jigoshop\Helper\Country;

/**
 * Address of the customer.
 *
 * @package Jigoshop\Entity\Customer
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
	 * @return string First name.
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @param string $firstName New first name.
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
	}

	/**
	 * @return string Last name.
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * @param string $lastName New last name.
	 */
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
	}

	/**
	 * @return string Address line.
	 */
	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * @param string $address Street, house etc. value.
	 */
	public function setAddress($address)
	{
		$this->address = $address;
	}

	/**
	 * @return string City name.
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * @param string $city New city name.
	 */
	public function setCity($city)
	{
		$this->city = $city;
	}

	/**
	 * @return string Postcode.
	 */
	public function getPostcode()
	{
		return $this->postcode;
	}

	/**
	 * @param string $postcode New postcode.
	 */
	public function setPostcode($postcode)
	{
		$this->postcode = $postcode;
	}

	/**
	 * @return string Country code.
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param string $country New country code.
	 */
	public function setCountry($country)
	{
		if ($country != $this->country) {
			$this->country = $country;
			$this->setState('');
		}
	}

	/**
	 * @return string State name or code.
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @param string $state New state name or code.
	 */
	public function setState($state)
	{
		$this->state = $state;
	}

	/**
	 * @return Email.
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $email New email.
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return string Phone number.
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param string $phone New phone number.
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
	}

	/**
	 * Checks whether the address is valid (has all required fields filled).
	 *
	 * @return bool Is address valid?
	 */
	public function isValid()
	{
		return $this->firstName != null && $this->lastName != null && $this->address != null && $this->country != null && $this->state != null && $this->postcode != null && $this->city != null;
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
	 * @return string Full name for the address.
	 */
	public function getName()
	{
		return $this->firstName.' '.$this->lastName;
	}

	/**
	 * Returns location string based on current translation.
	 *
	 * @return string Location string.
	 */
	public function getLocation()
	{
		// TODO: Write documentation about changing customer location string
		return trim(sprintf(
			_x('%1$s, %2$s', 'customer', 'jigoshop'),
			Country::getName($this->getCountry()),
			Country::hasStates($this->getCountry()) ? Country::getStateName($this->getCountry(), $this->getState()) : $this->getState(),
			$this->getPostcode()
		), ' ,');
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
