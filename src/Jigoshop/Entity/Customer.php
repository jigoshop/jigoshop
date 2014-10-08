<?php

namespace Jigoshop\Entity;

use Jigoshop\Helper\Country;

/**
 * Customer entity.
 *
 * @package Jigoshop\Entity
 */
class Customer
{
	public function getCountry()
	{
		// TODO: Implement
		return 'US';
	}

	public function getState()
	{
		// TODO: Implement
		return 'AK';
	}

	public function getPostcode()
	{
		// TODO: Implement
		return '123';
	}

	public function getLocation()
	{
		// TODO: Write documentation about changing customer location string
		return sprintf(
			_x('%1$s, %2$s', 'customer', 'jigoshop'),
			Country::getName($this->getCountry()),
			Country::getStateName($this->getCountry(), $this->getState()),
			$this->getPostcode()
		);
	}
}
