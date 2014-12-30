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
	 * Finds and fetches all available WordPress users.
	 *
	 * @return array List of all available users.
	 */
	public function findAll();

	/**
	 * Checks whether provided customer needs to be taxed.
	 *
	 * @param Customer $customer Customer to check.
	 * @return boolean Whether customer needs to be taxed.
	 */
	public function isTaxable(Customer $customer);
}
