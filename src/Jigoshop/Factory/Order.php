<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Order as Entity;
use Jigoshop\Service\CustomerServiceInterface;
use WPAL\Wordpress;

class Order implements EntityFactoryInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var CustomerServiceInterface */
	private $customerService;

	public function __construct(Wordpress $wp, CustomerServiceInterface $customerService)
	{
		$this->wp = $wp;
		$this->customerService = $customerService;
	}

	/**
	 * Creates new order properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return Entity
	 */
	public function create($id)
	{
		$date = new \DateTime();
		if (isset($_POST['aa'])) {
			$date->setDate($_POST['aa'], $_POST['mm'], $_POST['jj']);
			$date->setTime($_POST['hh'], $_POST['mn'], $_POST['ss']);
		}

		$order = new Entity($this->wp);
		$order->setId($id);
		$order->setCreatedAt($date);

		if (!empty($_POST)) {
			$order->setNumber($id); // TODO: Support for continuous numeration and custom order numbers
			$order->setUpdatedAt(new \DateTime());
			$order->setCustomerNote($_POST['post_excerpt']);
			$order->setStatus($_POST['order']['status']);

			if (isset($_POST['order']['billing'])) {
				$order->setBillingAddress($this->createAddress($_POST['order']['billing']));
			}
			if (isset($_POST['order']['shipping'])) {
				$order->setShippingAddress($this->createAddress($_POST['order']['shipping']));
			}

			if (!empty($_POST['order']['customer'])) {
				$order->setCustomer($this->customerService->find($_POST['order']['customer']));
			}

			// TODO: Items creation
		}

		return $order;
	}

	/**
	 * Fetches order from database.
	 *
	 * @param $post \WP_Post Post to fetch order for.
	 * @return \Jigoshop\Entity\Order
	 */
	public function fetch($post)
	{
		$order = new Entity($this->wp);
		$state = array();

		if($post){
			$state = array_map(function ($item){
				return $item[0];
			}, $this->wp->getPostMeta($post->ID));

			$state['id'] = $post->ID;
			$state['customer_note'] = $post->post_excerpt;
			$state['status'] = $post->post_status;
			$state['customer'] = $this->customerService->find($state['customer']);

			// TODO: Items fetching

			$order->restoreState($state);
		}

		return $this->wp->applyFilters('jigoshop\find\order', $order, $state);
	}

	private function createAddress($data)
	{
		if (!empty($data['company'])) {
			$address = new Entity\CompanyAddress();
			$address->setCompany($data['company']);
			if (isset($data['euvatno'])) {
				$address->setVatNumber($data['euvatno']);
			}
		} else {
			$address = new Entity\Address();
		}

		$address->setFirstName($data['first_name']);
		$address->setLastName($data['last_name']);
		$address->setAddress($data['address']);
		$address->setCountry($data['country']);
		$address->setState($data['state']);
		$address->setCity($data['city']);
		$address->setPostcode($data['postcode']);

		if (isset($data['phone'])) {
			$address->setPhone($data['phone']);
		}

		if (isset($data['email'])) {
			$address->setEmail($data['email']);
		}

		return $address;
	}
}
