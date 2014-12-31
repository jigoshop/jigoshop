<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Cart;
use Jigoshop\Entity\Customer as CustomerEntity;
use Jigoshop\Entity\Order as Entity;
use Jigoshop\Exception;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Validation;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Shipping\MultipleMethod;
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
	/** @var PaymentServiceInterface */
	private $paymentService;

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService, ProductServiceInterface $productService,
		ShippingServiceInterface $shippingService, PaymentServiceInterface $paymentService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->productService = $productService;
		$this->shippingService = $shippingService;
		$this->paymentService = $paymentService;
	}

	/**
	 * Creates new order properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return Entity
	 */
	public function create($id)
	{
		$post = $this->wp->getPost($id);

		// Support for our own post types and "Publish" button.
		if (isset($_POST['original_post_status'])) {
			$post->post_status = $_POST['original_post_status'];
		}

		$order = $this->fetch($post);

		$order->setUpdatedAt(new \DateTime());
		if (isset($_POST['post_excerpt'])) {
			$order->setCustomerNote($_POST['post_excerpt']);
		}
		if (isset($_POST['order'])) {
			if (isset($_POST['order']['status'])) {
				$order->setStatus($_POST['order']['status']);
			}

			if (!empty($_POST['order']['customer'])) {
				/** @var CustomerEntity $customer */
				$customer = $this->customerService->find($_POST['order']['customer']);
				$order->setCustomer($customer);
			}

			if (isset($_POST['order']['billing_address'])) {
				$order->getCustomer()->setBillingAddress($this->createAddress($_POST['order']['billing_address']));
			}
			if (isset($_POST['order']['shipping_address'])) {
				$order->getCustomer()->setShippingAddress($this->createAddress($_POST['order']['shipping_address']));
			}
		}

		$order = $this->wp->applyFilters('jigoshop\factory\order\create\after_customer', $order);

		$order->removeItems();
		$items = $this->getItems($id);
		foreach ($items as $item) {
			$order->addItem($item);
		}

		if (isset($_POST['order']['shipping'])) {
			$method = $this->shippingService->get($_POST['order']['shipping']);

			if ($method instanceof MultipleMethod && isset($_POST['order']['shipping_rate'])) {
				$method->setShippingRate($_POST['order']['shipping_rate']);
			}

			$order->setShippingMethod($method);
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
		/** @var Entity $order */
		$order = $this->wp->applyFilters('jigoshop\factory\order\fetch\before', $order);
		$state = array();

		if($post){
			$state = array_map(function ($item){
				return $item[0];
			}, $this->wp->getPostMeta($post->ID));

			$order->setId($post->ID);
			if (isset($state['customer'])) {
				// Customer must be unserialized twice "thanks" to WordPress second serialization.
				$order->setCustomer(unserialize(unserialize($state['customer'])));
				unset($state['customer']);
			}
			/** @var Entity $order */
			$order = $this->wp->applyFilters('jigoshop\factory\order\fetch\after_customer', $order);
			$state['customer_note'] = $post->post_excerpt;
			$state['status'] = $post->post_status;
			$state['created_at'] = strtotime($post->post_date);
			$state['items'] = $this->getItems($post->ID);
			$state['product_subtotal'] = array_reduce($state['items'], function($value, $item){
				/** @var $item Entity\Item */
				return $value + $item->getCost();
			}, 0.0);
			if (isset($state['shipping'])) {
				$shipping = unserialize($state['shipping']);
				if (!empty($shipping['method'])) {
					$state['shipping'] = array(
						'method' => $this->shippingService->findForState($shipping['method']),
						'price' => $shipping['price'],
						'rate' => isset($shipping['rate']) ? $shipping['rate'] : null,
					);
				}
			}
			if (isset($state['payment'])) {
				$state['payment'] = $this->paymentService->get($state['payment']);
			}
			if (isset($state['subtotal'])) {
				$state['subtotal'] = (float)$state['subtotal'];
			}

			$order->restoreState($state);
		}

		return $this->wp->applyFilters('jigoshop\find\order', $order, $state);
	}

	/**
	 * @param Cart $cart Cart to get data from.
	 * @return Entity Order instance.
	 * @throws Exception When errors occurred.
	 */
	public function fromCart(Cart $cart)
	{
		$order = clone $cart;
		$order->setId(false);
		$customer = $order->getCustomer();
		$customer->selectTaxAddress($this->options->get('taxes.shipping') ? 'shipping' : 'billing');
		$address = $this->createAddress($_POST['jigoshop_order']['billing']);
		$customer->setBillingAddress($address);

		$billingErrors = $this->validateAddress($address);

		if ($address->getEmail() == null || $address->getPhone() == null) {
			if ($address->getEmail() == null) {
				$billingErrors[] = __('Email address is empty.', 'jigoshop');
			}
			if ($address->getPhone() == null) {
				$billingErrors[] = __('Phone is empty.', 'jigoshop');
			}
		}

		if (!Validation::isEmail($address->getEmail())) {
			$billingErrors[] = __('Email address is invalid.', 'jigoshop');
		}

		if ($_POST['jigoshop_order']['different_shipping'] == 'on') {
			$address = $this->createAddress($_POST['jigoshop_order']['shipping']);
			$shippingErrors = $this->validateAddress($address);
		}

		$customer->setShippingAddress($address);

		$error = '';
		if (!empty($billingErrors)) {
			$error .= $this->prepareAddressError(__('Billing address is not valid.', 'jigoshop'), $billingErrors);
		}
		if (!empty($shippingErrors)) {
			$error .= $this->prepareAddressError(__('Shipping address is not valid.', 'jigoshop'), $shippingErrors);
		}
		if (!empty($error)) {
			throw new Exception($error);
		}

		$order->setCustomerNote(trim(htmlspecialchars(strip_tags($_POST['jigoshop_order']['note']))));
		$order->setStatus(Entity\Status::PENDING);

		if (isset($_POST['jigoshop_order']['payment_method'])) {
			$payment = $this->paymentService->get($_POST['jigoshop_order']['payment_method']);
			$this->wp->doAction('jigoshop\checkout\set_payment\before', $payment, $order);
			$order->setPaymentMethod($payment);
		}

		if (isset($_POST['jigoshop_order']['shipping_method'])) {
			$shipping = $this->shippingService->get($_POST['jigoshop_order']['shipping_method']);
			$this->wp->doAction('jigoshop\checkout\set_shipping\before', $shipping, $order);
			$order->setShippingMethod($shipping);
		}

		return $order;
	}

	private function createAddress($data)
	{
		if (!empty($data['company'])) {
			$address = new CustomerEntity\CompanyAddress();
			$address->setCompany($data['company']);
			if (isset($data['euvatno'])) {
				$address->setVatNumber($data['euvatno']);
			}
		} else {
			$address = new CustomerEntity\Address();
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
			WHERE joi.order_id = %d
			ORDER BY joi.id",
			array($id));
		$results = $wpdb->get_results($query, ARRAY_A);
		$items = array();

		for ($i = 0, $endI = count($results); $i < $endI;) {
			$id = $results[$i]['id'];
			$product = $this->productService->find($results[$i]['product_id']);
			$item = new Entity\Item();
			$item->setId($results[$i]['item_id']);
			$item->setName($results[$i]['title']);
			$item->setQuantity($results[$i]['quantity']);
			$item->setPrice($results[$i]['price']);
			$item->setTax($results[$i]['tax']);

			while ($i < $endI && $results[$i]['id'] == $id) {
				$meta = new Entity\Item\Meta();
				$meta->setKey($results[$i]['meta_key']);
				$meta->setValue($results[$i]['meta_value']);
				$item->addMeta($meta);
				$i++;
			}

			$product = $this->wp->applyFilters('jigoshop\factory\order\find_product', $product, $item);
			$item->setProduct($product);
			$item->setKey($this->productService->generateItemKey($item));
			$items[] = $item;
		}

		return $items;
	}

	/**
	 * @param $address CustomerEntity\Address
	 * @return array
	 */
	private function validateAddress($address)
	{
		$errors = array();

		if (!$address->isValid()) {
			if ($address->getFirstName() == null) {
				$errors[] = __('First name is empty.', 'jigoshop');
			}
			if ($address->getLastName() == null) {
				$errors[] = __('Last name is empty.', 'jigoshop');
			}
			if ($address->getAddress() == null) {
				$errors[] = __('Address is empty.', 'jigoshop');
			}
			if ($address->getCountry() == null) {
				$errors[] = __('Country is not selected.', 'jigoshop');
			}
			if ($address->getState() == null) {
				$errors[] = __('State or province is not selected.', 'jigoshop');
			}
			if ($address->getPostcode() == null) {
				$errors[] = __('Postcode is empty.', 'jigoshop');
			}
			if ($this->options->get('shopping.validate_zip') && !Validation::isPostcode($address->getPostcode(), $address->getCountry())) {
				$errors[] = __('Invalid postcode.', 'jigoshop');
			}
		}

		if (!Country::exists($address->getCountry())) {
			$errors[] = sprintf(__('Country "%s" does not exist.', 'jigoshop'), $address->getCountry());
		}
		if (Country::hasStates($address->getCountry()) && !Country::hasState($address->getCountry(), $address->getState())) {
			$errors[] = sprintf(__('Country "%s" does not have state "%s".', 'jigoshop'), $address->getCountry(), $address->getState());
		}

		return $errors;
	}

	private function prepareAddressError($message, $errors)
	{
		return $message.'<ul><li>'.join('</li><li>', $errors).'</li></ul>';
	}
}
