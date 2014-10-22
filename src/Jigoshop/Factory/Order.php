<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Order as Entity;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Order implements EntityFactoryInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var ProductServiceInterface */
	private $productService;

	public function __construct(Wordpress $wp, CustomerServiceInterface $customerService, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->customerService = $customerService;
		$this->productService = $productService;
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

			// TODO: Think on lazy loading of items.
			$items = $this->getItems($id);
			foreach ($items as $item) {
				$order->addItem($item);
			}
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

			$order->setId($post->ID);
			$state['customer_note'] = $post->post_excerpt;
			$state['status'] = $post->post_status;
			$state['customer'] = $this->customerService->find($state['customer']);
			// TODO: Think on lazy loading of items.
			$state['items'] = $this->getItems($post->ID);
			$state['product_subtotal'] = array_reduce($state['items'], function($value, $item){
				return $value + $item->getCost();
			}, 0.0);
			// TODO: Properly calculate subtotal and total
			$state['subtotal'] = $state['product_subtotal'];
			$state['total'] = $state['product_subtotal'];

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

	private function formatOrderItem($data)
	{
		$item = new Entity\Item();
		$item->setId($data['id']);
		$item->setType($data['product_type']);
		$item->setName($data['title']);
		$item->setQuantity($data['quantity']);
		$item->setPrice($data['price']);

		$product = $this->productService->find($data['id']);
		$item->setProduct($product);

		return $item;
	}

	/**
	 * @param $id int Order ID.
	 * @return array List of items assigned to the order.
	 */
	private function getItems($id)
	{
		$wpdb = $this->wp->getWPDB();
		$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}jigoshop_order_item joi WHERE joi.order_id = %d", array($id));
		$items = $wpdb->get_results($query, ARRAY_A);
		$that = $this;
		return array_map(function($item) use ($that){
			return $that->formatOrderItem($item);
		}, $items);
	}
}
