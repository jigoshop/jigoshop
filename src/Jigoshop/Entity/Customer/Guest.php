<?php

namespace Jigoshop\Entity\Customer;

use Jigoshop\Entity\Customer;

class Guest extends Customer
{
	public function getId()
	{
		return '';
	}

	public function getLogin()
	{
		return '';
	}

	public function getName()
	{
		return __('Guest', 'jigoshop');
	}

	public function __toString()
	{
		return $this->getName();
	}
}
