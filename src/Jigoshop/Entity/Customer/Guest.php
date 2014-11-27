<?php

namespace Jigoshop\Entity\Customer;

use Jigoshop\Entity\Customer;
use Jigoshop\Exception;
use Monolog\Registry;

class Guest extends Customer
{
	public function getId()
	{
		return '';
	}

	public function setId($id)
	{
		if (WP_DEBUG) {
			throw new Exception(__('Guest customer cannot be updated!', 'jigoshop'));
		}

		Registry::getInstance('jigoshop')->addDebug('Guest customer cannot be updated!');
	}

	public function getLogin()
	{
		return '';
	}

	public function setLogin($login)
	{
		if (WP_DEBUG) {
			throw new Exception(__('Guest customer cannot be updated!', 'jigoshop'));
		}

		Registry::getInstance('jigoshop')->addDebug('Guest customer cannot be updated!');
	}

	public function getName()
	{
		return __('Guest', 'jigoshop');
	}

	public function setName($name)
	{
		if (WP_DEBUG) {
			throw new Exception(__('Guest customer cannot be updated!', 'jigoshop'));
		}

		Registry::getInstance('jigoshop')->addDebug('Guest customer cannot be updated!');
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
