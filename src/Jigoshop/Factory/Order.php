<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer\Address;
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order as Entity;
use Jigoshop\Exception;
use Jigoshop\Frontend\Cart;
use Jigoshop\Helper\Country;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
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
		ShippingServiceInterface $shippingService, PaymentServiceInterface $paymentService, TaxServiceInterface $taxService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->productService = $productService;
		$this->shippingService = $shippingService;
		$this->paymentService = $paymentService;
		$this->taxService = $taxService;
	}

	/**
	 * Creates new order properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return Entity
	 */
	public function create($id)
	{
		if (empty($_POST)) {
			$post = $this->wp->getPost($id);

			return $this->fetch($post);
		}

		$order = new Entity($this->wp, $this->options->get('tax.classes'));
		$order->setId($id);

		$date = new \DateTime();
		if (isset($_POST['aa'])) {
			$date->setDate($_POST['aa'], $_POST['mm'], $_POST['jj']);
			$date->setTime($_POST['hh'], $_POST['mn'], $_POST['ss']);
		}

		$order->setCreatedAt($date);
		$order->setUpdatedAt(new \DateTime());
		if (isset($_POST['post_excerpt'])) {
			$order->setCustomerNote($_POST['post_excerpt']);
		}
		if (isset($_POST['order'])) {
			if (isset($_POST['order']['status'])) {
				$order->setStatus($_POST['order']['status']);
			}

			if (!empty($_POST['order']['customer'])) {
				$order->setCustomer($this->customerService->find($_POST['order']['customer']));
			}

			if (isset($_POST['order']['billing'])) {
				$order->getCustomer()->setBillingAddress($this->createAddress($_POST['order']['billing']));
			}
			if (isset($_POST['order']['shipping'])) {
				$order->getCustomer()->setShippingAddress($this->createAddress($_POST['order']['shipping']));
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
			$state['created_at'] = strtotime($post->post_date);
			// Customer must be unserialized twice "thanks" to WordPress second serialization.
			$state['customer'] = unserialize(unserialize($state['customer']));
			// TODO: Think on lazy loading of items.
			$state['items'] = $this->getItems($post->ID);
			$state['product_subtotal'] = array_reduce($state['items'], function($value, $item){
				/** @var $item Entity\Item */
				return $value + $item->getCost();
			}, 0.0);
			if ($state['shipping']) {
				$shipping = unserialize($state['shipping']);
				if (!empty($shipping['method'])) {
					$state['shipping'] = array(
						'method' => $this->shippingService->findForState($shipping['method']),
						'price' => $shipping['price'],
					);
				}
			}
			if ($state['payment']) {
				$state['payment'] = $this->paymentService->get($state['payment']);
			}
			$state['subtotal'] = (float)$state['subtotal'];

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
		$customer = $cart->getCustomer();
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

		if (filter_var($address->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
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

		$order = new Entity($this->wp, $this->options->get('tax.classes'));
		$order->setCustomer($customer);
		$order->setCustomerNote(trim(htmlspecialchars(strip_tags($_POST['jigoshop_order']['note']))));
		$order->setStatus(Entity\Status::CREATED);

		if (isset($_POST['jigoshop_order']['payment_method'])) {
			$payment = $this->paymentService->get($_POST['jigoshop_order']['payment_method']);
			$order->setPaymentMethod($payment);
		}

		if (isset($_POST['jigoshop_order']['shipping_method'])) {
			$shipping = $this->shippingService->get($_POST['jigoshop_order']['shipping_method']);
			$order->setShippingMethod($shipping, $this->taxService);
		}

		foreach ($cart->getItems() as $item) {
			$order->addItem($item);
		}

		return $order;
	}

	private function createAddress($data)
	{
		if (!empty($data['company'])) {
			$address = new CompanyAddress();
			$address->setCompany($data['company']);
			if (isset($data['euvatno'])) {
				$address->setVatNumber($data['euvatno']);
			}
		} else {
			$address = new Address();
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
			$tax = array();
			$id = $results[$i]['id'];
			$product = $this->productService->find($results[$i]['product_id']);
			$item = new Entity\Item();
			$item->setId($results[$i]['item_id']);
			$item->setName($results[$i]['title']);
			$item->setQuantity($results[$i]['quantity']);
			$item->setPrice($results[$i]['price']);

			while ($i < $endI && $results[$i]['id'] == $id) {
				if (strpos($results[$i]['meta_key'], 'tax_') !== false) {
					$tax[str_replace('tax_', '', $results[$i]['meta_key'])] = $results[$i]['meta_value'];
				} else {
					$meta = new Entity\Item\Meta();
					$meta->setKey($results[$i]['meta_key']);
					$meta->setValue($results[$i]['meta_value']);
					$item->addMeta($meta);
				}
				$i++;
			}

			$item->setTax($tax);
			$product = $this->wp->applyFilters('jigoshop\factory\order\find_product', $product, $item);
			$item->setProduct($product);
			$item->setKey($this->productService->generateItemKey($item));
			$items[] = $item;
		}

		return $items;
	}

	/**
	 * @param $address Address
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
			if ($address->getPostcode() == null) {// TODO: Zip validation
				$errors[] = __('Postcode is empty.', 'jigoshop');
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
