<?php
namespace Jigoshop\Service;

use Jigoshop\Entity\Customer;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Order;


/**
 * Customer service.
 *
 * @package Jigoshop\Service
 */
interface CustomerServiceInterface extends ServiceInterface
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
	public function find($id);

	/**
	 * Finds and fetches all available WordPress users.
	 *
	 * @return array List of all available users.
	 */
	public function findAll();

	/**
	 * Saves product to database.
	 *
	 * @param EntityInterface $object Customer to save.
	 * @throws Exception
	 */
	public function save(EntityInterface $object);

	/**
	 * Checks whether provided customer needs to be taxed.
	 *
	 * @param Customer $customer Customer to check.
	 * @return boolean Whether customer needs to be taxed.
	 */
	public function isTaxable(Customer $customer);
}
