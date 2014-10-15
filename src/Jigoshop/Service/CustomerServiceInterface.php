<?php
namespace Jigoshop\Service;


/**
 * Customer service.
 *
 * @package Jigoshop\Service
 */
interface CustomerServiceInterface
{
	/**
	 * Returns currently logged in customer.
	 *
	 * @return \Jigoshop\Entity\Customer Current customer entity.
	 */
	public function getCurrent();

	/**
	 * Finds single user with specified ID.
	 *
	 * @param $id int Customer ID.
	 * @return \Jigoshop\Entity\Customer Customer for selected ID.
	 */
	public function get($id);

	/**
	 * Finds and fetches all available WordPress users.
	 *
	 * @return array List of all available users.
	 */
	public function findAll();
}
