<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order as Entity;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use WPAL\Wordpress;

class Order implements EntityFactoryInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var ShippingServiceInterface */
	private $shippingService;

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService, ProductServiceInterface $productService,
		ShippingServiceInterface $shippingService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->productService = $productService;
		$this->shippingService = $shippingService;
	}

	/**
	 * Creates new order properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return Entity
	 */
	public function create($id)
	{
		$order = new Entity($this->wp, $this->options->get('tax.classes'));
		$order->setId($id);

		if (empty($_POST)) {
			$post = $this->wp->getPost($id);

			return $this->fetch($post);
		}

		$date = new \DateTime();
		if (isset($_POST['aa'])) {
			$date->setDate($_POST['aa'], $_POST['mm'], $_POST['jj']);
			$date->setTime($_POST['hh'], $_POST['mn'], $_POST['ss']);
		}

		$order->setCreatedAt($date);
		$order->setNumber($id); // TODO: Support for continuous numeration and custom order numbers
		$order->setUpdatedAt(new \DateTime());
		if (isset($_POST['post_excerpt'])) {
			$order->setCustomerNote($_POST['post_excerpt']);
		}
		if (isset($_POST['order'])) {
			if (isset($_POST['order']['status'])) {
				$order->setStatus($_POST['order']['status']);
			}

			if (isset($_POST['order']['billing'])) {
				$order->setBillingAddress($this->createAddress($_POST['order']['billing']));
			}
			if (isset($_POST['order']['shipping'])) {
				$order->setShippingAddress($this->createAddress($_POST['order']['shipping']));
			}

			if (!empty($_POST['order']['customer'])) {
				$order->setCustomer($this->customerService->find($_POST['order']['customer']));
			}
		}

		// TODO: Think on lazy loading of items.
		$items = $this->getItems($id);
		foreach ($items as $item) {
			$order->addItem($item);
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
		$order = new Entity($this->wp, $this->options->get('tax.classes'));
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
			if ($state['shipping']) {
				$state['shipping'] = $this->shippingService->findForState(unserialize($state['shipping']));
			}
			$state['subtotal'] = $state['subtotal'];

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
		$item->setTax($data['tax']);

		$product = $this->productService->find($data['product_id']);
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
		$query = $wpdb->prepare("
			SELECT * FROM {$wpdb->prefix}jigoshop_order_item joi
			LEFT JOIN {$wpdb->prefix}jigoshop_order_item_meta joim ON joim.item_id = joi.id
			WHERE joi.order_id = %d AND (joim.meta_key LIKE %s OR joim.meta_key IS NULL)
			ORDER BY joi.id",
			array($id, 'tax%'));
		$results = $wpdb->get_results($query, ARRAY_A);
		$items = array();

		for ($i = 0, $endI = count($results); $i < $endI;) {
			$item = array(
				'id' => $results[$i]['item_id'],
				'order_id' => $results[$i]['order_id'],
				'product_id' => $results[$i]['product_id'],
				'product_type' => $results[$i]['product_type'],
				'title' => $results[$i]['title'],
				'price' => $results[$i]['price'],
				'quantity' => $results[$i]['quantity'],
				'cost' => $results[$i]['cost'],
				'tax' => array(),
			);

			while ($i < $endI && $results[$i]['item_id'] == $item['id']) {
				$item['tax'][str_replace('tax_', '', $results[$i]['meta_key'])] = $results[$i]['meta_value'];
				$i++;
			}

			$items[] = $item;
		}

		$that = $this;
		return array_map(function($item) use ($that){
			return $that->formatOrderItem($item);
		}, $items);
	}
}
