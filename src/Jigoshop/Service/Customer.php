<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Customer as Entity;

/**
 * Customer service.
 *
 * @package Jigoshop\Service
 */
class Customer implements CustomerServiceInterface
{
	/**
	 * Returns currently logged in customer.

	 *
*@return Entity Current customer entity.
	 */
	public function getCurrent()
	{
		// TODO: Properly fetch customer from session and database
		return new Entity();
	}

	/**
	 * Finds single user with specified ID.

	 *
*@param $id int Customer ID.
	 * @return Entity Customer for selected ID.
	 */
	public function get($id)
	{
		// TODO: Properly fetch customer based on ID
	}

	/**
	 * Finds and fetches all available WordPress users.
	 *
	 * @return array List of all available users.
	 */
	public function findAll()
	{
		// TODO: Implement
		$guest = new Entity\Guest();
		return array(
			$guest->getId() => $guest,
			1 => new Entity(),
		);
	}
}
