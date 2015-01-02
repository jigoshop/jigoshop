<?php

namespace Jigoshop\Entity;

use Jigoshop\Exception;
use Monolog\Registry;

/**
 * Customer entity.
 *
 * @package Jigoshop\Entity
 */
class Customer implements EntityInterface
{
	private $id;
	private $login;
	private $email;
	private $name;
	private $taxAddress = 'billing';

	/** @var Customer\Address */
	private $billingAddress;
	/** @var Customer\Address */
	private $shippingAddress;

	public function __construct()
	{
		$this->billingAddress = new Customer\Address();
		$this->shippingAddress = new Customer\Address();
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id Customer ID.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string Login of the user associated with the customer.
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * Updates customer login.
	 *
	 * @param string $login Login of the customer.
	 */
	public function setLogin($login)
	{
		$this->login = $login;
	}

	/**
	 * @return string Customer's email.
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
	 * @return string Name of the customer.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name Name of the customer.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return Customer\Address
	 */
	public function getBillingAddress()
	{
		return $this->billingAddress;
	}

	/**
	 * @param Customer\Address $billingAddress
	 */
	public function setBillingAddress($billingAddress)
	{
		$this->billingAddress = $billingAddress;
	}

	/**
	 * @return Customer\Address
	 */
	public function getShippingAddress()
	{
		return $this->shippingAddress;
	}

	/**
	 * Selects which address is to be used as tax address.
	 *
	 * @param $address string Name of address to be used as tax address.
	 */
	public function selectTaxAddress($address)
	{
		if (!in_array($address, array('billing', 'shipping'))) {
			if (WP_DEBUG) {
				throw new Exception(sprintf(__('Unknown address type: "%s".', 'jigoshop'), $address));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical(sprintf('Unknown address type: "%s".', $address));
			return;
		}

		$this->taxAddress = $address;
	}

	/**
	 * @return Customer\Address
	 */
	public function getTaxAddress()
	{
		if ($this->taxAddress === 'billing') {
			return $this->billingAddress;
		}

		return $this->shippingAddress;
	}

	/**
	 * @param Customer\Address $shippingAddress
	 */
	public function setShippingAddress($shippingAddress)
	{
		$this->shippingAddress = $shippingAddress;
	}

	/**
	 * Checks whether billing and shipping addresses have the same country, state and postcode
	 * .
	 * @return bool Shipping and billing address matches?
	 */
	public function hasMatchingAddresses()
	{
		return $this->billingAddress->getCountry() == $this->shippingAddress->getCountry() &&
			$this->billingAddress->getState() == $this->shippingAddress->getState() &&
			$this->billingAddress->getPostcode() == $this->shippingAddress->getPostcode();
	}

	public function __toString()
	{
		return sprintf('%s (%s)', $this->getName(), $this->getEmail());
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		return array(
			'id' => $this->id,
			'login' => $this->login,
			'email' => $this->email,
			'name' => $this->name,
			'billing' => $this->billingAddress,
			'shipping' => $this->shippingAddress,
		);
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		if (isset($state['id'])) {
			$this->id = $state['id'];
		}
		if (isset($state['login'])) {
			$this->login = $state['login'];
		}
		if (isset($state['email'])) {
			$this->email = $state['email'];
		}
		if (isset($state['name'])) {
			$this->name = $state['name'];
		}

		if (isset($state['billing'])) {
			$this->setBillingAddress(unserialize($state['billing']));
		}
		if (isset($state['shipping'])) {
			$this->setShippingAddress(unserialize($state['shipping']));
		}
	}
}
