<?php

namespace Jigoshop\Entity\Customer;

use Jigoshop\Entity\Customer;
use Jigoshop\Exception;

class Guest extends Customer
{
	public function getId()
	{
		return '';
	}

	public function setId($id)
	{
		// TODO: Log message.
		if (WP_DEBUG) {
			throw new Exception(__('Guest customer cannot be updated!', 'jigoshop'));
		}
	}

	public function getLogin()
	{
		return '';
	}

	public function setLogin($login)
	{
		// TODO: Log message.
		if (WP_DEBUG) {
			throw new Exception(__('Guest customer cannot be updated!', 'jigoshop'));
		}
	}

	public function getName()
	{
		return __('Guest', 'jigoshop');
	}

	public function setName($name)
	{
		// TODO: Log message.
		if (WP_DEBUG) {
			throw new Exception(__('Guest customer cannot be updated!', 'jigoshop'));
		}
	}

	public function __toString()
	{
		return $this->getName();
	}

	public function getStateToSave()
	{
		return array(
			'id' => '',
		);
	}

}
