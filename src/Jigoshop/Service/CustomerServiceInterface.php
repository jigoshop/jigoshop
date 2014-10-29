<?php
namespace Jigoshop\Service;

use Jigoshop\Entity\Customer;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\OrderInterface;


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
	 * Prepares and returns customer object for specified order.
	 *
	 * @param OrderInterface $order Order to fetch shipping customer from.
	 * @return \Jigoshop\Entity\Customer
	 */
	public function getShipping(OrderInterface $order);

	/**
	 * Prepares and returns customer object for specified order.
	 *
	 * @param OrderInterface $order Order to fetch tax customer from.
	 * @return \Jigoshop\Entity\Customer
	 */
	public function getTax(OrderInterface $order);

	/**
	 * Saves product to database.
	 *
	 * @param EntityInterface $object Customer to save.
	 * @throws Exception
	 */
	public function save(EntityInterface $object);
}
