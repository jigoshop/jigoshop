<?php

namespace Jigoshop\Entity;

use Jigoshop\Helper\Country;

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

	private $country;
	private $state;
	private $postcode;

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
	 * @return string Country code.
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param string $country New country code for current customer.
	 */
	public function setCountry($country)
	{
		if ($this->country != $country) {
			$this->country = $country;
			$this->setState(''); // On country change - also clear state.
		}
	}

	/**
	 * @return string State code or name.
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
			'country' => $this->country,
			'state' => $this->state,
			'postcode' => $this->postcode,
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

		if (isset($state['country'])) {
			$this->setCountry($state['country']);
		}
		if (isset($state['state'])) {
			$this->setState($state['state']);
		}
		if (isset($state['postcode'])) {
			$this->setPostcode($state['postcode']);
		}
	}
}
