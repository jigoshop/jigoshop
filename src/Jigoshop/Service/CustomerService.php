<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Customer as Entity;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Order;
use Jigoshop\Exception;
use Jigoshop\Factory\Customer as Factory;
use Jigoshop\Helper\Country;
use Monolog\Registry;
use WPAL\Wordpress;

/**
 * Customer service.
 *
 * @package Jigoshop\Service
 */
class CustomerService implements CustomerServiceInterface
{
	/** @var Wordpress */
	private $wp;
	/** @var Factory */
	private $factory;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Factory $factory, Options $options)
	{
		$this->wp = $wp;
		$this->factory = $factory;
		$this->options = $options;
	}

	/**
	 * Returns currently logged in customer.
	 *
	 * @return Entity Current customer entity.
	 */
	public function getCurrent()
	{
		$user = $this->wp->wpGetCurrentUser();
		return $this->factory->fetch($user);
	}

	/**
	 * Finds single user with specified ID.
	 *
	 * @param $id int Customer ID.
	 * @return Entity Customer for selected ID.
	 */
	public function find($id)
	{
		$user = $this->wp->getUserData($id);
		return $this->factory->fetch($user);
	}

	/**
	 * Finds and fetches all available WordPress users.
	 *
	 * @return array List of all available users.
	 */
	public function findAll()
	{
		$guest = new Entity\Guest();
		$customers = array(
			$guest->getId() => $guest,
		);

		$users = $this->wp->getUsers();
		foreach ($users as $user) {
			$customers[$user->ID] = $this->factory->fetch($user);
		}

		return $customers;
	}

	/**
	 * Saves product to database.
	 *
	 * @param EntityInterface $object Customer to save.
	 * @throws Exception
	 */
	public function save(EntityInterface $object)
	{
		if (!($object instanceof Entity)) {
			throw new Exception('Trying to save not a customer!');
		}

		$fields = $object->getStateToSave();

		if (isset($fields['id']) || isset($fields['name']) || isset($fields['email']) || isset($fields['login'])) {
			// TODO: Do we want to update user data like this?
//			$this->wp->wpUpdateUser(array(
//				'ID' => $fields['id'],
//				'display_name' => $fields['name'],
//				'user_email' => $fields['email'],
//			));

			unset($fields['id'], $fields['name'], $fields['email'], $fields['login']);
		}

		if ($object instanceof Entity\Guest) {
			$_SESSION[Factory::CUSTOMER] = $fields;
			return;
		}

		foreach ($fields as $field => $value) {
			$this->wp->updateUserMeta($object->getId(), $field, $value);
		}
	}

	/**
	 * Checks whether provided customer needs to be taxed.
	 *
	 * @param Entity $customer Customer to check.
	 * @return boolean Whether customer needs to be taxed.
	 */
	public function isTaxable(Entity $customer)
	{
		$country = $this->options->get('general.country');
		$customerCountry = $customer->getTaxAddress()->getCountry();

		if(Country::isEU($country)){
			return Country::isEU($customerCountry);
		}

		return ($country == $customerCountry);
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return EntityInterface Item found.
	 */
	public function findForPost($post)
	{
		if (WP_DEBUG) {
			throw new Exception('Customer service do not support fetching for post - users are not stored this way.');
		}

		Registry::getInstance(JIGOSHOP_LOGGER)->addWarning('Invalid call to Jigoshop\Service\Customer::findForPost() method.');
		return null;
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery($query)
	{
		if (WP_DEBUG) {
			throw new Exception('Customer service do not support fetching by query - users are not stored like posts.');
		}

		Registry::getInstance(JIGOSHOP_LOGGER)->addWarning('Invalid call to Jigoshop\Service\Customer::findByQuery() method.');
		return null;
	}
}
