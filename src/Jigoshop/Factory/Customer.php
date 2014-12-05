<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer as Entity;
use WPAL\Wordpress;

class Customer implements EntityFactoryInterface
{
	const CUSTOMER = 'jigoshop_customer';

	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
	}

	/**
	 * Creates new customer properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return Entity
	 */
	public function create($id)
	{
		$customer = new Entity();
		$customer->setId($id);

		return $customer;
	}

	/**
	 * Fetches customer from database.
	 *
	 * @param $user \WP_User User object to fetch customer for.
	 * @return \Jigoshop\Entity\Customer
	 */
	public function fetch($user)
	{
		if ($user->ID == 0) {
			$customer = new Entity\Guest();

			if (isset($_SESSION[self::CUSTOMER])) {
				$customer->restoreState($_SESSION[self::CUSTOMER]);
			}
		} else {
			$customer = new Entity();

			$state = array();
			$meta = $this->wp->getUserMeta($user->ID);

			if (is_array($meta)) {
				$state = array_map(function ($item){
					return $item[0];
				}, $meta);
			}

			$state['id'] = $user->ID;
			$state['login'] = $user->get('login');
			$state['email'] = $user->get('user_email');
			$state['name'] = $user->get('display_name');

			$customer->restoreState($state);
		}

//		echo '<pre>'; var_dump($customer); exit;

		return $this->wp->applyFilters('jigoshop\find\customer', $customer, $state);
	}
}
