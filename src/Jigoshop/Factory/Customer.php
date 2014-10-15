<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer as Entity;
use WPAL\Wordpress;

class Customer implements EntityFactoryInterface
{
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

//		if (!empty($_POST)) {
//			$customer->setName($this->wp->sanitizeTitle($_POST['post_title']));
//			$customer->setDescription($this->wp->wpautop($this->wp->wptexturize($_POST['post_excerpt'])));
//			$_POST['product']['categories'] = $this->getTerms($id, Types::PRODUCT_CATEGORY, $this->wp->getTerms(Types::PRODUCT_CATEGORY, array(
//				'posts__in' => $_POST['tax_input']['product_category'],
//			)));
//			$_POST['product']['tags'] = $this->getTerms($id, Types::PRODUCT_TAG, $this->wp->getTerms(Types::PRODUCT_TAG, array(
//				'posts__in' => $_POST['tax_input']['product_tag'],
//			)));
//
//			if (!isset($_POST['product']['tax_classes'])) {
//				$_POST['product']['tax_classes'] = array();
//			}
//
//			$customer->restoreState($_POST['product']);
//			$customer->markAsDirty($_POST['product']);
//		}

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
		$customer = new Entity();
		$state = array();

		if($user){
			$state = array_map(function ($item){
				return $item[0];
			}, $this->wp->getUserMeta($user->ID));

			$state['id'] = $user->ID;
			$state['login'] = $user->get('login');
			$state['email'] = $user->get('user_email');
			$state['name'] = $user->get('display_name');

			$customer->restoreState($state);
		}

		return $this->wp->applyFilters('jigoshop\find\customer', $customer, $state);
	}
}
