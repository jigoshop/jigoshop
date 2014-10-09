<?php

namespace Jigoshop\Entity;

use Jigoshop\Helper\Country;

/**
 * Customer entity.
 *
 * TODO: Proper implementation.
 *
 * @package Jigoshop\Entity
 */
class Customer
{
	private $country;
	private $state;
	private $postcode;

	public function __construct()
	{
		if (!isset($_SESSION['jigoshop_customer'])) {
			$_SESSION['jigoshop_customer'] = array(
				'country' => 'GB',
				'state' => '',
				'postcode' => '',
			);
		}

		$this->country = $_SESSION['jigoshop_customer']['country'];
		$this->state = $_SESSION['jigoshop_customer']['state'];
		$this->postcode = $_SESSION['jigoshop_customer']['postcode'];
	}

	public function getCountry()
	{
		return $this->country;
	}

	public function getState()
	{
		return $this->state;
	}

	public function getPostcode()
	{
		return $this->postcode;
	}

	/**
	 * @param string $country Country code for current customer.
	 */
	public function setCountry($country)
	{
		$this->country = $country;
		$_SESSION['jigoshop_customer']['country'] = $country;
	}

	/**
	 * @param mixed $postcode
	 */
	public function setPostcode($postcode)
	{
		$this->postcode = $postcode;
		$_SESSION['jigoshop_customer']['postcode'] = $postcode;
	}

	/**
	 * @param mixed $state
	 */
	public function setState($state)
	{
		$this->state = $state;
		$_SESSION['jigoshop_customer']['state'] = $state;
	}

	public function getLocation()
	{
		// TODO: Write documentation about changing customer location string
		return trim(sprintf(
			_x('%1$s, %2$s', 'customer', 'jigoshop'),
			Country::getName($this->getCountry()),
			Country::getStateName($this->getCountry(), $this->getState()),
			$this->getPostcode()
		), ' ,');
	}
}
