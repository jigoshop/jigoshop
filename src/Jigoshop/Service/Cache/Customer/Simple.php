<?php

namespace Jigoshop\Service\Cache\Customer;

use Jigoshop\Entity\Customer;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Order;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\Exception;

class Simple implements CustomerServiceInterface
{
	/** @var \Jigoshop\Service\CustomerServiceInterface */
	private $service;
	private $current;
	private $customers = array();
	private $fetchedAll = false;

	public function __construct(CustomerServiceInterface $service)
	{
		$this->service = $service;
	}
	/**
	 * Returns currently logged in customer.
	 *
	 * @return \Jigoshop\Entity\Customer Current customer entity.
	 */
	public function getCurrent()
	{
		if ($this->current === null) {
			$this->current = $this->service->getCurrent();
		}

		return $this->current;
	}

	/**
	 * Finds single user with specified ID.
	 *
	 * @param $id int Customer ID.
	 * @return \Jigoshop\Entity\Customer Customer for selected ID.
	 */
	public function find($id)
	{
		if (!isset($this->customers[$id])) {
			$this->customers[$id] = $this->service->find($id);
		}

		return $this->customers[$id];
	}

	/**
	 * Finds and fetches all available WordPress users.
	 *
	 * @return array List of all available users.
	 */
	public function findAll()
	{
		if (!$this->fetchedAll) {
			$this->customers = $this->service->findAll();
		}

		return $this->customers;
	}

	/**
	 * Saves product to database.
	 *
	 * @param EntityInterface $object Customer to save.
	 * @throws Exception
	 */
	public function save(EntityInterface $object)
	{
		$this->customers[$object->getId()] = $object;
		$this->service->save($object);
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return EntityInterface Item found.
	 */
	public function findForPost($post)
	{
		return $this->service->findForPost($post);
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery($query)
	{
		return $this->service->findByQuery($query);
	}

	/**
	 * Checks whether provided customer needs to be taxed.
	 *
	 * @param Customer $customer Customer to check.
	 * @return boolean Whether customer needs to be taxed.
	 */
	public function isTaxable(Customer $customer)
	{
		return $this->service->isTaxable($customer);
	}
}
